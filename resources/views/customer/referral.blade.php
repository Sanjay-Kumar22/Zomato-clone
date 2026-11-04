@extends('customer.layouts.app')
@section('title', 'Refer & Earn')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-gift text-danger me-2"></i>Refer & Earn</h4>

<div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07);background:linear-gradient(135deg,#e8342a,#ff6b6b)">
    <div class="card-body p-4 text-white text-center">
        <div style="font-size:44px">🎁</div>
        <h5 class="fw-bold mt-2">Give ₹50, Get ₹100</h5>
        <p class="mb-0" style="font-size:13.5px;opacity:0.9">
            Your friend gets ₹50 wallet credit when they sign up with your code.
            You get ₹100 credited to your wallet once their first order is delivered.
        </p>
    </div>
</div>

<div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-4">
        <label class="form-label fw-semibold" style="font-size:13px">Your Referral Code</label>
        <div class="d-flex gap-2 mb-3">
            <input type="text" class="form-control fw-bold text-center" id="referralCode"
                value="{{ $user->referral_code }}" readonly style="letter-spacing:2px;font-size:18px;color:#e8342a">
            <button class="btn btn-outline-danger" onclick="copyText('referralCode')">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>

        <label class="form-label fw-semibold" style="font-size:13px">Shareable Link</label>
        <div class="d-flex gap-2">
            <input type="text" class="form-control" id="referralLink"
                value="{{ route('register') }}?ref={{ $user->referral_code }}" readonly style="font-size:12.5px">
            <button class="btn btn-outline-danger" onclick="copyText('referralLink')">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>
    </div>
</div>

<h6 class="fw-bold mb-3">Friends You've Referred ({{ $referredUsers->count() }})</h6>

<div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
    <div class="card-body p-0">
        @forelse($referredUsers as $friend)
        <div class="d-flex justify-content-between align-items-center px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div>
                <div class="fw-600" style="font-size:14px">{{ $friend->name }}</div>
                <div class="text-muted" style="font-size:12px">Joined {{ $friend->created_at->format('d M Y') }}</div>
            </div>
            @if($friend->referral_bonus_paid)
                <span class="badge bg-success">₹100 Earned</span>
            @else
                <span class="badge bg-light text-dark border">Waiting for their first order</span>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <div style="font-size:44px">👥</div>
            <p class="mt-2 mb-0">No referrals yet. Share your code with friends!</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function copyText(id) {
    const el = document.getElementById(id);
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value);
    event.target.closest('button').innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
}
</script>
@endsection
