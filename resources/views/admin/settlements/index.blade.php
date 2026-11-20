@extends('admin.layouts.app')
@section('title', 'Restaurant Payouts')
@section('page-title', 'Restaurant Payouts')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Restaurant payouts</h4>
    <p class="text-muted mb-0">Approve a request before recording payment. Paid orders cannot be included in another request.</p>
</div>
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.settlements.index') }}">All</a>
    @foreach(['pending', 'approved', 'paid', 'rejected'] as $status)
        <a class="btn btn-sm {{ request('status') === $status ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.settlements.index', ['status' => $status]) }}">{{ ucfirst($status) }}</a>
    @endforeach
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Request</th><th>Restaurant / Owner</th><th>Orders</th><th>Period</th><th>Gross</th><th>Commission</th><th>Payout</th><th>Status / action</th></tr></thead>
            <tbody>
            @forelse($settlements as $settlement)
            <tr>
                <td>#{{ $settlement->id }}<div class="small text-muted">{{ $settlement->created_at->format('d M Y') }}</div></td>
                <td><strong>{{ $settlement->restaurant->name }}</strong><div class="small text-muted">{{ $settlement->restaurant->owner->name ?? 'Owner removed' }}</div></td>
                <td>{{ $settlement->orders_count }}</td>
                <td>{{ $settlement->period_start->format('d M') }} – {{ $settlement->period_end->format('d M Y') }}</td>
                <td>₹{{ number_format($settlement->gross_amount, 2) }}</td>
                <td>₹{{ number_format($settlement->commission_amount, 2) }}</td>
                <td class="fw-bold">₹{{ number_format($settlement->payout_amount, 2) }}</td>
                <td style="min-width:245px">
                    <span class="badge text-bg-{{ $settlement->status === 'paid' ? 'success' : ($settlement->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($settlement->status) }}</span>
                    @if($settlement->payout_reference)<div class="small text-muted mt-1">Ref: {{ $settlement->payout_reference }}</div>@endif
                    @if($settlement->review_note)<div class="small text-muted">{{ $settlement->review_note }}</div>@endif
                    @if($settlement->status === 'pending' || $settlement->status === 'approved')
                    <form class="mt-2" method="POST" action="{{ route('admin.settlements.update', $settlement) }}">
                        @csrf @method('PATCH')
                        <div class="input-group input-group-sm mb-1">
                            <select class="form-select" name="status" required>
                                @if($settlement->status === 'pending')
                                    <option value="approved">Approve request</option>
                                @endif
                                @if($settlement->status === 'approved')
                                    <option value="paid">Mark paid</option>
                                @endif
                                <option value="rejected">Reject</option>
                            </select>
                            <button class="btn btn-danger">Save</button>
                        </div>
                        @if($settlement->status === 'approved')
                        <input class="form-control form-control-sm mb-1" name="payout_reference" maxlength="120" placeholder="Bank / UTR reference (required when paid)">
                        @endif
                        <input class="form-control form-control-sm" name="review_note" maxlength="1000" placeholder="Optional note">
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No payout requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($settlements->hasPages())<div class="card-body">{{ $settlements->links() }}</div>@endif
</div>
@endsection
