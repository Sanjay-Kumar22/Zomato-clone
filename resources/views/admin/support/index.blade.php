@extends('admin.layouts.app')
@section('title', 'Support Tickets')
@section('page-title', 'Support Tickets')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div><h4 class="fw-bold mb-1">Support tickets</h4><p class="text-muted mb-0">Review customer requests and send a response.</p></div>
    <div class="d-flex gap-2">
        @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $value => $label)
        <a class="btn btn-sm {{ request('status') === $value ? 'btn-danger' : 'btn-outline-secondary' }}" href="{{ route('admin.support.index', ['status' => $value]) }}">{{ $label }}</a>
        @endforeach
        <a class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.support.index') }}">All</a>
    </div>
</div>
<div class="d-flex flex-column gap-3">
    @forelse($tickets as $ticket)
    <div class="card"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div><h5 class="fw-bold mb-1">#{{ $ticket->id }} · {{ $ticket->subject }}</h5><div class="small text-muted">{{ $ticket->user->name }} · {{ $ticket->user->email }} · {{ ucfirst($ticket->category) }}@if($ticket->order_id) · <a href="{{ route('admin.orders.show', $ticket->order_id) }}">Order #{{ $ticket->order_id }}</a>@endif · {{ $ticket->created_at->format('d M Y, h:i A') }}</div></div>
            <span class="badge text-bg-{{ $ticket->status === 'resolved' ? 'success' : ($ticket->status === 'in_progress' ? 'primary' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
        </div>
        <div class="rounded-3 p-3 mb-3" style="background:#f7f8fb;white-space:pre-line">{{ $ticket->message }}</div>
        <form action="{{ route('admin.support.update', $ticket) }}" method="POST" class="row g-2 align-items-end">
            @csrf @method('PATCH')
            <div class="col-md-2"><label class="form-label small">Status</label><select class="form-select form-select-sm" name="status" required>
                @foreach(['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $value => $label)<option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>@endforeach
            </select></div>
            <div class="col-md-8"><label class="form-label small">Reply to customer</label><textarea class="form-control form-control-sm" rows="2" name="admin_reply" maxlength="3000" placeholder="Write a clear response...">{{ old('admin_reply', $ticket->admin_reply) }}</textarea></div>
            <div class="col-md-2"><button class="btn btn-danger btn-sm w-100">Update ticket</button></div>
        </form>
    </div></div>
    @empty
    <div class="card"><div class="card-body text-center text-muted py-5">No support tickets in this view.</div></div>
    @endforelse
</div>
@if($tickets->hasPages())<div class="mt-3">{{ $tickets->links() }}</div>@endif
@endsection
