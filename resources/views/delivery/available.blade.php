@extends('delivery.layouts.app')
@section('title', 'Available for Pickup')
@section('page-title', 'Available for Pickup')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2 text-primary"></i>Orders Ready for Pickup</span>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted" id="autoRefreshNote">Auto-refreshing every 30s</small>
            <span class="badge bg-primary">{{ $availableOrders->count() }} ready</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Order #</th>
                    <th>Restaurant</th>
                    <th>Pickup Address</th>
                    <th>Deliver To</th>
                    <th>Waiting Since</th>
                    <th>Delivery Time</th>
                    <th>Special Instructions</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($availableOrders as $order)
                @php
                    $pickupUrl = $order->restaurant->lat && $order->restaurant->lng
                        ? "https://www.google.com/maps/dir/?api=1&destination={$order->restaurant->lat},{$order->restaurant->lng}"
                        : "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($order->restaurant->address ?? '');
                    $waitingMinutes = $order->updated_at->diffInMinutes(now());
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">#{{ $order->id }}</td>
                    <td>{{ $order->restaurant->name }}</td>
                    <td style="font-size:12px">{{ $order->restaurant->address }}</td>
                    <td style="font-size:12px">{{ $order->address->full_address ?? '' }} ({{ $order->address->pincode ?? '' }})</td>
                    <td style="font-size:12px">
                        <span class="badge {{ $waitingMinutes > 15 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted' }}">
                            {{ $order->updated_at->diffForHumans(null, true) }} ago
                        </span>
                    </td>
                    <td style="font-size:12px">
                        @if($order->delivery_slot === 'ASAP' || !$order->delivery_slot)
                            <span class="badge bg-warning-subtle text-warning">⚡ ASAP</span>
                        @else
                            <span class="badge bg-info-subtle text-info">🕒 {{ $order->delivery_slot }}</span>
                        @endif
                    </td>
                    <td style="font-size:12px;max-width:220px">{{ $order->special_instructions ?: '—' }}</td>
                    <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ $pickupUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm" title="Navigate to restaurant">
                                <i class="bi bi-signpost-2"></i>
                            </a>
                            <form action="{{ route('delivery.orders.accept', $order) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-primary btn-sm">
                                    <i class="bi bi-bag-check me-1"></i>Accept
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No orders ready for pickup right now</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
    // Keeps this list fresh without the agent having to remember to hit
    // refresh — new pickups need to be seen quickly. Pauses while a form on
    // the page is mid-submit so an "Accept" click never gets interrupted.
    let formSubmitting = false;
    document.querySelectorAll('form').forEach(f => f.addEventListener('submit', () => formSubmitting = true));
    setInterval(() => {
        if (!formSubmitting && document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 30000);
</script>
@endsection
