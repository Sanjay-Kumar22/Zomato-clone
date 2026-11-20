@extends('customer.layouts.app')
@section('title', 'Free Delivery Membership')
@section('content')
<div class="container py-4" style="max-width:920px">
    <div class="rounded-4 p-4 p-md-5 text-white mb-4" style="background:linear-gradient(125deg,#241c15,#704129 65%,#e8342a)">
        <div class="small text-uppercase fw-bold mb-2" style="letter-spacing:.14em;color:#ffd9a1">ZomatoClone Membership</div>
        <h1 class="display-6 fw-bold mb-2">More meals. Less delivery fees.</h1>
        <p class="mb-0 opacity-75">Enjoy delivery-fee waivers on eligible orders from restaurants that serve your address.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card h-100"><div class="card-body p-4">
                <h4 class="fw-bold">Monthly Free Delivery</h4>
                <div class="display-5 fw-bold text-danger my-3">₹{{ number_format($price, 2) }} <span class="fs-6 text-muted fw-normal">/ month</span></div>
                <ul class="text-muted ps-3">
                    <li class="mb-2">Delivery fee is automatically waived at checkout while the plan is active.</li>
                    <li class="mb-2">Restaurant, packaging, taxes and platform fees still apply.</li>
                    <li>Paid securely from your ZomatoClone wallet; no recurring charge.</li>
                </ul>
                @if($activeMembership)
                    <div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>Active until {{ $activeMembership->expires_at->format('d M Y, h:i A') }}. Free delivery applies automatically.</div>
                @else
                    <form action="{{ route('customer.membership.purchase') }}" method="POST" onsubmit="return confirm('Use ₹{{ number_format($price, 2) }} from your wallet to activate one month?');">
                        @csrf
                        <button class="btn btn-danger btn-lg rounded-pill w-100">Activate membership · ₹{{ number_format($price, 2) }}</button>
                    </form>
                    <div class="small text-muted text-center mt-2">Wallet balance: ₹{{ number_format(auth()->user()->wallet_balance, 2) }}</div>
                @endif
            </div></div>
        </div>
        <div class="col-md-5">
            <div class="card"><div class="card-header">Membership history</div><div class="card-body">
                @forelse(auth()->user()->memberships()->latest('starts_at')->take(8)->get() as $entry)
                    <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                        <div><div class="fw-semibold">{{ ucfirst($entry->plan) }} plan</div><div class="small text-muted">{{ $entry->starts_at->format('d M Y') }} – {{ $entry->expires_at->format('d M Y') }}</div></div>
                        <div class="text-end"><div class="fw-semibold">₹{{ number_format($entry->price_paid, 2) }}</div><span class="badge text-bg-{{ $entry->expires_at->isFuture() ? 'success' : 'secondary' }}">{{ $entry->expires_at->isFuture() ? 'Active' : 'Expired' }}</span></div>
                    </div>
                @empty
                    <div class="text-muted text-center py-4">No membership purchases yet.</div>
                @endforelse
            </div></div>
        </div>
    </div>
</div>
@endsection
