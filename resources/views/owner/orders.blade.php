@extends('owner.layouts.app')
@section('title', 'Orders')
@section('page-title', 'Manage Orders')

@section('styles')
<style>
    .order-row { cursor: pointer; }
    .order-row:hover { background: #fff9f9; }
    .detail-row { display: none; background: #faf9f9; }
    .detail-row.show { display: table-row; }
    .item-line { padding: 6px 0; border-bottom: 1px solid #eee; font-size: 13px; }
    .item-line:last-child { border-bottom: none; }
    .opt-tag { font-size: 11px; color: #888; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bag-check me-2 text-danger"></i>All Orders</span>
        <span class="badge bg-danger">{{ $orders->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('owner.orders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search order #, customer name, email, phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i> Search</button>
                    @if(request()->hasAny(['search','status']))
                        <a href="{{ route('owner.orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters"><i class="bi bi-x"></i></a>
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
                    <th>Customer</th>
                    <th>Restaurant</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $colors = ['pending'=>['#fff8e1','#f57f17'],'confirmed'=>['#e3f2fd','#1565c0'],'preparing'=>['#fff3e0','#e65100'],'out_for_delivery'=>['#f3e5f5','#6a1b9a'],'delivered'=>['#e8f5e9','#2e7d32'],'cancelled'=>['#fce4ec','#880e4f']];
                    $c = $colors[$order->status] ?? ['#f5f5f5','#666'];
                @endphp
                <tr class="order-row" onclick="document.getElementById('detail-{{ $order->id }}').classList.toggle('show')">
                    <td class="ps-4"><i class="bi bi-chevron-down text-muted"></i></td>
                    <td class="fw-bold">#{{ $order->id }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ $order->restaurant->name }}</td>
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
                    <td onclick="event.stopPropagation()">
                        @if(in_array($order->status, ['pending','confirmed','preparing']))
                        <form action="{{ route('owner.orders.status', $order) }}" method="POST" class="d-flex gap-1">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="width:140px;font-size:12px">
                                @foreach(['confirmed','preparing','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_',' ',$s)) }}
                                </option>
                                @endforeach
                            </select>
                            <button class="btn btn-danger btn-sm">✓</button>
                        </form>
                        @elseif($order->status === 'out_for_delivery')
                            <span class="text-muted" style="font-size:12px">
                                <i class="bi bi-bicycle me-1"></i>With delivery agent
                            </span>
                        @else
                            <span class="text-muted" style="font-size:12px">—</span>
                        @endif
                    </td>
                </tr>

                <!-- Expandable order details -->
                <tr id="detail-{{ $order->id }}" class="detail-row">
                    <td></td>
                    <td colspan="9" class="pb-3">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="fw-600 mb-2" style="font-size:13px">Order Items</div>
                                @if($order->special_instructions)
                                <div class="alert border-0 rounded-3 py-2 px-3 mb-3" style="background:#fff7e8;color:#684c1c;font-size:12px">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    <strong>Customer instructions:</strong> {{ $order->special_instructions }}
                                </div>
                                @endif
                                @foreach($order->items as $item)
                                <div class="item-line d-flex justify-content-between">
                                    <div>
                                        {{ $item->name }} <span class="text-muted">× {{ $item->quantity }}</span>
                                        @if(!empty($item->selected_options))
                                            <div class="opt-tag">
                                                {{ collect($item->selected_options)->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="fw-600">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</span>
                                </div>
                                @endforeach

                                <div class="d-flex justify-content-between pt-2" style="font-size:12px;color:#888">
                                    <span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span>
                                </div>
                                @if($order->discount > 0)
                                <div class="d-flex justify-content-between" style="font-size:12px;color:#2e7d32">
                                    <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                                    <span>- ₹{{ number_format($order->discount, 2) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between" style="font-size:12px;color:#888">
                                    <span>Delivery Fee</span><span>₹{{ number_format($order->delivery_fee, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="fw-600 mb-2" style="font-size:13px">Delivery Time</div>
                                <div class="text-muted mb-3" style="font-size:13px">
                                    @if($order->delivery_slot === 'ASAP' || !$order->delivery_slot)
                                        ⚡ As soon as possible
                                    @else
                                        🕒 {{ $order->delivery_slot }}
                                    @endif
                                </div>

                                <div class="fw-600 mb-2" style="font-size:13px">Delivery Address</div>
                                @if($order->address)
                                <div class="text-muted" style="font-size:13px">
                                    <div class="fw-500 text-dark">{{ $order->address->label ?? 'Address' }}</div>
                                    {{ $order->address->full_address ?? $order->address->address ?? '' }}
                                    <div>Pincode: {{ $order->address->pincode ?? '—' }}</div>
                                </div>
                                @else
                                <div class="text-muted" style="font-size:13px">No address on file</div>
                                @endif

                                <a href="{{ route('owner.orders.print', $order) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm mt-3" onclick="event.stopPropagation()">
                                    <i class="bi bi-printer me-1"></i>Print Kitchen Slip
                                </a>

                                <div class="fw-600 mt-3 mb-1" style="font-size:13px">Delivery Agent</div>
                                @if($order->deliveryAgent)
                                <div class="text-muted" style="font-size:13px">
                                    <i class="bi bi-person-check text-success me-1"></i>{{ $order->deliveryAgent->name }}
                                    @if($order->deliveryAgent->phone)
                                        · {{ $order->deliveryAgent->phone }}
                                    @endif
                                </div>
                                @elseif($order->status === 'preparing')
                                <div class="text-muted" style="font-size:13px">
                                    <i class="bi bi-clock-history me-1"></i>Waiting for a delivery agent to accept
                                </div>
                                @elseif(in_array($order->status, ['pending','confirmed']))
                                <div class="text-muted" style="font-size:13px">
                                    Not yet ready for pickup
                                </div>
                                @else
                                <div class="text-muted" style="font-size:13px">—</div>
                                @endif

                                @if(in_array($order->status, ['pending', 'confirmed', 'preparing']))
                                <form action="{{ route('owner.orders.status', $order) }}" method="POST" class="mt-3" onclick="event.stopPropagation()">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    <label class="form-label fw-semibold mb-1" style="font-size:12px">Kitchen note for customer</label>
                                    <textarea name="cooking_notes" class="form-control form-control-sm mb-2" rows="2"
                                        maxlength="2000" placeholder="Share a preparation update or substitution...">{{ $order->cooking_notes }}</textarea>
                                    <button class="btn btn-outline-danger btn-sm">Save note</button>
                                </form>
                                @elseif($order->cooking_notes)
                                <div class="mt-3" style="font-size:12px">
                                    <div class="fw-semibold mb-1">Kitchen note</div>
                                    <div class="text-muted">{{ $order->cooking_notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">
                    @if(request()->hasAny(['search','status']))
                        No orders match your search/filters. <a href="{{ route('owner.orders.index') }}">Clear filters</a>
                    @else
                        No orders yet
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
