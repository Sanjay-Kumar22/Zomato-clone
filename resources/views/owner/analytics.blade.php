@extends('owner.layouts.app')
@section('title', 'Analytics')
@section('page-title', 'Analytics & Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group">
        @foreach([7 => '7 Days', 30 => '30 Days', 90 => '90 Days'] as $val => $label)
        <a href="{{ route('owner.analytics', ['range' => $val]) }}"
           class="btn btn-sm {{ $days == $val ? 'btn-danger' : 'btn-outline-danger' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #388e3c, #81c784)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">₹{{ number_format($totalRevenue, 0) }}</div>
                    <div class="stat-label">Revenue (delivered orders)</div>
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

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 rounded-4 h-100" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line me-2 text-danger"></i>Revenue Trend</h6>
                <canvas id="revenueChart" height="90"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-4 h-100" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-danger"></i>Order Status</h6>
                @if($statusCounts->isEmpty())
                    <p class="text-muted text-center py-4">No orders yet in this range.</p>
                @else
                <canvas id="statusChart" height="180"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-danger"></i>Best-Selling Items</h6>
                @if($bestSellers->isEmpty())
                    <p class="text-muted text-center py-4">No delivered orders in this range yet.</p>
                @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Item</th><th>Qty Sold</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($bestSellers as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
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
    <div class="col-md-5">
        <div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-shop me-2 text-danger"></i>Revenue by Restaurant</h6>
                @if($revenueByRestaurant->isEmpty())
                    <p class="text-muted text-center py-4">No delivered orders in this range yet.</p>
                @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Restaurant</th><th>Orders</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($revenueByRestaurant as $row)
                        <tr>
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

@if($statusCounts->isNotEmpty())
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json($statusCounts->keys()),
        datasets: [{
            data: @json($statusCounts->values()),
            backgroundColor: ['#ffb74d', '#64b5f6', '#ba68c8', '#4dd0e1', '#81c784', '#e57373'],
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } }
});
@endif
</script>
@endsection
