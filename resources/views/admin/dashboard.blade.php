@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #e8342a, #ff6b6b)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalRestaurants }}</div>
                    <div class="stat-label">Total Restaurants</div>
                </div>
                <i class="bi bi-shop stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #1976d2, #64b5f6)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalCustomers }}</div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #388e3c, #81c784)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalOrders }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <i class="bi bi-bag-check stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.restaurants.index', ['approval' => 'pending']) }}" class="text-decoration-none">
        <div class="stat-card" style="background: linear-gradient(135deg, #f57c00, #ffb74d)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $pendingApprovals }}</div>
                    <div class="stat-label">Pending Approvals</div>
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
                    <div style="font-size:34px;font-weight:700;color:#1a1a2e;">₹{{ number_format($totalRevenue, 2) }}</div>
                    <div class="text-muted" style="font-size:12.5px;">From delivered orders only</div>
                </div>
                <a href="{{ route('admin.analytics') }}" class="btn btn-sm btn-outline-danger ms-auto">
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
                    <div class="col-4">
                        <div style="font-size:22px;font-weight:700;color:#1a1a2e;">₹{{ number_format($todayRevenue, 2) }}</div>
                        <div class="text-muted" style="font-size:11.5px;">Revenue</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:22px;font-weight:700;color:#1a1a2e;">{{ $todayOrders }}</div>
                        <div class="text-muted" style="font-size:11.5px;">New Orders</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:22px;font-weight:700;color:#1a1a2e;">{{ $todayNewUsers }}</div>
                        <div class="text-muted" style="font-size:11.5px;">New Signups</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals — actionable right here, no extra click needed -->
@if($pendingRestaurants->isNotEmpty())
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hourglass-split me-2 text-warning"></i>Awaiting Your Approval</span>
        @if($pendingApprovals > 5)
            <a href="{{ route('admin.restaurants.index', ['approval' => 'pending']) }}" class="btn btn-sm btn-outline-warning">
                View all {{ $pendingApprovals }} pending
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <tbody>
                @foreach($pendingRestaurants as $r)
                <tr>
                    <td class="ps-4">
                        <div class="fw-500">{{ $r->name }}</div>
                        <small class="text-muted">{{ $r->owner->name ?? 'N/A' }} · {{ $r->pincode }} · applied {{ $r->created_at->diffForHumans() }}</small>
                    </td>
                    <td class="text-end pe-4" style="width:220px">
                        <a href="{{ route('admin.restaurants.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Review
                        </a>
                        <form action="{{ route('admin.restaurants.approve', $r) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg"></i> Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row g-4">
    <!-- Recent Restaurants -->
    <div class="col-lg-6">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-shop me-2 text-danger"></i>Recent Restaurants</span>
            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Restaurant</th>
                        <th>Owner</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRestaurants as $r)
                    <tr>
                        <td class="ps-4 fw-500">{{ $r->name }}</td>
                        <td>{{ $r->owner->name ?? 'N/A' }}</td>
                        <td>
                            @if($r->is_approved)
                                <span class="badge badge-approved px-2 py-1 rounded-pill">Approved</span>
                            @else
                                <span class="badge badge-pending px-2 py-1 rounded-pill">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No restaurants yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-6">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bag-check me-2 text-success"></i>Recent Orders</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-success">View All</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-500 text-decoration-none">#{{ $order->id }}</a>
                            <div class="text-muted" style="font-size:11.5px">{{ $order->restaurant->name ?? '' }}</div>
                        </td>
                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:10.5px">
                                {{ ucfirst(str_replace('_',' ',$order->status)) }}
                            </span>
                        </td>
                        <td class="text-end pe-4 fw-500">₹{{ number_format($order->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
@endsection
