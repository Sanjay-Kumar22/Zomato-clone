<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PendingCheckout;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Turns a PendingCheckout into a real Order — but only once the payment
 * gateway has actually confirmed the payment. Until this runs, nothing has
 * been written to the orders table, no coupon has been consumed, no loyalty
 * points have been redeemed and no wallet balance has been touched, so an
 * abandoned or failed online payment leaves no trace at all — the cart is
 * exactly as the customer left it.
 */
class CheckoutFinalizer
{
    /**
     * Safe to call more than once for the same checkout (the browser redirect
     * and the gateway webhook can both fire for the same payment) — every
     * call after the first just returns the order that was already created.
     */
    public function finalize(PendingCheckout $pending, string $paymentId): Order
    {
        return DB::transaction(function () use ($pending, $paymentId) {
            // Lock the row so a simultaneous webhook + browser redirect can't
            // both create an order for the same payment.
            $pending = PendingCheckout::whereKey($pending->id)->lockForUpdate()->first();

            if ($pending->order_id) {
                return $pending->order;
            }

            $data = $pending->payload;

            $order = Order::create([
                'user_id'         => $pending->user_id,
                'restaurant_id'   => $pending->restaurant_id,
                'address_id'      => $data['address_id'],
                'subtotal'        => $data['subtotal'],
                'delivery_fee'    => $data['delivery_fee'],
                'tip_amount'      => $data['tip_amount'],
                'delivery_slot'   => $data['delivery_slot'],
                'discount'        => $data['discount'],
                'coupon_code'     => $data['coupon_code'],
                'points_redeemed' => $data['points_redeemed'],
                'points_discount' => $data['points_discount'],
                'points_earned'   => $data['points_earned'],
                'wallet_used'     => $data['wallet_used'],
                'total'           => $pending->payable_amount,
                'status'          => 'pending',
                'payment_status'  => 'paid',
                'payment_method'  => $pending->payment_method,
                'payment_id'      => $paymentId,
            ]);

            foreach ($data['cart_items'] as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'food_item_id'     => $item['food_item_id'],
                    'name'             => $item['name'],
                    'price_snapshot'   => $item['price'],
                    'quantity'         => $item['quantity'],
                    'selected_options' => $item['options'] ?? [],
                ]);
            }

            if (!empty($data['coupon_code'])) {
                Coupon::where('code', $data['coupon_code'])->increment('used_count');
            }

            if (($data['points_redeemed'] ?? 0) > 0) {
                User::whereKey($pending->user_id)->decrement('loyalty_points', $data['points_redeemed']);
            }

            if (($data['wallet_used'] ?? 0) > 0) {
                app(WalletService::class)->debit($pending->user, (float) $data['wallet_used'], 'order_payment', $order->id);
            }

            $pending->update(['status' => 'paid', 'order_id' => $order->id]);

            $order->load('restaurant.owner', 'items', 'user');
            $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
            Notification::send(User::admins()->get(), new NewOrderPlaced($order));

            if ($order->user?->email) {
                Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
            }

            return $order;
        });
    }
}
