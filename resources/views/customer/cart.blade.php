@extends('customer.layouts.app')
@section('title', 'Your Cart')

@section('content')
<style>
    .zc-cart-page{max-width:1080px;margin:0 auto;padding-bottom:24px}
    .zc-cart-title{display:flex;align-items:center;gap:10px;margin-bottom:18px;font-weight:700}
    .zc-cart-title i{color:#e23744}
    .zc-cart-note{display:flex;align-items:flex-start;gap:9px;background:#fff8f8;border:1px solid #f2d5d8;border-radius:12px;padding:12px 14px;color:#555;font-size:13px;margin-bottom:18px}
    .zc-cart-note i{color:#e23744;margin-top:1px}
    .zc-restaurant-card{background:#fff;border:1px solid #eee;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.05);margin-bottom:18px}
    .zc-restaurant-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;background:#fff;border-bottom:1px solid #eee}
    .zc-restaurant-name{font-size:17px;font-weight:700;color:#222}
    .zc-restaurant-meta{font-size:12px;color:#777;margin-top:3px}
    .zc-clear-btn{border:1px solid #ddd;background:#fff;color:#555;border-radius:8px;padding:6px 11px;font-size:12px}
    .zc-clear-btn:hover{border-color:#e23744;color:#e23744}
    .zc-item{display:grid;grid-template-columns:64px minmax(0,1fr) auto auto auto;gap:14px;align-items:center;padding:15px 18px;border-bottom:1px solid #f0f0f0}
    .zc-item:last-of-type{border-bottom:0}
    .zc-item-image{width:64px;height:64px;border-radius:12px;overflow:hidden;background:#f7f7f7;display:flex;align-items:center;justify-content:center;font-size:25px}
    .zc-item-image img{width:100%;height:100%;object-fit:cover}
    .zc-item-name{font-weight:600;color:#222;font-size:15px;line-height:1.35}
    .zc-item-options{font-size:11px;color:#888;margin-top:3px}
    .zc-item-price{font-size:13px;color:#e23744;font-weight:600;margin-top:4px}
    .zc-qty{display:flex;align-items:center;border:1px solid #ddd;border-radius:9px;overflow:hidden;background:#fff}
    .zc-qty button{width:31px;height:31px;border:0;background:#fff;color:#555;font-size:16px;line-height:1}
    .zc-qty button:hover{background:#fff4f5;color:#e23744}
    .zc-qty span{min-width:30px;text-align:center;font-size:13px;font-weight:600}
    .zc-item-total{min-width:82px;text-align:right;font-weight:700;color:#222;font-size:14px}
    .zc-delete{width:32px;height:32px;border:1px solid #f0b8be;background:#fff;color:#e23744;border-radius:8px}
    .zc-delete:hover{background:#fff4f5}
    .zc-cart-body{padding:16px 18px;background:#fcfcfc;border-top:1px solid #eee}
    .zc-coupon-applied{display:flex;justify-content:space-between;align-items:center;background:#f2f9ee;border:1px solid #dcebd4;border-radius:10px;padding:10px 12px;margin-bottom:14px}
    .zc-coupon-applied span{font-size:13px;color:#2e7d32}
    .zc-remove{border:0;background:transparent;color:#e23744;font-size:12px;padding:0}
    .zc-coupon-form{display:flex;gap:8px;margin-bottom:15px}
    .zc-coupon-form input{height:38px;border-radius:9px;border:1px solid #ddd;padding:0 12px}
    .zc-coupon-form button{height:38px;border-radius:9px;min-width:72px}
    .zc-summary{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:22px;align-items:end}
    .zc-summary-row{display:flex;justify-content:space-between;gap:20px;font-size:13px;color:#555;margin-bottom:8px}
    .zc-summary-row.discount{color:#2e7d32}
    .zc-summary hr{border:0;border-top:1px solid #ddd;margin:12px 0}
    .zc-total{display:flex;justify-content:space-between;align-items:center;font-weight:700;font-size:15px;color:#222}
    .zc-total strong{font-size:20px;color:#e23744}
    .zc-checkout{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;height:44px;border-radius:11px;font-weight:600}
    .zc-empty{text-align:center;padding:70px 20px;background:#fff;border:1px solid #eee;border-radius:16px}
    .zc-empty-icon{font-size:55px;line-height:1}
    @media(max-width:768px){
      .zc-cart-page{padding:0 4px 20px}
      .zc-item{grid-template-columns:54px minmax(0,1fr) auto;gap:10px;padding:13px 12px}
      .zc-item-image{width:54px;height:54px}
      .zc-item-total{grid-column:2;text-align:left;min-width:0}
      .zc-delete{grid-column:3;grid-row:1}
      .zc-qty{grid-column:2;justify-self:start;grid-row:2}
      .zc-summary{grid-template-columns:1fr;gap:16px}
      .zc-restaurant-head{padding:14px 12px}
      .zc-cart-body{padding:14px 12px}
    }
</style>

<div class="zc-cart-page">
    <h4 class="zc-cart-title"><i class="bi bi-bag"></i><span>Your Cart</span></h4>

    @if(empty($cartGroups))
        <div class="zc-empty">
            <div class="zc-empty-icon">🛒</div>
            <h5 class="mt-3 mb-1">Your cart is empty</h5>
            <p class="text-muted mb-3">Add something delicious from your favorite restaurant.</p>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-danger rounded-pill px-4">Browse Restaurants</a>
        </div>
    @else
        <div class="zc-cart-note">
            <i class="bi bi-info-circle-fill"></i>
            <span>Items from different restaurants are kept in separate carts. Choose <strong>Checkout this cart</strong> for the restaurant you want to order from.</span>
        </div>

        @foreach($cartGroups as $group)
            <div class="zc-restaurant-card">
                <div class="zc-restaurant-head">
                    <div>
                        <div class="zc-restaurant-name">{{ $group['restaurant'] }}</div>
                        <div class="zc-restaurant-meta">{{ count($group['items']) }} item{{ count($group['items']) === 1 ? '' : 's' }} in this cart</div>
                    </div>
                    <form action="{{ route('customer.cart.clear') }}" method="POST">
                        @csrf @method('DELETE')
                        <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                        <button type="submit" class="zc-clear-btn">Clear cart</button>
                    </form>
                </div>

                @foreach($group['items'] as $id => $item)
                    <div class="zc-item">
                        <div class="zc-item-image">
                            @if($item['image'])
                                <img src="{{ str_starts_with($item['image'], 'http') ? $item['image'] : asset('storage/'.$item['image']) }}" alt="{{ $item['name'] }}">
                            @else
                                🍽️
                            @endif
                        </div>
                        <div>
                            <div class="zc-item-name">{{ $item['name'] }}</div>
                            @if(!empty($item['options']))
                                <div class="zc-item-options">{{ collect($item['options'])->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}</div>
                            @endif
                            <div class="zc-item-price">₹{{ number_format($item['price'], 2) }}</div>
                        </div>
                        <div class="zc-qty">
                            <form action="{{ route('customer.cart.update') }}" method="POST" style="margin:0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                                <input type="hidden" name="food_item_id" value="{{ $id }}">
                                <input type="hidden" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}">
                                <button type="submit" aria-label="Decrease quantity">−</button>
                            </form>
                            <span>{{ $item['quantity'] }}</span>
                            <form action="{{ route('customer.cart.update') }}" method="POST" style="margin:0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                                <input type="hidden" name="food_item_id" value="{{ $id }}">
                                <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                <button type="submit" aria-label="Increase quantity">+</button>
                            </form>
                        </div>
                        <div class="zc-item-total">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                        <form action="{{ route('customer.cart.remove') }}" method="POST" style="margin:0">
                            @csrf @method('DELETE')
                            <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                            <input type="hidden" name="food_item_id" value="{{ $id }}">
                            <button type="submit" class="zc-delete" aria-label="Remove item"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @endforeach

                <div class="zc-cart-body">
                    @if($group['applied_coupon'])
                        <div class="zc-coupon-applied">
                            <span><i class="bi bi-check-circle-fill me-1"></i><strong>{{ $group['applied_coupon']->code }}</strong> applied</span>
                            <form action="{{ route('customer.coupon.remove') }}" method="POST" style="margin:0">
                                @csrf @method('DELETE')
                                <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                                <button type="submit" class="zc-remove">Remove</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('customer.coupon.apply') }}" method="POST" class="zc-coupon-form">
                            @csrf
                            <input type="hidden" name="restaurant_id" value="{{ $group['restaurant_id'] }}">
                            <input type="text" name="code" class="form-control" placeholder="Enter coupon code" autocomplete="off">
                            <button type="submit" class="btn btn-outline-danger">Apply</button>
                        </form>
                    @endif

                    <div class="zc-summary">
                        <div class="zc-summary-box">
                            <div class="zc-summary-row"><span>Subtotal</span><span>₹{{ number_format($group['subtotal'], 2) }}</span></div>
                            @if($group['discount'] > 0)
                                <div class="zc-summary-row discount"><span>Coupon Discount</span><span>- ₹{{ number_format($group['discount'], 2) }}</span></div>
                            @endif
                            <div class="zc-summary-row"><span>Delivery Fee</span><span>₹{{ number_format($group['delivery_fee'], 2) }}</span></div>
                            <hr>
                            <div class="zc-total"><span>Total</span><strong>₹{{ number_format($group['total'], 2) }}</strong></div>
                        </div>
                        <div>
                            <a href="{{ route('customer.checkout', ['restaurant_id' => $group['restaurant_id']]) }}" class="btn btn-danger zc-checkout">
                                Checkout this cart <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
