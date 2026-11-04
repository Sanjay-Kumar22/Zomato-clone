@extends('admin.layouts.app')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-bag-check me-2 text-danger"></i>Order Items</div>
            <div class="card-body p-4">
                @foreach($order->items as $item)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div>
                        {{ $item->name }} <span class="text-muted">× {{ $item->quantity }}</span>
                        @if(!empty($item->selected_options))
                        <div class="text-muted" style="font-size:12px">
                            {{ collect($item->selected_options)->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                        </div>
                        @endif
                    </div>
                    <span class="fw-600">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</span>
                </div>
                @endforeach

                <div class="d-flex justify-content-between pt-3" style="font-size:14px;color:#666">
                    <span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="d-flex justify-content-between" style="font-size:14px;color:#2e7d32">
                    <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                    <span>- ₹{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between" style="font-size:14px;color:#666">
                    <span>Delivery Fee</span><span>₹{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold fs-5 pt-2 border-top mt-2">
                    <span>Total</span><span class="text-danger">₹{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        @if($order->review)
        <div class="card">
            <div class="card-header"><i class="bi bi-star-fill me-2 text-warning"></i>Customer Review</div>
            <div class="card-body p-4">
                <div class="mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= $order->review->rating ? 'bi-star-fill' : 'bi-star' }}" style="color:#f9a825"></i>
                    @endfor
                </div>
                @if($order->review->food_rating || $order->review->delivery_rating)
                <div class="text-muted mb-2" style="font-size:12px">
                    @if($order->review->food_rating) Food: {{ $order->review->food_rating }}/5 @endif
                    @if($order->review->delivery_rating) · Delivery: {{ $order->review->delivery_rating }}/5 @endif
                </div>
                @endif
                @if($order->review->comment)
                <p class="mb-0" style="font-size:14px">{{ $order->review->comment }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-danger"></i>Order Info</div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Status</div>
                    @php
                        $colors = ['pending'=>['#fff8e1','#f57f17'],'confirmed'=>['#e3f2fd','#1565c0'],'preparing'=>['#fff3e0','#e65100'],'out_for_delivery'=>['#f3e5f5','#6a1b9a'],'delivered'=>['#e8f5e9','#2e7d32'],'cancelled'=>['#fce4ec','#880e4f']];
                        $c = $colors[$order->status] ?? ['#f5f5f5','#666'];
                    @endphp
                    <span class="badge px-3 py-2 rounded-pill" style="background:{{ $c[0] }};color:{{ $c[1] }}">
                        {{ ucfirst(str_replace('_',' ',$order->status)) }}
                    </span>
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Payment</div>
                    @if(in_array($order->payment_method, ['stripe', 'razorpay']))
                        @if($order->payment_status === 'paid')
                            <span class="badge px-3 py-2 rounded-pill" style="background:#e8f5e9;color:#2e7d32">Paid Online</span>
                        @else
                            <span class="badge px-3 py-2 rounded-pill" style="background:#fce4ec;color:#c62828">Payment Pending</span>
                        @endif
                    @else
                        <span class="badge px-3 py-2 rounded-pill" style="background:#fff8e1;color:#f57f17">Cash on Delivery</span>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Delivery Time</div>
                    <div class="fw-600">
                        @if($order->delivery_slot === 'ASAP' || !$order->delivery_slot)
                            ⚡ As soon as possible
                        @else
                            🕒 {{ $order->delivery_slot }}
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Placed On</div>
                    <div class="fw-600">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px">Restaurant</div>
                    <div class="fw-600">{{ $order->restaurant->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person me-2 text-danger"></i>Customer</div>
            <div class="card-body p-4">
                <div class="fw-600">{{ $order->user->name ?? '—' }}</div>
                <div class="text-muted" style="font-size:13px">{{ $order->user->email ?? '' }}</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-geo-alt me-2 text-danger"></i>Delivery Address</div>
            <div class="card-body p-4">
                @if($order->address)
                <div class="fw-600">{{ $order->address->label ?? 'Address' }}</div>
                <div class="text-muted" style="font-size:13px">{{ $order->address->full_address ?? '' }}</div>
                <div class="text-muted" style="font-size:13px">Pincode: {{ $order->address->pincode ?? '—' }}</div>
                @else
                <div class="text-muted">No address on file</div>
                @endif
            </div>
        </div>

        @if($order->deliveryAgent)
        <div class="card">
            <div class="card-header"><i class="bi bi-bicycle me-2 text-danger"></i>Delivery Agent</div>
            <div class="card-body p-4">
                <div class="fw-600">{{ $order->deliveryAgent->name }}</div>
                <div class="text-muted" style="font-size:13px">{{ $order->deliveryAgent->email }}</div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
