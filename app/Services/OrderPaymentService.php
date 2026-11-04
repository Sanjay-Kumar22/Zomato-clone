<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

/**
 * Finalizes the money/benefit side of an order only after payment is confirmed.
 * This prevents unpaid Stripe/Razorpay orders from consuming coupons, loyalty
 * points, wallet balance, or clearing the customer's cart.
 */
class OrderPaymentService
{
    public function finalize(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            return;
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->getAttribute('payment_finalized_at')) {
                return;
            }

            $user = $lockedOrder->user()->lockForUpdate()->firstOrFail();

            if ($lockedOrder->points_redeemed > 0) {
                $points = min((int) $lockedOrder->points_redeemed, (int) $user->loyalty_points);
                if ($points !== (int) $lockedOrder->points_redeemed) {
                    throw new \RuntimeException('Not enough loyalty points remain to finalize this order.');
                }
                $user->decrement('loyalty_points', $points);
            }

            if ($lockedOrder->wallet_used > 0) {
                if ((float) $user->wallet_balance < (float) $lockedOrder->wallet_used) {
                    throw new \RuntimeException('Insufficient wallet balance to finalize this order.');
                }
                app(WalletService::class)->debit(
                    $user,
                    (float) $lockedOrder->wallet_used,
                    'order_payment',
                    $lockedOrder->id
                );
            }

            if ($lockedOrder->coupon_code) {
                $coupon = Coupon::where('code', $lockedOrder->coupon_code)->lockForUpdate()->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            // A timestamp is used as an idempotency marker. The migration below
            // adds this nullable column; existing installs can run the migration.
            $lockedOrder->update(['payment_finalized_at' => now()]);
        });

        // Clear only after successful finalization. This is intentionally outside
        // the DB transaction because the cart/coupon are stored in the browser session.
        app(CartService::class)->clear();
        app(CouponService::class)->remove();
        session()->forget('pending_payment_order_id');
    }
}
