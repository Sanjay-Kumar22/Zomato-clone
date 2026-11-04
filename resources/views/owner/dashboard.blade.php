@extends('owner.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@if($pendingOrdersCount > 0)
<div class="alert border-0 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2"
     style="background:#fff3e0;color:#e65100;">
    <span><i class="bi bi-hourglass-split me-2"></i>
        You have <strong>{{ $pendingOrdersCount }}</strong> order{{ $pendingOrdersCount > 1 ? 's' : '' }} that need attention right now.
    </span>
    <a href="{{ route('owner.orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning fw-600">Review Orders</a>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #e8342a, #ff6b6b)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $myRestaurantsCount }}</div>
                    <div class="stat-label">My Restaurants</div>
                </div>
                <i class="bi bi-shop stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #6a1b9a, #ba68c8)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalFoodItems }}</div>
                    <div class="stat-label">Total Food Items</div>
                </div>
                <i class="bi bi-egg-fried stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('owner.orders.index') }}" class="text-decoration-none">
        <div class="stat-card" style="background: linear-gradient(135deg, #1976d2, #64b5f6)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalOrders }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <i class="bi bi-bag-check stat-icon"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('owner.orders.index', ['status' => 'pending']) }}" class="text-decoration-none">
        <div class="stat-card" style="background: linear-gradient(135deg, #f57c00, #ffb74d)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $pendingOrdersCount }}</div>
                    <div class="stat-label">Needs Attention</div>
                </div>
                <i class="bi bi-clock-history stat-icon"></i>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- Revenue + Today's snapshot -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-cash-stack me-2 text-success"></i>Lifetime Revenue</div>
            <div class="card-body d-flex align-items-center">
                <div>
                    <div style="font-size:34px;font-weight:700;color:#241C15;">₹{{ number_format($totalRevenue, 2) }}</div>
                    <div class="text-muted" style="font-size:12.5px;">From delivered orders only</div>
                </div>
                <a href="{{ route('owner.analytics') }}" class="btn btn-sm btn-outline-danger ms-auto">
                    <i class="bi bi-graph-up me-1"></i>Full Analytics
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar-day me-2 text-primary"></i>Today at a Glance</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div style="font-size:24px;font-weight:700;color:#241C15;">₹{{ number_format($todayRevenue, 2) }}</div>
                        <div class="text-muted" style="font-size:11.5px;">Revenue</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:24px;font-weight:700;color:#241C15;">{{ $todayOrders }}</div>
                        <div class="text-muted" style="font-size:11.5px;">New Orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- My Restaurants -->
    <div class="col-lg-7">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-shop me-2 text-danger"></i>My Restaurants</span>
            <a href="{{ route('owner.restaurants.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Restaurant</th>
                        <th>Approved</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myRestaurants as $r)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @if($r->image)
                                    <img src="{{ $r->image_url }}" alt="{{ $r->name }}"
                                        width="36" height="36" class="rounded-circle" style="object-fit:cover">
                                @else
                                    <div class="rounded-circle bg-danger d-flex align-items-center
                                        justify-content-center text-white"
                                        style="width:36px;height:36px">
                                        {{ strtoupper(substr($r->name,0,1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div>{{ $r->name }}</div>
                                    <small class="text-muted">{{ $r->pincode }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($r->is_approved)
                                <span class="badge badge-approved px-2 py-1 rounded-pill">✅ Approved</span>
                            @else
                                <span class="badge badge-pending px-2 py-1 rounded-pill">⏳ Pending</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('owner.restaurants.toggle', $r) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="badge px-2 py-1 rounded-pill border-0 {{ $r->is_open ? 'badge-open' : 'badge-closed' }}"
                                    style="cursor:pointer" title="Click to toggle">
                                    {{ $r->is_open ? '🟢 Open' : '🔴 Closed' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('owner.restaurants.food-items.index', $r) }}"
                                class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-egg-fried me-1"></i>Menu
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-shop fs-1 d-block mb-2"></i>
                            No restaurants yet
                            <br>
                            <a href="{{ route('owner.restaurants.create') }}"
                                class="btn btn-danger btn-sm mt-2">Add Restaurant</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-5">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bag-check me-2 text-success"></i>Recent Orders</span>
            <a href="{{ route('owner.orders.index') }}" class="btn btn-sm btn-outline-success">View All</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-500">#{{ $order->id }} — {{ $order->user->name ?? 'Customer' }}</div>
                            <small class="text-muted">{{ $order->restaurant->name ?? '' }} · ₹{{ number_format($order->total, 2) }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <span class="badge bg-light text-dark border" style="font-size:10.5px">
                                {{ ucfirst(str_replace('_',' ',$order->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="text-center text-muted py-4">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
@endsection
