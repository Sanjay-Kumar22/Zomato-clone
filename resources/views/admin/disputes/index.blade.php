@extends('admin.layouts.app')
@section('title', 'Disputes')
@section('page-title', 'Refunds & Disputes')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Customer Complaints</span>
        <span class="badge bg-danger">{{ $disputes->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('admin.disputes.index') }}" method="GET" class="row g-2">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                @if(request('status'))
                <div class="col-md-2">
                    <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
                @endif
            </form>
        </div>

        <div class="p-3">
            @forelse($disputes as $d)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-bold">{{ $d->user->name }}</span>
                        <span class="text-muted ms-2" style="font-size:12px">
                            Order #{{ $d->order_id }} · {{ $d->order->restaurant->name ?? '—' }}
                        </span>
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border" style="font-size:11px">{{ $d->reason }}</span>
                            @if($d->status === 'open')
                                <span class="badge bg-warning text-dark" style="font-size:11px">Open</span>
                            @elseif($d->status === 'resolved')
                                <span class="badge bg-success" style="font-size:11px">Resolved · ₹{{ number_format($d->refund_amount, 2) }} refunded</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:11px">Rejected</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-muted" style="font-size:11px">{{ $d->created_at->diffForHumans() }}</span>
                </div>
                <p class="mb-2 mt-2" style="font-size:14px">{{ $d->description }}</p>

                @if($d->status !== 'open')
                    @if($d->resolution_note)
                    <div class="p-2 rounded-3" style="background:#F5F5F5;font-size:12.5px">
                        <strong>Note:</strong> {{ $d->resolution_note }}
                        <div class="text-muted" style="font-size:11px">by {{ $d->resolver->name ?? 'Admin' }} · {{ $d->resolved_at?->diffForHumans() }}</div>
                    </div>
                    @endif
                @else
                <form action="{{ route('admin.disputes.resolve', $d) }}" method="POST" class="row g-2 mt-1" onsubmit="return confirmResolve(this)">
                    @csrf
                    <div class="col-md-3">
                        <input type="number" name="refund_amount" step="0.01" min="0.01"
                            max="{{ $d->order->total }}" class="form-control form-control-sm"
                            placeholder="Refund ₹ amount">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="resolution_note" class="form-control form-control-sm" placeholder="Note (optional)">
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" name="action" value="refund" class="btn btn-success btn-sm flex-grow-1">
                            <i class="bi bi-wallet2 me-1"></i>Refund to Wallet
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm flex-grow-1">
                            Reject
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <p class="mb-0">No disputes found.</p>
            </div>
            @endforelse
        </div>

        <div class="p-3">{{ $disputes->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmResolve(form) {
    const clicked = document.activeElement;
    if (clicked && clicked.value === 'refund') {
        const amount = form.querySelector('[name="refund_amount"]').value;
        if (!amount || parseFloat(amount) <= 0) {
            alert('Please enter a refund amount.');
            return false;
        }
        return confirm('Refund ₹' + amount + ' to this customer\'s wallet?');
    }
    return confirm('Reject this dispute without a refund?');
}
</script>
@endpush
