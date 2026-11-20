@extends('customer.layouts.app')
@section('title', 'Checkout')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    body { background: #FAF7F5; }

    .checkout-section {
        background: white; border-radius: 18px; padding: 26px 28px;
        box-shadow: 0 1px 3px rgba(20,15,10,0.04), 0 8px 24px rgba(20,15,10,0.04);
        margin-bottom: 22px; border: 1px solid #F3EDE8;
    }
    .checkout-section h6 {
        font-size: 15px; font-weight: 700; margin-bottom: 20px;
        display: flex; align-items: center; letter-spacing: 0.01em;
        color: #241C15;
    }
    .step-badge {
        width: 26px; height: 26px; border-radius: 50%;
        background: #FDECEA; color: #e8342a; font-size: 12px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: 12px; flex-shrink: 0;
    }

    /* Address selector */
    .addr-option {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 18px; border: 1.5px solid #EFE7E1;
        border-radius: 14px; margin-bottom: 10px;
        cursor: pointer; transition: all 0.18s ease; position: relative;
    }
    .addr-option:hover { border-color: #f0b3ae; background: #FFFAF9; }
    .addr-option.selected { border-color: #e8342a; background: #FFF6F5; box-shadow: 0 0 0 3px rgba(232,52,42,0.06); }
    .addr-option input[type=radio] {
        margin-top: 3px; accent-color: #e8342a; width:17px; height:17px; flex-shrink:0;
    }
    .addr-icon-badge {
        width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
        background: #FBF3EF; display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .addr-option.selected .addr-icon-badge { background: #FDE4E2; }
    .addr-label {
        font-size: 10.5px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.04em; color: #e8342a; margin-bottom: 3px;
    }
    .addr-text { font-size: 13.5px; color: #3A2F26; line-height: 1.45; font-weight: 500; }
    .addr-pincode { font-size: 11.5px; color: #A9998A; margin-top: 3px; }
    .default-tag {
        margin-left: auto; flex-shrink: 0;
        background: #E9F7EE; color: #1E8E4E;
        font-size: 10px; font-weight: 800; letter-spacing: 0.02em;
        padding: 4px 10px; border-radius: 20px; height: fit-content;
    }

    /* Add address button */
    .add-addr-btn {
        display: flex; align-items: center; gap: 12px;
        padding: 16px 18px; border: 1.5px dashed #e8b0aa;
        border-radius: 14px; cursor: pointer;
        color: #e8342a; font-weight: 700; font-size: 13.5px;
        background: #FFFAF9; transition: all 0.18s ease;
        margin-bottom: 14px;
    }
    .add-addr-btn:hover { background: #FDECEA; border-color: #e8342a; }

    /* Address row (radio card + edit button) */
    .addr-row { display: flex; align-items: stretch; gap: 8px; margin-bottom: 10px; }
    .addr-row .addr-option { margin-bottom: 0; flex: 1; }
    .addr-edit-btn {
        flex-shrink: 0; width: 44px; border: 1.5px solid #EFE7E1; border-radius: 14px;
        background: white; color: #A9998A; cursor: pointer; transition: all 0.18s ease;
    }
    .addr-edit-btn:hover { border-color: #e8342a; color: #e8342a; background: #FFF6F5; }

    /* Delivery slot chips (compact, scrollable — replaces the old tall list) */
    .slot-chip {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 18px; border: 1.5px solid #EFE7E1; border-radius: 14px;
        cursor: pointer; transition: all 0.18s ease; margin-bottom: 16px;
    }
    .slot-chip:hover { border-color: #f0b3ae; background: #FFFAF9; }
    .slot-chip.selected { border-color: #e8342a; background: #FFF6F5; box-shadow: 0 0 0 3px rgba(232,52,42,0.06); }
    .slot-chip input[type=radio] { accent-color: #e8342a; width: 17px; height: 17px; }
    .slot-chip-icon {
        width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
        background: #FFF4E0; display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .slot-chip-label { font-size: 13.5px; font-weight: 700; color: #241C15; }
    .slot-chip-sub { font-size: 11.5px; color: #A9998A; margin-top: 1px; }
    .slot-scroll-label {
        font-size: 11px; color: #A9998A; margin-bottom: 10px;
        text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700;
    }
    .slot-scroll {
        display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px;
        scrollbar-width: thin;
    }
    .slot-scroll::-webkit-scrollbar { height: 5px; }
    .slot-scroll::-webkit-scrollbar-thumb { background: #EFE7E1; border-radius: 10px; }
    .slot-pill {
        flex-shrink: 0; display: flex; align-items: center; gap: 6px;
        padding: 10px 18px; border: 1.5px solid #EFE7E1; border-radius: 30px;
        font-size: 12.5px; font-weight: 700; color: #6B5C4F; cursor: pointer;
        white-space: nowrap; transition: all 0.18s ease;
    }
    .slot-pill:hover { border-color: #e8342a; color: #e8342a; }
    .slot-pill:has(input:checked) { border-color: #e8342a; background: #FFF6F5; color: #e8342a; }
    .slot-pill input { display: none; }

    /* Payment method cards */
    .pay-option .addr-icon-badge { font-size: 17px; }
    .pay-badge-cod { background: #E9F7EE; }
    .pay-badge-stripe { background: #E8F1FE; }
    .pay-badge-razorpay { background: #F1EAFB; }

    /* Current location btn */
    .location-btn {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 16px; border: 1.5px solid #EFE7E1;
        border-radius: 14px; cursor: pointer;
        color: #e8342a; font-size: 13px; font-weight: 700;
        background: white; transition: all 0.18s ease; margin-bottom: 16px;
    }
    .location-btn:hover { background: #FFF6F5; border-color: #e8342a; }

    .section-divider {
        text-align: center; font-size: 11.5px; color: #C6B9AC;
        margin: 18px 0; position: relative; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .section-divider::before {
        content: ''; position: absolute; top: 50%; left: 0; right: 0;
        height: 1px; background: #F0E8E2;
    }
    .section-divider span { background: white; padding: 0 12px; position: relative; }

    /* Order items */
    .order-item-row {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 0; border-bottom: 1px solid #F5EFEA;
    }
    .order-item-row:last-child { border-bottom: none; padding-bottom: 0; }
    .order-item-thumb {
        width: 46px; height: 46px; border-radius: 10px; object-fit: cover;
        flex-shrink: 0; background: #FBF3EF;
    }
    .order-item-thumb-placeholder {
        width: 46px; height: 46px; border-radius: 10px; flex-shrink: 0;
        background: #FBF3EF; display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }

    /* Bill summary */
    .bill-card {
        position: sticky; top: 90px; background: white; border-radius: 18px;
        border: 1px solid #F3EDE8; box-shadow: 0 1px 3px rgba(20,15,10,0.04), 0 8px 24px rgba(20,15,10,0.04);
        overflow: hidden;
    }
    .bill-card-header { padding: 24px 26px 4px; }
    .bill-card-body { padding: 4px 26px 26px; }
    .bill-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 13.5px; }
    .bill-row .label { color: #8A7A6D; }
    .bill-row .value { font-weight: 700; color: #241C15; }
    .bill-dashed {
        border: none; border-top: 1.5px dashed #EFE7E1; margin: 12px 0;
    }
    .bill-total-row { display: flex; justify-content: space-between; align-items: baseline; padding: 6px 0 20px; }
    .bill-total-row .label { font-size: 15px; font-weight: 800; color: #241C15; }
    .bill-total-row .value { font-size: 21px; font-weight: 800; color: #e8342a; }

    .place-btn {
        background: #e8342a; color: white; border: none; border-radius: 14px;
        padding: 17px; font-size: 15px; font-weight: 700; width: 100%; cursor: pointer;
        transition: all 0.18s ease; box-shadow: 0 6px 18px rgba(232,52,42,0.28);
    }
    .place-btn:hover { background: #d42a20; box-shadow: 0 8px 22px rgba(232,52,42,0.35); transform: translateY(-1px); }
    .place-btn:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; transform: none; }

    .coupon-input-wrap { display: flex; gap: 8px; margin-bottom: 18px; }
    .coupon-input-wrap .form-control { background: #FAF7F5; border: 1.5px solid #F0E8E2; }

    /* Modal */
    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 1px solid #f0f0f0; padding: 20px 24px 16px; }
    .modal-body { padding: 20px 24px; }
    .form-label { font-size: 13px; font-weight: 600; color: #444; }
    .form-control, .form-select {
        border-radius: 10px; border: 1.5px solid #eee;
        padding: 10px 14px; font-size: 14px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #e8342a; box-shadow: 0 0 0 3px rgba(232,52,42,0.1);
    }

    /* Label type selector */
    .label-selector { display: flex; gap: 8px; margin-bottom: 16px; }
    .label-opt {
        flex: 1; text-align: center; padding: 10px;
        border: 2px solid #eee; border-radius: 10px;
        cursor: pointer; font-size: 13px; font-weight: 600;
        color: #666; transition: all 0.2s;
    }
    .label-opt:hover { border-color: #e8342a; color: #e8342a; }
    .label-opt.selected { border-color: #e8342a; background: #fff5f5; color: #e8342a; }
    .label-opt input { display: none; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-2">
    <a href="{{ route('customer.cart.index') }}" class="btn btn-light btn-sm rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0" style="color:#241C15">Checkout</h4>
    </div>
</div>
<p class="mb-4" style="font-size:13px;color:#A9998A;margin-left:52px">
    <i class="bi bi-shield-check me-1"></i>Secure checkout — your details stay private
</p>

<form action="{{ route('customer.order.place') }}" method="POST" id="checkoutForm">
@csrf
<input type="hidden" name="restaurant_id" value="{{ request('restaurant_id') ?? app(\App\Services\CartService::class)->restaurantId() }}">
<div class="row g-4">
    <div class="col-md-7">

        <!-- Delivery Address -->
        <div class="checkout-section">
            <h6><span class="step-badge">1</span>Select Delivery Address</h6>

            <!-- Add new address -->
            <div class="add-addr-btn" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="bi bi-plus-circle-fill" style="font-size:20px"></i>
                Add address
            </div>

            @if($addresses->isNotEmpty())
            <div class="section-divider"><span>Saved addresses</span></div>

            @if($blockedAddressIds->count() === $addresses->count())
            <div class="alert border-0 rounded-3 mb-3" style="background:#fce4ec;color:#c62828;font-size:13px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                We don't deliver to any of your saved addresses from this restaurant.
                Please add an address closer to the restaurant, or go back and order from a restaurant nearer to you.
            </div>
            @endif

            @foreach($addresses as $addr)
            @php $isBlocked = $blockedAddressIds->contains($addr->id); @endphp
            <div class="addr-row" style="{{ $isBlocked ? 'opacity:0.55' : '' }}">
                <label class="addr-option {{ $addr->is_default && !$isBlocked ? 'selected' : '' }}" style="margin-bottom:0">
                    <input type="radio" name="address_id" value="{{ $addr->id }}"
                        data-fee="{{ $deliveryFeeByAddress[$addr->id] ?? 0 }}"
                        {{ $addr->is_default && !$isBlocked ? 'checked' : '' }}
                        {{ $isBlocked ? 'disabled' : '' }}>
                    <span class="addr-icon-badge">
                        {{ $addr->label === 'Home' ? '🏠' : ($addr->label === 'Work' ? '🏢' : '📍') }}
                    </span>
                    <div class="flex-grow-1">
                        <div class="addr-label">{{ $addr->label }}</div>
                        <div class="addr-text">{{ $addr->full_address }}</div>
                        <div class="addr-pincode">Pincode: {{ $addr->pincode }}</div>
                    </div>
                    @if($isBlocked)
                        <span class="badge" style="background:#fce4ec;color:#c62828;font-size:11px">We don't deliver here</span>
                    @elseif($addr->is_default)
                        <span class="default-tag">✓ Default</span>
                    @endif
                </label>
                <button type="button" class="addr-edit-btn" title="Edit address"
                    onclick="openEditAddress({{ $addr->id }}, {{ json_encode($addr->label) }}, {{ json_encode($addr->full_address) }}, {{ json_encode($addr->pincode) }}, {{ $addr->lat ?? 'null' }}, {{ $addr->lng ?? 'null' }}, {{ $addr->is_default ? 'true' : 'false' }})">
                    <i class="bi bi-pencil"></i>
                </button>
            </div>
            @endforeach
            @endif
        </div>

        <!-- Delivery Time -->
        <div class="checkout-section">
            <h6><span class="step-badge">2</span>Delivery Time</h6>

            <label class="slot-chip selected" style="cursor:pointer">
                <input type="radio" name="delivery_slot" value="ASAP" checked>
                <span class="slot-chip-icon">⚡</span>
                <div>
                    <div class="slot-chip-label">As soon as possible</div>
                    <div class="slot-chip-sub">Standard delivery, no scheduling needed</div>
                </div>
            </label>

            @if(count($deliverySlots))
            <div class="slot-scroll-label">Or schedule for later today</div>
            <div class="slot-scroll">
                @foreach($deliverySlots as $slot)
                <label class="slot-pill" style="cursor:pointer">
                    <input type="radio" name="delivery_slot" value="{{ $slot }}">
                    <i class="bi bi-clock"></i> {{ $slot }}
                </label>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Payment Method -->
        <div class="checkout-section">
            <h6><span class="step-badge">3</span>Payment Method</h6>

            <label class="addr-option pay-option selected" style="cursor:pointer">
                <input type="radio" name="payment_method" value="cod" checked>
                <span class="addr-icon-badge pay-badge-cod">💵</span>
                <div class="flex-grow-1">
                    <div class="addr-label" style="color:#1E8E4E">Cash on Delivery</div>
                    <div class="addr-text">Pay with cash when your order arrives</div>
                </div>
            </label>

            <label class="addr-option pay-option" style="cursor:pointer">
                <input type="radio" name="payment_method" value="stripe">
                <span class="addr-icon-badge pay-badge-stripe">💳</span>
                <div class="flex-grow-1">
                    <div class="addr-label" style="color:#3378E0">Pay Online (Card)</div>
                    <div class="addr-text">Secure payment via Stripe — Visa, Mastercard, RuPay</div>
                </div>
            </label>

            <label class="addr-option pay-option" style="cursor:pointer">
                <input type="radio" name="payment_method" value="razorpay">
                <span class="addr-icon-badge pay-badge-razorpay">📱</span>
                <div class="flex-grow-1">
                    <div class="addr-label" style="color:#7B4FCB">Pay Online (UPI / Card / Wallet)</div>
                    <div class="addr-text">Secure payment via Razorpay — UPI, Cards, Netbanking, Wallets</div>
                </div>
            </label>
        </div>

        <!-- Order Items -->
        <div class="checkout-section">
            <h6><span class="step-badge">4</span>Your Order</h6>
            @foreach($cartItems as $item)
            @php
                $thumb = $item['image'] ?? null;
                if ($thumb && !str_starts_with($thumb, 'http')) { $thumb = asset('storage/'.$thumb); }
            @endphp
            <div class="order-item-row">
                @if($thumb)
                    <img src="{{ $thumb }}" alt="{{ $item['name'] }}" class="order-item-thumb">
                @else
                    <div class="order-item-thumb-placeholder">🍽️</div>
                @endif
                <div class="flex-grow-1">
                    <div style="font-size:14px;font-weight:700;color:#241C15">{{ $item['name'] }}</div>
                    <div style="font-size:12px;color:#A9998A">{{ $item['restaurant'] }} · qty {{ $item['quantity'] }}</div>
                    @if(!empty($item['options']))
                        <div style="font-size:11px;color:#C6B9AC">
                            {{ collect($item['options'])->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                        </div>
                    @endif
                </div>
                <span style="font-weight:700;color:#241C15">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
            </div>
            @endforeach

            <div class="mt-4">
                <label for="specialInstructions" class="form-label fw-bold" style="font-size:13px;color:#241C15">
                    <i class="bi bi-chat-left-text me-1 text-danger"></i>Special instructions
                    <span class="text-muted fw-normal">(optional)</span>
                </label>
                <textarea id="specialInstructions" name="special_instructions" class="form-control"
                    rows="3" maxlength="1000"
                    placeholder="Allergies, spice preference, or delivery instructions..."
                    style="border-radius:12px;border-color:#EFE7E1;font-size:13px">{{ old('special_instructions') }}</textarea>
                <div class="form-text">Your note will be shared with the restaurant and delivery partner.</div>
            </div>
        </div>
    </div>

    <!-- Bill Summary -->
    <div class="col-md-5">
        <div class="bill-card">
            <div class="bill-card-header">
                <h6><i class="bi bi-receipt me-2 text-danger"></i>Bill Summary</h6>
            </div>
            <div class="bill-card-body">

            <!-- Coupon -->
            @php($checkoutRestaurantId = request('restaurant_id') ?? app(\App\Services\CartService::class)->restaurantId())
            @if($appliedCoupon)
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded-3" style="background:#f1f8e9">
                    <span style="font-size:13px;color:#2e7d32">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <strong>{{ $appliedCoupon->code }}</strong> applied
                    </span>
                    <button type="button" class="btn btn-link btn-sm text-danger p-0" style="font-size:12px"
                        onclick="submitCouponAction('remove', {{ (int) $checkoutRestaurantId }})">Remove</button>
                </div>
            @else
                <div class="coupon-input-wrap">
                    <input type="text" id="checkoutCouponCode" class="form-control form-control-sm" placeholder="Enter coupon code">
                    <button type="button" class="btn btn-outline-danger btn-sm" style="white-space:nowrap"
                        onclick="submitCouponAction('apply', {{ (int) $checkoutRestaurantId }})">Apply</button>
                </div>
            @endif

            <!-- Loyalty Points -->
            @if($maxRedeemable > 0)
                <div class="mb-3 p-2 rounded-3" style="background:#FFF8E8;border:1px solid #F5E4B8">
                    <label class="d-flex align-items-center justify-content-between" style="cursor:pointer;font-size:13px">
                        <span>
                            <i class="bi bi-gem me-1" style="color:#c9971e"></i>
                            Redeem <strong id="redeemPointsLabel">{{ $maxRedeemable }}</strong> of your {{ $userPoints }} points
                            (₹<span id="redeemValueLabel">{{ $maxRedeemable }}</span> off)
                        </span>
                        <input type="checkbox" id="redeemPointsToggle" class="form-check-input" style="accent-color:#c9971e">
                    </label>
                </div>
                <input type="hidden" name="redeem_points" id="redeemPointsInput" value="0">
            @elseif($userPoints > 0)
                <div class="mb-3" style="font-size:11.5px;color:#A9998A">
                    <i class="bi bi-gem me-1"></i>You have {{ $userPoints }} loyalty points — not enough yet to redeem on this order's value.
                </div>
            @endif

            <!-- Wallet -->
            @if($walletBalance > 0)
                <div class="mb-3 p-2 rounded-3" style="background:#EAF7EF;border:1px solid #C9EAD4; margin-top:10px">
                    <label class="d-flex align-items-center justify-content-between" style="cursor:pointer;font-size:13px">
                        <span>
                            <i class="bi bi-wallet2 me-1" style="color:#1E8E4E"></i>
                            Pay from wallet — balance ₹{{ number_format($walletBalance, 2) }}
                        </span>
                        <input type="checkbox" id="useWalletToggle" class="form-check-input" style="accent-color:#1E8E4E">
                    </label>
                </div>
                <input type="hidden" name="use_wallet" id="useWalletInput" value="0">
            @else
                <div class="mb-3 p-2 rounded-3 d-flex align-items-center justify-content-between" style="background:#F5F5F5;border:1px solid #E5E5E5;font-size:13px">
                    <span>
                        <i class="bi bi-wallet2 me-1 text-muted"></i>
                        Wallet balance — ₹0.00
                    </span>
                    <a href="{{ route('customer.wallet.index') }}" target="_blank" class="text-danger fw-600" style="font-size:12px">Add Money</a>
                </div>
            @endif

            <div class="bill-row">
                <span class="label">Item Total</span>
                <span class="value">₹{{ number_format($subtotal, 2) }}</span>
            </div>
            @if($discount > 0)
            <div class="bill-row">
                <span class="label">Coupon Discount</span>
                <span class="value" style="color:#1E8E4E">- ₹{{ number_format($discount, 2) }}</span>
            </div>
            @endif
            @if($maxRedeemable > 0)
            <div class="bill-row" id="pointsDiscountRow" style="display:none">
                <span class="label">Points Redeemed</span>
                <span class="value" style="color:#1E8E4E">- ₹<span id="pointsDiscountValue">0</span></span>
            </div>
            @endif
            @if($walletBalance > 0)
            <div class="bill-row" id="walletDiscountRow" style="display:none">
                <span class="label">Paid from Wallet</span>
                <span class="value" style="color:#1E8E4E">- ₹<span id="walletDiscountValue">0</span></span>
            </div>
            @endif
            <div class="bill-row">
                <span class="label">Delivery Fee</span>
                <span id="deliveryFeeDisplay" class="value" style="{{ $deliveryFee > 0 ? '' : 'color:#1E8E4E' }}">
                    {{ $deliveryFee > 0 ? '₹' . number_format($deliveryFee, 2) : 'FREE' }}
                </span>
            </div>
            @if($hasActiveMembership)
            <div class="small text-success mb-2"><i class="bi bi-stars me-1"></i>Member benefit applied: free delivery.</div>
            @endif
            @if($packagingCharge > 0)
            <div class="bill-row">
                <span class="label">Packaging</span>
                <span class="value">₹{{ number_format($packagingCharge, 2) }}</span>
            </div>
            @endif
            <div class="bill-row">
                <span class="label">GST & taxes ({{ $gstRate }}%)</span>
                <span class="value">₹{{ number_format($taxAmount, 2) }}</span>
            </div>
            <div class="bill-row">
                <span class="label">Platform fee</span>
                <span class="value">₹{{ number_format($platformFee, 2) }}</span>
            </div>
            @if($subtotal < $minimumOrderValue)
            <div class="alert alert-warning py-2 mb-3" style="font-size:12px">
                Add ₹{{ number_format($minimumOrderValue - $subtotal, 2) }} more to meet this restaurant's minimum order.
            </div>
            @endif

            <!-- Delivery partner tip -->
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="label"><i class="bi bi-heart me-1 text-danger"></i>Tip your delivery partner</span>
                </div>
                <div class="d-flex gap-2 flex-wrap" id="tipChips">
                    @foreach([0, 20, 30, 50] as $amt)
                    <button type="button" class="btn btn-sm tip-chip {{ $amt === 0 ? 'active' : '' }}" data-tip="{{ $amt }}"
                        style="border-radius:20px;border:1.5px solid #EEE3D6;padding:5px 14px;font-size:12.5px;">
                        {{ $amt === 0 ? 'No tip' : '₹' . $amt }}
                    </button>
                    @endforeach
                    <button type="button" class="btn btn-sm tip-chip" id="customTipBtn"
                        style="border-radius:20px;border:1.5px solid #EEE3D6;padding:5px 14px;font-size:12.5px;">
                        Custom
                    </button>
                </div>
                <input type="number" id="customTipInput" min="0" max="500" step="1" placeholder="Enter custom tip amount"
                    class="form-control form-control-sm mt-2" style="display:none;max-width:200px;">
                <input type="hidden" name="tip_amount" id="tipAmountInput" value="0">
                <div class="text-muted mt-1" style="font-size:11px;">100% of this goes to your delivery partner.</div>
            </div>

            <hr class="bill-dashed">
            <div class="bill-total-row">
                <span class="label">Total</span>
                <span id="totalDisplay" class="value">₹{{ number_format($total, 2) }}</span>
            </div>

            <button type="submit" id="placeOrderBtn" class="place-btn" {{ ($addresses->isEmpty() || $blockedAddressIds->count() === $addresses->count() || $subtotal < $minimumOrderValue) ? 'disabled' : '' }}>
                <i class="bi bi-bag-check me-2"></i>Place Order · ₹<span id="placeOrderTotal">{{ number_format($total, 2) }}</span>
            </button>

            @if($addresses->isEmpty())
            <p class="text-center text-muted mt-3" style="font-size:12px">
                <i class="bi bi-info-circle me-1"></i>Add a delivery address to continue
            </p>
            @endif
            </div>
        </div>
    </div>
</div>
</form>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('customer.address.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                    <!-- Label selector -->
                    <div class="mb-4">
                        <div class="form-label mb-2">Address Type</div>
                        <div class="label-selector">
                            <label class="label-opt selected" onclick="selectLabel(this, 'Home')">
                                <input type="radio" name="label" value="Home" checked>
                                🏠<br>Home
                            </label>
                            <label class="label-opt" onclick="selectLabel(this, 'Work')">
                                <input type="radio" name="label" value="Work">
                                🏢<br>Work
                            </label>
                            <label class="label-opt" onclick="selectLabel(this, 'Other')">
                                <input type="radio" name="label" value="Other">
                                📍<br>Other
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Flat / House No, Building</label>
                        <input type="text" name="house" class="form-control"
                            placeholder="e.g. Flat 4B, Sunshine Apartments" id="houseInput">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Street, Area, Landmark</label>
                        <input type="text" name="area" class="form-control"
                            placeholder="e.g. MG Road, Near City Mall" id="areaInput">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pin Location on Map (optional but recommended)</label>
                        <input type="text" id="addrMapSearch" class="form-control mb-2"
                            placeholder="Search your area to auto-fill and pin it...">
                        <div id="addrMap" style="height:220px;border-radius:12px;overflow:hidden;border:1px solid #ddd"></div>
                        <input type="hidden" name="lat" id="addrLat">
                        <input type="hidden" name="lng" id="addrLng">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control"
                            placeholder="e.g. 110001" maxlength="10" required id="pincodeInput">
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defCheck">
                        <label class="form-check-label" for="defCheck" style="font-size:13px">
                            Set as default address
                        </label>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-bold"
                        style="border-radius:12px;padding:14px" onclick="combineAddress()">
                        Save Address
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Address Modal (shared — populated via JS when an Edit button is clicked) -->
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAddressForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                    <div class="mb-4">
                        <div class="form-label mb-2">Address Type</div>
                        <div class="label-selector" id="editLabelSelector">
                            <label class="label-opt" onclick="selectEditLabel(this, 'Home')">
                                <input type="radio" name="label" value="Home"> 🏠<br>Home
                            </label>
                            <label class="label-opt" onclick="selectEditLabel(this, 'Work')">
                                <input type="radio" name="label" value="Work"> 🏢<br>Work
                            </label>
                            <label class="label-opt" onclick="selectEditLabel(this, 'Other')">
                                <input type="radio" name="label" value="Other"> 📍<br>Other
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Address</label>
                        <textarea name="full_address" id="editFullAddress" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pin Location on Map (optional but recommended)</label>
                        <input type="text" id="editAddrMapSearch" class="form-control mb-2"
                            placeholder="Search your area to auto-fill and pin it...">
                        <div id="editAddrMap" style="height:220px;border-radius:12px;overflow:hidden;border:1px solid #ddd"></div>
                        <input type="hidden" name="lat" id="editAddrLat">
                        <input type="hidden" name="lng" id="editAddrLng">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" id="editPincodeInput" class="form-control"
                            placeholder="e.g. 110001" maxlength="10" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editDefCheck">
                        <label class="form-check-label" for="editDefCheck" style="font-size:13px">
                            Set as default address
                        </label>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-bold" style="border-radius:12px;padding:14px">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Address card highlight on select + live delivery fee/total recalculation
const SUBTOTAL_MINUS_DISCOUNT = {{ $subtotal - $discount }};
const FIXED_CHECKOUT_FEES = {{ $packagingCharge + $taxAmount + $platformFee }};
const MAX_REDEEMABLE_POINTS = {{ $maxRedeemable ?? 0 }};
const WALLET_BALANCE = {{ $walletBalance ?? 0 }};
let currentTip = 0;
let currentDeliveryFee = {{ $deliveryFee }};
let currentPointsDiscount = 0;
let useWallet = false;

function recalcTotal() {
    const beforeWallet = Math.max(0, SUBTOTAL_MINUS_DISCOUNT - currentPointsDiscount + currentDeliveryFee + FIXED_CHECKOUT_FEES + currentTip);
    const walletApplied = useWallet ? Math.min(WALLET_BALANCE, beforeWallet) : 0;
    const total = Math.max(0, beforeWallet - walletApplied);

    const walletRow = document.getElementById('walletDiscountRow');
    if (walletRow) {
        if (walletApplied > 0) {
            walletRow.style.display = 'flex';
            document.getElementById('walletDiscountValue').textContent = walletApplied.toFixed(2);
        } else {
            walletRow.style.display = 'none';
        }
    }

    document.getElementById('totalDisplay').textContent = '₹' + total.toFixed(2);
    document.getElementById('placeOrderTotal').textContent = total.toFixed(2);
}

const walletToggle = document.getElementById('useWalletToggle');
if (walletToggle) {
    walletToggle.addEventListener('change', function() {
        useWallet = this.checked;
        document.getElementById('useWalletInput').value = useWallet ? 1 : 0;
        recalcTotal();
    });
}

const redeemToggle = document.getElementById('redeemPointsToggle');
if (redeemToggle) {
    redeemToggle.addEventListener('change', function() {
        const input = document.getElementById('redeemPointsInput');
        const row = document.getElementById('pointsDiscountRow');
        if (this.checked) {
            currentPointsDiscount = MAX_REDEEMABLE_POINTS;
            input.value = MAX_REDEEMABLE_POINTS;
            row.style.display = 'flex';
            document.getElementById('pointsDiscountValue').textContent = MAX_REDEEMABLE_POINTS.toFixed(2);
        } else {
            currentPointsDiscount = 0;
            input.value = 0;
            row.style.display = 'none';
        }
        recalcTotal();
    });
}

document.querySelectorAll('input[name="address_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.addr-option:not(.pay-option)').forEach(c => c.classList.remove('selected'));
        this.closest('.addr-option').classList.add('selected');

        currentDeliveryFee = parseFloat(this.dataset.fee || 0);

        document.getElementById('deliveryFeeDisplay').textContent = currentDeliveryFee > 0
            ? '₹' + currentDeliveryFee.toFixed(2)
            : 'FREE';
        document.getElementById('deliveryFeeDisplay').style.color = currentDeliveryFee > 0 ? '' : '#2e7d32';
        recalcTotal();
    });
});

// Tip selector — chips + custom amount, all keep the total in sync
const tipChips = document.querySelectorAll('.tip-chip:not(#customTipBtn)');
const customTipBtn = document.getElementById('customTipBtn');
const customTipInput = document.getElementById('customTipInput');
const tipAmountInput = document.getElementById('tipAmountInput');

function setActiveTipChip(activeEl) {
    document.querySelectorAll('.tip-chip').forEach(c => {
        c.style.background = '';
        c.style.color = '';
        c.style.borderColor = '#EEE3D6';
    });
    activeEl.style.background = '#E8342A';
    activeEl.style.color = '#fff';
    activeEl.style.borderColor = '#E8342A';
}

tipChips.forEach(chip => {
    chip.addEventListener('click', function() {
        customTipInput.style.display = 'none';
        currentTip = parseFloat(this.dataset.tip || 0);
        tipAmountInput.value = currentTip;
        setActiveTipChip(this);
        recalcTotal();
    });
});

customTipBtn.addEventListener('click', function() {
    customTipInput.style.display = 'block';
    customTipInput.focus();
    setActiveTipChip(this);
});

customTipInput.addEventListener('input', function() {
    currentTip = Math.max(0, Math.min(500, parseFloat(this.value || 0)));
    tipAmountInput.value = currentTip;
    recalcTotal();
});

// Highlight "No tip" by default
setActiveTipChip(document.querySelector('.tip-chip[data-tip="0"]'));

function submitCouponAction(action, restaurantId) {
    const form = document.createElement('form');
    form.method = action === 'remove' ? 'POST' : 'POST';
    form.action = action === 'remove'
        ? @json(route('customer.coupon.remove'))
        : @json(route('customer.coupon.apply'));
    form.style.display = 'none';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = @json(csrf_token());
    form.appendChild(csrf);

    const restaurant = document.createElement('input');
    restaurant.type = 'hidden';
    restaurant.name = 'restaurant_id';
    restaurant.value = restaurantId;
    form.appendChild(restaurant);

    if (action === 'remove') {
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);
    } else {
        const codeInput = document.getElementById('checkoutCouponCode');
        const code = (codeInput?.value || '').trim();
        if (!code) {
            codeInput?.focus();
            return;
        }
        const coupon = document.createElement('input');
        coupon.type = 'hidden';
        coupon.name = 'code';
        coupon.value = code;
        form.appendChild(coupon);
    }

    document.body.appendChild(form);
    form.submit();
}

// Payment method highlight on select
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.pay-option').forEach(c => c.classList.remove('selected'));
        this.closest('.pay-option').classList.add('selected');
    });
});

// Delivery slot highlight on select
document.querySelectorAll('input[name="delivery_slot"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.slot-chip, .slot-pill').forEach(c => c.classList.remove('selected'));
        this.closest('.slot-chip, .slot-pill').classList.add('selected');
    });
});

// Label type selector
function selectLabel(el, val) {
    document.querySelectorAll('#addAddressModal .label-opt').forEach(l => l.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}

function selectEditLabel(el, val) {
    document.querySelectorAll('#editLabelSelector .label-opt').forEach(l => l.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}

// Combine house + area into full_address before submit
function combineAddress() {
    const house = document.getElementById('houseInput').value;
    const area  = document.getElementById('areaInput').value;
    // Create hidden full_address field
    let hidden = document.getElementById('fullAddrHidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'full_address';
        hidden.id = 'fullAddrHidden';
        document.querySelector('#addAddressModal form').appendChild(hidden);
    }
    hidden.value = [house, area].filter(Boolean).join(', ');
}

// Address map picker — free OpenStreetMap + Leaflet, no API key needed
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    let map, marker, mapInitialized = false;

    document.getElementById('addAddressModal').addEventListener('shown.bs.modal', function() {
        if (mapInitialized) { map.invalidateSize(); return; }
        mapInitialized = true;

        const defaultPos = [30.7333, 76.7794]; // reasonable default center
        map = L.map('addrMap').setView(defaultPos, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        marker = L.marker(defaultPos, { draggable: true }).addTo(map);

        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            document.getElementById('addrLat').value = pos.lat;
            document.getElementById('addrLng').value = pos.lng;
        });

        const searchInput = document.getElementById('addrMapSearch');
        let debounceTimer, resultsBox;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (resultsBox) { resultsBox.remove(); resultsBox = null; }
            if (q.length < 3) return;

            debounceTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=in&q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept-Language': 'en' }
                })
                    .then(res => res.json())
                    .then(showResults)
                    .catch(() => {});
            }, 400);
        });

        function showResults(results) {
            if (resultsBox) resultsBox.remove();
            if (!results.length) return;

            resultsBox = document.createElement('div');
            resultsBox.style.cssText = 'background:white;border:1px solid #ddd;border-radius:8px;margin-top:-6px;margin-bottom:6px;max-height:200px;overflow-y:auto;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:1000;position:relative;';

            results.forEach(r => {
                const item = document.createElement('div');
                item.textContent = r.display_name;
                item.style.cssText = 'padding:8px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
                item.addEventListener('mouseenter', () => item.style.background = '#fff5f5');
                item.addEventListener('mouseleave', () => item.style.background = 'white');
                item.addEventListener('click', function() {
                    const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    document.getElementById('addrLat').value = lat;
                    document.getElementById('addrLng').value = lng;
                    searchInput.value = r.display_name;
                    resultsBox.remove();
                    resultsBox = null;

                    const areaField = document.getElementById('areaInput');
                    if (areaField && !areaField.value.trim()) areaField.value = r.display_name;

                    const pincodeField = document.getElementById('pincodeInput');
                    const match = r.display_name.match(/\b\d{6}\b/);
                    if (pincodeField && match && !pincodeField.value.trim()) pincodeField.value = match[0];
                });
                resultsBox.appendChild(item);
            });

            searchInput.parentNode.insertBefore(resultsBox, searchInput.nextSibling);
        }
    });
})();

// Edit Address — shared modal, populated from the clicked address's data
const editUpdateUrlTemplate = "{{ route('customer.address.update', ['address' => '__ID__']) }}";

function openEditAddress(id, label, fullAddress, pincode, lat, lng, isDefault) {
    document.getElementById('editAddressForm').action = editUpdateUrlTemplate.replace('__ID__', id);
    document.getElementById('editFullAddress').value = fullAddress;
    document.getElementById('editPincodeInput').value = pincode;
    document.getElementById('editDefCheck').checked = !!isDefault;
    document.getElementById('editAddrLat').value = lat ?? '';
    document.getElementById('editAddrLng').value = lng ?? '';

    document.querySelectorAll('#editLabelSelector .label-opt').forEach(l => {
        const match = l.querySelector('input').value === label;
        l.classList.toggle('selected', match);
        l.querySelector('input').checked = match;
    });

    const modal = new bootstrap.Modal(document.getElementById('editAddressModal'));
    modal.show();

    document.getElementById('editAddressModal').addEventListener('shown.bs.modal', function onShown() {
        initEditMap(lat, lng);
    }, { once: true });
}

let editMap, editMarker, editMapInitialized = false;

function initEditMap(lat, lng) {
    const pos = (lat && lng) ? [parseFloat(lat), parseFloat(lng)] : [30.7333, 76.7794];

    if (!editMapInitialized) {
        editMapInitialized = true;
        editMap = L.map('editAddrMap').setView(pos, lat && lng ? 16 : 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(editMap);
        editMarker = L.marker(pos, { draggable: true }).addTo(editMap);
        editMarker.on('dragend', function() {
            const p = editMarker.getLatLng();
            document.getElementById('editAddrLat').value = p.lat;
            document.getElementById('editAddrLng').value = p.lng;
        });
    } else {
        editMap.invalidateSize();
        editMap.setView(pos, lat && lng ? 16 : 12);
        editMarker.setLatLng(pos);
    }

    const searchInput = document.getElementById('editAddrMapSearch');
    let debounceTimer, resultsBox;
    searchInput.value = '';

    searchInput.oninput = function() {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (resultsBox) { resultsBox.remove(); resultsBox = null; }
        if (q.length < 3) return;

        debounceTimer = setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=in&q=${encodeURIComponent(q)}`, {
                headers: { 'Accept-Language': 'en' }
            }).then(res => res.json()).then(showEditResults).catch(() => {});
        }, 400);
    };

    function showEditResults(results) {
        if (resultsBox) resultsBox.remove();
        if (!results.length) return;

        resultsBox = document.createElement('div');
        resultsBox.style.cssText = 'background:white;border:1px solid #ddd;border-radius:8px;margin-top:-6px;margin-bottom:6px;max-height:200px;overflow-y:auto;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:1000;position:relative;';

        results.forEach(r => {
            const item = document.createElement('div');
            item.textContent = r.display_name;
            item.style.cssText = 'padding:8px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            item.addEventListener('mouseenter', () => item.style.background = '#fff5f5');
            item.addEventListener('mouseleave', () => item.style.background = 'white');
            item.addEventListener('click', function() {
                const lt = parseFloat(r.lat), lg = parseFloat(r.lon);
                editMap.setView([lt, lg], 16);
                editMarker.setLatLng([lt, lg]);
                document.getElementById('editAddrLat').value = lt;
                document.getElementById('editAddrLng').value = lg;
                searchInput.value = r.display_name;
                resultsBox.remove();
                resultsBox = null;

                const pincodeField = document.getElementById('editPincodeInput');
                const match = r.display_name.match(/\b\d{6}\b/);
                if (pincodeField && match) pincodeField.value = match[0];
            });
            resultsBox.appendChild(item);
        });

        searchInput.parentNode.insertBefore(resultsBox, searchInput.nextSibling);
    }
}
</script>
@endsection