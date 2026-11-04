@extends('customer.layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-700 mb-0"><i class="bi bi-bell-fill me-2 text-danger"></i>Notifications</h4>
    @if(auth()->user()->unreadNotifications()->count() > 0)
    <form action="{{ route('customer.notifications.readAll') }}" method="POST">
        @csrf @method('PATCH')
        <button class="btn btn-outline-danger btn-sm rounded-pill">Mark all as read</button>
    </form>
    @endif
</div>

@forelse($notifications as $n)
<div class="card border-0 rounded-4 mb-2" style="box-shadow:0 2px 12px rgba(0,0,0,0.06); {{ $n->read_at ? '' : 'background:#fff9f9' }}">
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-600" style="font-size:14px">{{ $n->data['restaurant'] ?? 'Order Update' }}</div>
            <div class="text-muted" style="font-size:13px">{{ $n->data['message'] ?? '' }}</div>
            <div class="text-muted" style="font-size:11px">{{ $n->created_at->diffForHumans() }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($n->data['order_id']))
            <a href="{{ route('customer.orders.show', $n->data['order_id']) }}" class="btn btn-outline-danger btn-sm rounded-pill">View Order</a>
            @endif
            @if(!$n->read_at)
            <form action="{{ route('customer.notifications.read', $n->id) }}" method="POST">
                @csrf @method('PATCH')
                <button class="btn btn-light btn-sm rounded-pill">Mark read</button>
            </form>
            @endif
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <div style="font-size:60px">🔔</div>
    <h5 class="mt-3">No notifications yet</h5>
</div>
@endforelse

<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
