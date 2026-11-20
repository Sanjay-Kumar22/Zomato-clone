@extends('admin.layouts.app')

@section('title', 'Analytics')
@section('page-title', 'Platform Analytics & Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group">
        @foreach([7 => '7 Days', 30 => '30 Days', 90 => '90 Days'] as $val => $label)
        <a href="{{ route('admin.analytics', ['range' => $val]) }}"
           class="btn btn-sm {{ $days == $val ? 'btn-danger' : 'btn-outline-danger' }}">{{ $label }}</a>
        @endforeach
    </div>
    <a class="btn btn-outline-success btn-sm" href="{{ route('admin.analytics.export', ['range' => $days]) }}">
        <i class="bi bi-download me-1"></i>Export CSV
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #388e3c, #81c784)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">₹{{ number_format($totalRevenue, 0) }}</div>
                    <div class="stat-label">Platform Revenue (fees + commission)</div>
                </div>
                <i class="bi bi-cash-stack stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #1976d2, #64b5f6)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalOrders }}</div>
                    <div class="stat-label">Delivered Orders</div>
                </div>
                <i class="bi bi-bag-check stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f57c00, #ffb74d)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">₹{{ number_format($avgOrderValue, 0) }}</div>
                    <div class="stat-label">Avg Order Value</div>
                </div>
                <i class="bi bi-graph-up stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-bar-chart-line me-2 text-danger"></i>Revenue Trend</div>
    <div class="card-body">
        <canvas id="revenueChart" height="80"></canvas>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-trophy me-2 text-danger"></i>Top Restaurants by Revenue</div>
            <div class="card-body p-0">
                @if($topRestaurants->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No delivered orders in this range yet.</p>
                @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">#</th><th>Restaurant</th><th>Orders</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($topRestaurants as $i => $row)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-600">{{ $row->restaurant->name ?? '—' }}</td>
                            <td>{{ $row->orders }}</td>
                            <td class="fw-bold">₹{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-egg-fried me-2 text-danger"></i>Best-Selling Items (Platform-wide)</div>
            <div class="card-body p-0">
                @if($bestSellers->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No delivered orders in this range yet.</p>
                @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">#</th><th>Item</th><th>Qty</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($bestSellers as $i => $item)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-600">{{ $item->name }}</td>
                            <td>{{ $item->total_qty }}</td>
                            <td class="fw-bold">₹{{ number_format($item->total_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($revenueSeries),
            borderColor: '#e8342a',
            backgroundColor: 'rgba(232,52,42,0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 2,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endsection
