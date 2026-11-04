@extends('admin.layouts.app')
@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted mb-1" style="font-size:11.5px">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Order #, customer, or restaurant"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:11.5px">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ',$s)) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:11.5px">Restaurant</label>
                <select name="restaurant_id" class="form-select form-select-sm">
                    <option value="">All Restaurants</option>
                    @foreach($restaurants as $r)
                    <option value="{{ $r->id }}" {{ request('restaurant_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:11.5px">From date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:11.5px">To date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-danger btn-sm w-100">Filter</button>
            </div>
            @if(request()->hasAny(['search','status','restaurant_id','from_date','to_date']))
            <div class="col-12">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-link btn-sm p-0" style="font-size:12.5px">
                    <i class="bi bi-x-circle me-1"></i>Clear all filters
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bag-check me-2 text-danger"></i>All Orders</span>
        <span class="badge bg-danger">{{ $orders->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Order #</th>
                    <th>Customer</th>
                    <th>Restaurant</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $colors = ['pending'=>['#fff8e1','#f57f17'],'confirmed'=>['#e3f2fd','#1565c0'],'preparing'=>['#fff3e0','#e65100'],'out_for_delivery'=>['#f3e5f5','#6a1b9a'],'delivered'=>['#e8f5e9','#2e7d32'],'cancelled'=>['#fce4ec','#880e4f']];
                    $c = $colors[$order->status] ?? ['#f5f5f5','#666'];
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">#{{ $order->id }}</td>
                    <td>{{ $order->user->name ?? '—' }}</td>
                    <td>{{ $order->restaurant->name ?? '—' }}</td>
                    <td>{{ $order->items->count() }} items</td>
                    <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                    <td>
                        @if(in_array($order->payment_method, ['stripe', 'razorpay']))
                            @if($order->payment_status === 'paid')
                                <span class="badge px-2 py-1 rounded-pill" style="background:#e8f5e9;color:#2e7d32;font-size:11px">Paid Online</span>
                            @else
                                <span class="badge px-2 py-1 rounded-pill" style="background:#fce4ec;color:#c62828;font-size:11px">Payment Pending</span>
                            @endif
                        @else
                            <span class="badge px-2 py-1 rounded-pill" style="background:#fff8e1;color:#f57f17;font-size:11px">COD</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge px-2 py-1 rounded-pill"
                            style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:11px">
                            {{ ucfirst(str_replace('_',' ',$order->status)) }}
                        </span>
                    </td>
                    <td style="font-size:12px">{{ $order->created_at->format('d M, h:i A') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
