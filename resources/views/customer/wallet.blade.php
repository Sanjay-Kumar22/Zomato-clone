@extends('customer.layouts.app')
@section('title', 'My Wallet')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-wallet2 text-danger me-2"></i>My Wallet</h4>

<div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07);background:linear-gradient(135deg,#1E8E4E,#27AE60)">
    <div class="card-body p-4 text-white">
        <div style="font-size:13px;opacity:0.9">AVAILABLE BALANCE</div>
        <div style="font-size:34px;font-weight:800">₹{{ number_format($balance, 2) }}</div>
        <div style="font-size:12px;opacity:0.85" class="mt-1">
            Wallet balance is used automatically at checkout when you toggle "Pay from wallet" — refunds from cancelled orders and resolved complaints land here too.
        </div>
    </div>
</div>

<div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2 text-success"></i>Add Money</h6>
        <form action="{{ route('customer.wallet.topup') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-auto">
                @foreach([100, 200, 500, 1000] as $amt)
                <button type="button" class="btn btn-outline-success btn-sm quick-amt" data-amt="{{ $amt }}">₹{{ $amt }}</button>
                @endforeach
            </div>
            <div class="col-md-3">
                <input type="number" name="amount" id="topupAmount" class="form-control" min="10" max="10000" step="1" placeholder="Enter amount" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success rounded-pill px-4">Add Money</button>
            </div>
        </form>
        <div class="text-muted mt-2" style="font-size:11.5px">Secure payment via Razorpay — UPI, Cards, Netbanking, Wallets. Min ₹10, max ₹10,000 per transaction.</div>
    </div>
</div>

<h6 class="fw-bold mb-3">Transaction History</h6>

<div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-0">
        @forelse($transactions as $t)
        <div class="d-flex justify-content-between align-items-center px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div>
                <div class="fw-600" style="font-size:14px">{{ $t->reason_label }}</div>
                <div class="text-muted" style="font-size:12px">
                    {{ $t->created_at->format('d M Y, h:i A') }}
                    @if($t->order) · Order #{{ $t->order_id }} @endif
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold" style="font-size:15px;color:{{ $t->type === 'credit' ? '#1E8E4E' : '#e8342a' }}">
                    {{ $t->type === 'credit' ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                </div>
                <div class="text-muted" style="font-size:11px">Balance: ₹{{ number_format($t->balance_after, 2) }}</div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <div style="font-size:44px">💳</div>
            <p class="mt-2 mb-0">No wallet transactions yet.</p>
        </div>
        @endforelse
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>

<script>
document.querySelectorAll('.quick-amt').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('topupAmount').value = this.dataset.amt;
    });
});
</script>
@endsection
