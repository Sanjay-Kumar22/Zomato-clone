@extends('delivery.layouts.app')
@section('title', 'Delivery History')
@section('page-title', 'Delivery History')

@section('styles')
<style>
    .history-row { cursor: pointer; }
    .history-row:hover { background: #fafdff; }
    .history-detail { display: none; background: #fafdff; }
    .history-detail.show { display: table-row; }
    .item-line { padding: 5px 0; border-bottom: 1px solid #eee; font-size: 13px; }
    .item-line:last-child { border-bottom: none; }
    .pay-badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history me-2 text-success"></i>Delivery History
        <span class="badge bg-success ms-1">{{ $myDeliveredOrders->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('delivery.orders.history') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search order #, customer, or restaurant..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-success btn-sm flex-grow-1"><i class="bi bi-search"></i></button>
                    @if(request()->filled('search'))
                        <a href="{{ route('delivery.orders.history') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4"></th>
                    <th>Order #</th>
                    <th>Restaurant</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Delivered On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myDeliveredOrders as $order)
                <tr class="history-row" onclick="document.getElementById('hist-{{ $order->id }}').classList.toggle('show')">
                    <td class="ps-4"><i class="bi bi-chevron-down text-muted"></i></td>
                    <td class="fw-bold">#{{ $order->id }}</td>
                    <td>{{ $order->restaurant->name }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td style="font-size:12px">{{ $order->items->count() }} items</td>
                    <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                    <td>
                        @if(in_array($order->payment_method, ['stripe', 'razorpay']))
                            <span class="pay-badge" style="background:#e8f5e9;color:#2e7d32">Paid Online</span>
                        @else
                            <span class="pay-badge" style="background:#fff8e1;color:#f57f17">COD</span>
                        @endif
                    </td>
                    <td style="font-size:12px">{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                </tr>
                <tr id="hist-{{ $order->id }}" class="history-detail">
                    <td></td>
                    <td colspan="7" class="pb-3">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="fw-600 mb-2" style="font-size:13px">Items Delivered</div>
                                @foreach($order->items as $item)
                                <div class="item-line d-flex justify-content-between">
                                    <span>
                                        {{ $item->name }} <span class="text-muted">× {{ $item->quantity }}</span>
                                        @if(!empty($item->selected_options))
                                            <div class="text-muted" style="font-size:11px">
                                                {{ collect($item->selected_options)->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                                            </div>
                                        @endif
                                    </span>
                                    <span class="fw-600">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="col-md-5">
                                <div class="fw-600 mb-2" style="font-size:13px">Delivered To</div>
                                @if($order->address)
                                <div class="text-muted" style="font-size:13px">
                                    <div class="fw-500 text-dark">{{ $order->address->label ?? 'Address' }}</div>
                                    {{ $order->address->full_address ?? '' }}
                                    <div>Pincode: {{ $order->address->pincode ?? '—' }}</div>
                                </div>
                                @else
                                <div class="text-muted" style="font-size:13px">No address on file</div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">
                    @if(request()->filled('search'))
                        No deliveries match "{{ request('search') }}". <a href="{{ route('delivery.orders.history') }}">Clear search</a>
                    @else
                        No deliveries completed yet
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $myDeliveredOrders->links() }}</div>
@endsection
