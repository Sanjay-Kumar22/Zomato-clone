@extends('customer.layouts.app')
@section('title', 'Customer Support')
@section('content')
<div class="container py-4" style="max-width:1000px">
    <div class="mb-4"><h2 class="fw-bold">Customer support</h2><p class="text-muted">Tell us what happened. You can follow each request here.</p></div>
    <div class="row g-4">
        <div class="col-lg-5">
            @if($faqs->isNotEmpty())
            <div class="card mb-4"><div class="card-header fw-bold"><i class="bi bi-question-circle text-danger me-2"></i>Quick answers</div><div class="card-body">
                <div class="accordion accordion-flush" id="helpFaqs">
                    @foreach($faqs as $category => $items)
                    <div class="small fw-bold text-muted text-uppercase mt-2 mb-1">{{ $category }}</div>
                    @foreach($items as $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeading{{ $faq->id }}"><button class="accordion-button collapsed px-0 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#faqAnswer{{ $faq->id }}">{{ $faq->question }}</button></h2>
                        <div id="faqAnswer{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#helpFaqs"><div class="accordion-body px-0 pt-1 pb-3 text-muted" style="white-space:pre-line">{{ $faq->answer }}</div></div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div></div>
            @endif
            <div class="card"><div class="card-header fw-bold">Create a support request</div><div class="card-body">
                <form action="{{ route('customer.support.store') }}" method="POST">
                    @csrf
                    <label class="form-label">Topic</label><input class="form-control mb-3" name="subject" maxlength="180" value="{{ old('subject') }}" required>
                    <label class="form-label">Category</label><select class="form-select mb-3" name="category" required>
                        @foreach(['order' => 'Order', 'payment' => 'Payment', 'delivery' => 'Delivery', 'account' => 'Account', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="form-label">Related order <span class="text-muted">(optional)</span></label>
                    <select class="form-select mb-3" name="order_id"><option value="">Not order-specific</option>
                        @foreach($orders as $order)<option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>#{{ $order->id }} · {{ ucfirst(str_replace('_', ' ', $order->status)) }} · {{ $order->created_at->format('d M Y') }}</option>@endforeach
                    </select>
                    <label class="form-label">What can we help with?</label><textarea class="form-control mb-3" rows="5" name="message" minlength="10" maxlength="3000" required>{{ old('message') }}</textarea>
                    <button class="btn btn-danger rounded-pill w-100">Submit request</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">Your requests</h5><span class="text-muted small">{{ $tickets->total() }} total</span></div>
            <div class="d-flex flex-column gap-3">
                @forelse($tickets as $ticket)
                <div class="card"><div class="card-body p-4">
                    <div class="d-flex justify-content-between gap-3"><div><div class="fw-bold">#{{ $ticket->id }} · {{ $ticket->subject }}</div><div class="small text-muted">{{ ucfirst($ticket->category) }}@if($ticket->order_id) · Order #{{ $ticket->order_id }}@endif · {{ $ticket->created_at->format('d M Y, h:i A') }}</div></div><span class="badge text-bg-{{ $ticket->status === 'resolved' ? 'success' : ($ticket->status === 'in_progress' ? 'primary' : 'warning') }} align-self-start">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></div>
                    <p class="mt-3 mb-2" style="white-space:pre-line">{{ $ticket->message }}</p>
                    @if($ticket->admin_reply)<div class="rounded-3 p-3 mt-3" style="background:#f5f7fb"><div class="small fw-bold text-danger mb-1">Support reply · {{ $ticket->responded_at?->diffForHumans() }}</div><div style="white-space:pre-line">{{ $ticket->admin_reply }}</div></div>@endif
                </div></div>
                @empty
                <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-chat-square-text fs-1 d-block mb-2"></i>You haven't sent a support request yet.</div></div>
                @endforelse
            </div>
            @if($tickets->hasPages())<div class="mt-3">{{ $tickets->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
