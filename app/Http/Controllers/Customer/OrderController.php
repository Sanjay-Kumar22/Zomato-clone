<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\LoyaltyService;
use App\Services\WalletService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Restaurant;
use App\Notifications\NewOrderPlaced;
use App\Models\User;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    // Delivery fee for a given pincode, based on the restaurant's configured zones
    protected function deliveryFeeForPincode(?string $pincode): float
    {
        if (!$pincode || !$this->cart->restaurantId()) {
            return 0;
        }

        return (float) (DeliveryZone::where('restaurant_id', $this->cart->restaurantId())
            ->where('pincode', $pincode)
            ->value('delivery_fee') ?? 0);
    }

    // Whether the restaurant currently in the cart delivers to this address —
    // true for its own base pincode or an explicit non-blocked zone, true if
    // the address falls within the restaurant's delivery radius (when both
    // have coordinates), false otherwise. Accepts either an Address model
    // (preferred — carries lat/lng for the radius fallback) or a bare pincode.
    protected function restaurantDeliversToPincode($addressOrPincode): bool
    {
        $pincode = is_string($addressOrPincode) || $addressOrPincode === null
            ? $addressOrPincode
            : $addressOrPincode->pincode;
        $lat = is_object($addressOrPincode) ? $addressOrPincode->lat : null;
        $lng = is_object($addressOrPincode) ? $addressOrPincode->lng : null;

        if (!$pincode && $lat === null || !$this->cart->restaurantId()) {
            return true;
        }

        $restaurant = Restaurant::find($this->cart->restaurantId());

        return $restaurant ? $restaurant->deliversToLocation($pincode, $lat, $lng) : true;
    }

    // Generates 30-minute delivery slot options for the next ~3.5 hours
    protected function generateDeliverySlots(): array
    {
        $now = now();
        // Round up to the next 30-minute mark, then push 30 min further out
        // so the first slot always gives the kitchen enough lead time.
        $minutes = $now->minute < 30 ? 30 : 60;
        $start = $now->copy()->minute(0)->second(0)->addMinutes($minutes)->addMinutes(30);

        $slots = [];
        for ($i = 0; $i < 7; $i++) {
            $slotStart = $start->copy()->addMinutes($i * 30);
            $slotEnd   = $slotStart->copy()->addMinutes(30);
            $slots[] = $slotStart->format('h:i A') . ' - ' . $slotEnd->format('h:i A');
        }

        return $slots;
    }

    public function checkout(CouponService $coupons, LoyaltyService $loyalty)
    {
        $cartItems = $this->cart->get();

        if (empty($cartItems)) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Cart is empty!');
        }

        $restaurant = Restaurant::find($this->cart->restaurantId());
        if ($restaurant && !$restaurant->isCurrentlyOpen()) {
            return redirect()->route('customer.restaurant.show', $restaurant)
                ->with('error', 'This restaurant is currently closed and not accepting orders right now.');
        }

        $addresses = auth()->user()->addresses;
        $subtotal  = $this->cart->total();

        // Delivery fee per saved address (keyed by address id), so the page can
        // update the total instantly in JS when the customer picks a different address.
        $deliveryFeeByAddress = $addresses->mapWithKeys(
            fn ($a) => [$a->id => $this->deliveryFeeForPincode($a->pincode)]
        );

        // Which saved addresses fall outside this restaurant's delivery area
        // (explicitly blocked, or just a pincode it doesn't serve at all —
        // e.g. the customer picked "View all locations" and browsed into a
        // restaurant far from where they actually live).
        $blockedAddressIds = $addresses->filter(
            fn ($a) => !$this->restaurantDeliversToPincode($a)
        )->pluck('id');

        $selectedAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $deliveryFee     = $selectedAddress ? $deliveryFeeByAddress[$selectedAddress->id] : 0;

        $couponResult  = $coupons->resolve($this->cart->restaurantId(), $subtotal);
        $discount      = $couponResult['discount'];
        $appliedCoupon = $couponResult['coupon'];

        $total = $subtotal - $discount + $deliveryFee;

        $deliverySlots = $this->generateDeliverySlots();

        $userPoints    = auth()->user()->loyalty_points;
        $maxRedeemable = $loyalty->maxRedeemablePoints($subtotal - $discount, $userPoints);
        $walletBalance = auth()->user()->wallet_balance;

        return view('customer.checkout', compact(
            'cartItems', 'addresses', 'subtotal', 'deliveryFee', 'total', 'discount', 'appliedCoupon', 'deliveryFeeByAddress', 'blockedAddressIds', 'deliverySlots', 'userPoints', 'maxRedeemable', 'walletBalance'
        ));
    }

    public function place(Request $request, CouponService $coupons, LoyaltyService $loyalty)
    {
        $request->validate([
            'address_id'     => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,stripe,razorpay',
            'delivery_slot'  => 'nullable|string|max:50',
            'tip_amount'     => 'nullable|numeric|min:0|max:500',
            'redeem_points'  => 'nullable|integer|min:0',
            'use_wallet'     => 'nullable|boolean',
        ]);

        $cartItems = $this->cart->get();

        if (empty($cartItems)) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Cart is empty!');
        }

        $restaurant = Restaurant::find($this->cart->restaurantId());
        if ($restaurant && !$restaurant->isCurrentlyOpen()) {
            return back()->with('error', 'This restaurant is currently closed and not accepting orders right now.');
        }

        $subtotal = $this->cart->total();

        // Compute delivery fee off the address actually selected for this order —
        // not a cached/default value — so the charge always matches the delivery pincode.
        $selectedAddress = auth()->user()->addresses()->find($request->address_id);

        if (!$this->restaurantDeliversToPincode($selectedAddress)) {
            return back()->with('error', "Sorry, this restaurant doesn't deliver to your selected address's location. Please choose a different address, or order from a restaurant closer to you.");
        }

        $deliveryFee     = $this->deliveryFeeForPincode($selectedAddress?->pincode);
        $tipAmount       = (float) ($request->tip_amount ?? 0);

        $couponResult  = $coupons->resolve($this->cart->restaurantId(), $subtotal);
        $discount      = $couponResult['discount'];
        $appliedCoupon = $couponResult['coupon'];

        // Loyalty points redemption — capped server-side regardless of what was submitted.
        $redeemedPoints  = $loyalty->resolveRedeem((int) ($request->redeem_points ?? 0), $subtotal - $discount, auth()->user()->loyalty_points);
        $pointsDiscount  = $loyalty->redeemValue($redeemedPoints);

        $total = max(0, $subtotal - $discount - $pointsDiscount + $deliveryFee + $tipAmount);

        // Wallet — capped to what's actually in the wallet and what's still owed.
        $walletUsed = $request->boolean('use_wallet')
            ? min((float) auth()->user()->wallet_balance, $total)
            : 0;
        $payableTotal = round($total - $walletUsed, 2);

        // Points earned on the paid-for amount (item subtotal minus all discounts).
        $pointsEarned = $loyalty->pointsForAmount(max(0, $subtotal - $discount - $pointsDiscount));

        $order = DB::transaction(function () use ($cartItems, $request, $subtotal, $deliveryFee, $tipAmount, $payableTotal, $walletUsed, $discount, $appliedCoupon, $coupons, $redeemedPoints, $pointsDiscount, $pointsEarned, $loyalty) {
            $order = Order::create([
                'user_id'         => auth()->id(),
                'restaurant_id'   => $this->cart->restaurantId(),
                'address_id'      => $request->address_id,
                'subtotal'        => $subtotal,
                'delivery_fee'    => $deliveryFee,
                'tip_amount'      => $tipAmount,
                'delivery_slot'   => $request->delivery_slot ?: 'ASAP',
                'discount'        => $discount,
                'coupon_code'     => $appliedCoupon?->code,
                'points_redeemed' => $redeemedPoints,
                'points_discount' => $pointsDiscount,
                'points_earned'   => $pointsEarned,
                'wallet_used'     => $walletUsed,
                'total'           => $payableTotal,
                'status'          => 'pending',
                'payment_status'  => 'pending',
                'payment_method'  => $request->payment_method,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'food_item_id'     => $item['food_item_id'],
                    'name'             => $item['name'],
                    'price_snapshot'   => $item['price'],
                    'quantity'         => $item['quantity'],
                    'selected_options' => $item['options'] ?? [],
                ]);
            }

            if ($appliedCoupon) {
                $appliedCoupon->increment('used_count');
            }

            // Redeem now, but only award the earned points once the order is
            // actually delivered (see Owner\OrderController::updateStatus).
            if ($redeemedPoints > 0) {
                auth()->user()->decrement('loyalty_points', $redeemedPoints);
            }

            if ($walletUsed > 0) {
                app(\App\Services\WalletService::class)->debit(auth()->user(), $walletUsed, 'order_payment', $order->id);
            }

            $this->cart->clear();
            $coupons->remove();

            session(['last_order_id' => $order->id]);

            return $order;
        });

        // COD orders are confirmed immediately — notify the restaurant and email the customer now.
        // Stripe/Razorpay orders are notified/emailed only after payment is confirmed
        // (see PaymentController / RazorpayController). If the wallet already covered
        // the full total, there's nothing left to charge — confirm immediately too.
        $fullyPaidByWallet = $order->total <= 0;

        if ($request->payment_method === 'cod' || $fullyPaidByWallet) {
            if ($fullyPaidByWallet) {
                $order->update(['payment_status' => 'paid']);
            }

            $order->load('restaurant.owner', 'items', 'user');
            $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
            Notification::send(User::admins()->get(), new NewOrderPlaced($order));

            if ($order->user?->email) {
                Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
            }

            if ($fullyPaidByWallet) {
                return redirect()->route('customer.orders.index')
                    ->with('success', 'Order placed successfully! Paid fully from your wallet. 🎉');
            }
        }

        // Card payment → send the customer to Stripe's hosted checkout page.
        if ($request->payment_method === 'stripe') {
            return redirect()->route('customer.payment.checkout', $order);
        }

        // Card/UPI/wallet payment → send the customer to Razorpay's checkout popup.
        if ($request->payment_method === 'razorpay') {
            return redirect()->route('customer.payment.razorpay.checkout', $order);
        }

        return redirect()->route('customer.orders.index')
            ->with('success', 'Order placed successfully! Pay cash on delivery. 🎉');
    }

    // Re-add all items from a past order into the cart ("Order Again")
    public function reorder(Order $order)
    {
        abort_if((int) $order->user_id !== (int) auth()->id(), 403);

        $order->load('restaurant');

        if (!$order->restaurant || !$order->restaurant->is_approved) {
            return back()->with('error', 'This restaurant is no longer available.');
        }

        $this->cart->clear();

        $unavailable = [];

        foreach ($order->items as $item) {
            $foodItem = $item->foodItem;
            if (!$foodItem || !$foodItem->is_available) {
                $unavailable[] = $item->name;
                continue;
            }

            $optionIds = collect($item->selected_options ?? [])
                ->pluck('label')
                ->isNotEmpty()
                ? $foodItem->options()
                    ->whereIn('label', collect($item->selected_options ?? [])->pluck('label'))
                    ->whereIn('value', collect($item->selected_options ?? [])->pluck('value'))
                    ->pluck('id')->all()
                : [];

            $this->cart->add($foodItem->id, $item->quantity, $optionIds);
        }

        if (empty($this->cart->get())) {
            return redirect()->route('customer.restaurant.show', $order->restaurant)
                ->with('error', 'None of the items from this order are currently available.');
        }

        $message = 'Items added to cart from your past order!';
        if (!empty($unavailable)) {
            $message .= ' (Unavailable: ' . implode(', ', $unavailable) . ')';
        }

        return redirect()->route('customer.cart.index')->with('success', $message);
    }

    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())->with('restaurant', 'items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%")
                    ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$term}%"));
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('customer.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if((int) $order->user_id !== (int) auth()->id(), 403);
        $order->load('restaurant', 'items', 'address', 'review', 'deliveryAgent');
        return view('customer.order-detail', compact('order'));
    }

    // Lightweight JSON endpoint polled by the order detail page for live tracking
    public function status(Order $order)
    {
        abort_if((int) $order->user_id !== (int) auth()->id(), 403);

        return response()->json([
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'agent_lat'      => $order->agent_lat,
            'agent_lng'      => $order->agent_lng,
            'agent_updated'  => optional($order->agent_location_updated_at)->diffForHumans(),
        ]);
    }

    public function cancel(Order $order, WalletService $wallet)
    {
        abort_if((int) $order->user_id !== (int) auth()->id(), 403);

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        // Refund any loyalty points that were redeemed on this order — points
        // are only earned once delivered, so there's nothing to claw back there.
        if ($order->points_redeemed > 0) {
            auth()->user()->increment('loyalty_points', $order->points_redeemed);
        }

        // Already-paid online orders get refunded to the wallet — COD orders
        // were never charged, so there's nothing to give back.
        if ($order->payment_status === 'paid' && $order->payment_method !== 'cod') {
            $wallet->credit(auth()->user(), (float) $order->total, 'order_refund_cancel', $order->id);
        }

        // Any amount that was paid from the wallet at checkout also goes back,
        // regardless of how the rest of the order was paid.
        if ($order->wallet_used > 0) {
            $wallet->credit(auth()->user(), (float) $order->wallet_used, 'order_refund_cancel', $order->id);
        }

        return back()->with('success', 'Order cancelled successfully.');
    }
}