@extends('customer.layouts.app')
@section('title', 'My Orders')

@section('content')
<h4 class="fw-700 mb-4"><i class="bi bi-bag-check me-2 text-danger"></i>My Orders</h4>

<div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-3">
        <form action="{{ route('customer.orders.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:14px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                    <input type="text" name="search" class="form-control ps-4" style="border-radius:10px;"
                        placeholder="Search order #, or restaurant name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select" style="border-radius:10px;">
                    <option value="">All Statuses</option>
                    @foreach(['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-danger flex-grow-1" style="border-radius:10px;"><i class="bi bi-search"></i></button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;" title="Clear"><i class="bi bi-x"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

@forelse($orders as $order)
<div class="card border-0 rounded-4 mb-3" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="fw-700 mb-1">{{ $order->restaurant->name }}</h6>
                <div class="text-muted" style="font-size:13px">
                    Order #{{ $order->id }} ·
                    {{ $order->created_at->format('d M Y, h:i A') }}
                </div>
                <div class="mt-2" style="font-size:13px">
                    {{ $order->items->count() }} items ·
                    <strong>₹{{ number_format($order->total, 2) }}</strong>
                </div>
            </div>
            <div class="text-end">
                @php
                    $colors = [
                        'pending'          => ['bg'=>'#fff8e1','color'=>'#f57f17'],
                        'confirmed'        => ['bg'=>'#e3f2fd','color'=>'#1565c0'],
                        'preparing'        => ['bg'=>'#fff3e0','color'=>'#e65100'],
                        'out_for_delivery' => ['bg'=>'#f3e5f5','color'=>'#6a1b9a'],
                        'delivered'        => ['bg'=>'#e8f5e9','color'=>'#2e7d32'],
                        'cancelled'        => ['bg'=>'#fce4ec','color'=>'#880e4f'],
                    ];
                    $c = $colors[$order->status] ?? ['bg'=>'#f5f5f5','color'=>'#666'];
                @endphp
                <span class="badge px-3 py-2 rounded-pill"
                    style="background:{{ $c['bg'] }};color:{{ $c['color'] }};font-size:12px">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
                <div class="mt-2 d-flex gap-2 justify-content-end">
                    <a href="{{ route('customer.orders.show', $order) }}"
                        class="btn btn-outline-danger btn-sm">View Details</a>
                    @if($order->status === 'delivered')
                    <form action="{{ route('customer.orders.reorder', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Reorder
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <div style="font-size:60px">📦</div>
    @if(request()->hasAny(['search','status']))
        <h5 class="mt-3">No orders match your search/filters</h5>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-danger mt-2">Clear filters</a>
    @else
        <h5 class="mt-3">No orders yet</h5>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-danger mt-2">Order Now</a>
    @endif
</div>
@endforelse

{{ $orders->links() }}
@endsection