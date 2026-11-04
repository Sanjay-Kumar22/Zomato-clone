@extends('delivery.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@if(!auth()->user()->is_online)
<div class="alert border-0 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2"
     style="background:#fff3e0;color:#e65100;">
    <span><i class="bi bi-power me-2"></i>You're currently <strong>offline</strong> — go online to start receiving new pickup alerts.</span>
    <form action="{{ route('delivery.toggleOnline') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-warning fw-600">Go Online</button>
    </form>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('delivery.orders.available') }}" class="text-decoration-none">
        <div class="stat-card" style="background: linear-gradient(135deg, #1565c0, #64b5f6)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $availableCount }}</div>
                    <div class="stat-label">Available for Pickup</div>
                </div>
                <i class="bi bi-box-seam stat-icon"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #e65100, #ffb74d)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $myActiveOrders->count() }}</div>
                    <div class="stat-label">Out for Delivery (Mine)</div>
                </div>
                <i class="bi bi-bicycle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #6a1b9a, #ba68c8)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $deliveredToday->count() }}</div>
                    <div class="stat-label">Delivered Today</div>
                </div>
                <i class="bi bi-calendar-check stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('delivery.orders.history') }}" class="text-decoration-none">
        <div class="stat-card" style="background: linear-gradient(135deg, #2e7d32, #81c784)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $deliveredCount }}</div>
                    <div class="stat-label">Total Delivered</div>
                </div>
                <i class="bi bi-check2-circle stat-icon"></i>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Earnings -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-cash-coin me-2 text-success"></i>Earnings (delivery fees + tips)</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-md-4">
                <div class="text-muted" style="font-size:12.5px;text-transform:uppercase;letter-spacing:.05em;">Today</div>
                <div style="font-size:26px;font-weight:700;color:#1a1a2e;">₹{{ number_format($earningsToday, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted" style="font-size:12.5px;text-transform:uppercase;letter-spacing:.05em;">This Week</div>
                <div style="font-size:26px;font-weight:700;color:#1a1a2e;">₹{{ number_format($earningsThisWeek, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted" style="font-size:12.5px;text-transform:uppercase;letter-spacing:.05em;">All Time</div>
                <div style="font-size:26px;font-weight:700;color:#1a1a2e;">₹{{ number_format($earningsTotal, 2) }}</div>
            </div>
        </div>
        @if($tipsTotal > 0)
        <div class="text-center mt-2 text-muted" style="font-size:12px;">
            <i class="bi bi-heart-fill text-danger me-1"></i>Includes ₹{{ number_format($tipsTotal, 2) }} in tips from happy customers 🎉
        </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bicycle me-2 text-warning"></i>My Active Deliveries</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Order #</th>
                    <th>Restaurant</th>
                    <th>Customer</th>
                    <th>Deliver To</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Picked Up</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myActiveOrders as $order)
                @php
                    $mapsUrl = $order->address && $order->address->lat && $order->address->lng
                        ? "https://www.google.com/maps/dir/?api=1&destination={$order->address->lat},{$order->address->lng}"
                        : "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($order->address->full_address ?? '');
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">#{{ $order->id }}</td>
                    <td>{{ $order->restaurant->name }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td style="font-size:12px">{{ $order->address->full_address ?? '' }} ({{ $order->address->pincode ?? '' }})</td>
                    <td style="font-size:12px">{{ $order->items->count() }} items</td>
                    <td class="fw-bold">₹{{ number_format($order->total, 2) }}</td>
                    <td style="font-size:12px">
                        <span title="{{ $order->updated_at }}">{{ $order->updated_at->diffForHumans() }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm" title="Navigate to customer">
                                <i class="bi bi-signpost-2"></i>
                            </a>
                            <a href="{{ route('delivery.chat.show', $order) }}" class="btn btn-outline-secondary btn-sm position-relative" title="Chat with customer">
                                <i class="bi bi-chat-dots"></i>
                                <span class="chat-unread-badge badge bg-danger rounded-pill d-none"
                                    data-order-id="{{ $order->id }}"
                                    style="position:absolute;top:-6px;right:-6px;font-size:9px;padding:3px 5px">0</span>
                            </a>
                            <form action="{{ route('delivery.orders.delivered', $order) }}" method="POST"
                                  onsubmit="return confirm('Mark this order as delivered?');">
                                @csrf @method('PATCH')
                                <button class="btn btn-success btn-sm">
                                    <i class="bi bi-check2 me-1"></i>Mark Delivered
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No active deliveries right now. Check "Available for Pickup" in the sidebar for new orders.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>


@if($myActiveOrders->isNotEmpty())
<script>
// Share this device's live location for every order currently out for delivery,
// so the customer's tracking map shows a real rider position instead of a guess.
// Runs quietly in the background — no visible UI, just a periodic POST.
(function() {
    const orderIds = @json($myActiveOrders->pluck('id'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!navigator.geolocation || !orderIds.length) return;

    function sendLocation(lat, lng) {
        orderIds.forEach(function(id) {
            fetch('/delivery/orders/' + id + '/location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ lat: lat, lng: lng }),
            }).catch(() => {});
        });
    }

    navigator.geolocation.watchPosition(
        (pos) => sendLocation(pos.coords.latitude, pos.coords.longitude),
        () => {},
        { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
    );
})();
</script>
@endif

@if($myActiveOrders->isNotEmpty())
<script>
// Chat notifications — badges each order's chat icon with its unread count
// and shows a browser notification when a new customer message arrives.
// Works only while a tab of the site is open (browser Notification API),
// not a true push notification to a closed browser.
(function() {
    const unreadUrl = "{{ route('delivery.chat.unread') }}";
    let lastCounts = {};

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function checkUnread() {
        fetch(unreadUrl).then(res => res.json()).then(data => {
            const counts = data.counts || {};
            let totalNew = 0;

            document.querySelectorAll('.chat-unread-badge').forEach(function(badge) {
                const orderId = badge.dataset.orderId;
                const count = counts[orderId] || 0;
                badge.textContent = count;
                badge.classList.toggle('d-none', count === 0);

                if (count > (lastCounts[orderId] || 0)) {
                    totalNew += (count - (lastCounts[orderId] || 0));
                }
                lastCounts[orderId] = count;
            });

            if (totalNew > 0 && 'Notification' in window && Notification.permission === 'granted') {
                new Notification('New message from a customer', {
                    body: totalNew > 1 ? totalNew + ' new messages across your deliveries.' : 'You have a new message — tap to reply.',
                    icon: '/pwa-icons/icon-192.png',
                });
            }
        }).catch(() => {});
    }

    setInterval(checkUnread, 6000);
    checkUnread();
})();
</script>
@endif
@endsection
