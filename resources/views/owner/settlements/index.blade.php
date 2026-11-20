@extends('owner.layouts.app')
@section('title', 'Payouts & Settlements')
@section('page-title', 'Payouts & Settlements')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Payouts & settlements</h4>
    <p class="text-muted mb-0">Request settlement for paid, delivered orders. Each order is assigned to one payout only.</p>
</div>
<div class="row g-3 mb-4">
    @forelse($restaurants as $restaurant)
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $restaurant->name }}</h5>
                        <div class="text-muted small">{{ $restaurant->eligible_orders_count }} eligible orders</div>
                    </div>
                    <span class="badge text-bg-light align-self-start">{{ number_format($commissionRate, 1) }}% commission</span>
                </div>
                <div class="mt-3 d-flex align-items-end justify-content-between">
                    <div>
                        <div class="text-muted small">Food subtotal before discounts</div>
                        <div class="fs-4 fw-bold">₹{{ number_format((float) $restaurant->eligible_gross_total, 2) }}</div>
                    </div>
                    @if($restaurant->eligible_orders_count > 0)
                    <form method="POST" action="{{ route('owner.settlements.store') }}" onsubmit="return confirm('Create a payout request for all eligible delivered orders at {{ addslashes($restaurant->name) }}?');">
                        @csrf
                        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                        <button class="btn btn-danger rounded-pill"><i class="bi bi-wallet2 me-1"></i>Request payout</button>
                    </form>
                    @else
                    <button class="btn btn-outline-secondary rounded-pill" disabled>No balance due</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="alert alert-info mb-0">No restaurants are linked to your account.</div></div>
    @endforelse
</div>

<div class="card">
    <div class="card-header">Payout history</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Request</th><th>Restaurant</th><th>Period</th><th>Orders</th><th>Commission</th><th>Payout</th><th>Status</th><th>Reference</th></tr></thead>
            <tbody>
            @forelse($settlements as $settlement)
            <tr>
                <td>#{{ $settlement->id }}</td>
                <td>{{ $settlement->restaurant->name }}</td>
                <td>{{ $settlement->period_start->format('d M Y') }} – {{ $settlement->period_end->format('d M Y') }}</td>
                <td>{{ $settlement->orders_count }}</td>
                <td>₹{{ number_format($settlement->commission_amount, 2) }}</td>
                <td class="fw-bold">₹{{ number_format($settlement->payout_amount, 2) }}</td>
                <td><span class="badge text-bg-{{ $settlement->status === 'paid' ? 'success' : ($settlement->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($settlement->status) }}</span>
                    @if($settlement->review_note)<div class="small text-muted mt-1">{{ $settlement->review_note }}</div>@endif
                </td>
                <td>{{ $settlement->payout_reference ?: '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No payout requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($settlements->hasPages())<div class="card-body">{{ $settlements->links() }}</div>@endif
</div>
@endsection
