@extends('owner.layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span></span>
    @if(auth()->user()->unreadNotifications()->count() > 0)
    <form action="{{ route('owner.notifications.readAll') }}" method="POST">
        @csrf @method('PATCH')
        <button class="btn btn-outline-danger btn-sm rounded-pill">Mark all as read</button>
    </form>
    @endif
</div>

@forelse($notifications as $n)
<div class="card border-0 rounded-4 mb-2" style="box-shadow:0 2px 12px rgba(0,0,0,0.06); {{ $n->read_at ? '' : 'background:#fff9f9' }}">
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-600" style="font-size:14px">New Order — {{ $n->data['customer'] ?? 'Customer' }}</div>
            <div class="text-muted" style="font-size:13px">{{ $n->data['message'] ?? '' }}</div>
            <div class="text-muted" style="font-size:11px">{{ $n->created_at->diffForHumans() }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($n->data['order_id']))
            <a href="{{ route('owner.orders.index') }}" class="btn btn-outline-danger btn-sm rounded-pill">View Orders</a>
            @endif
            @if(!$n->read_at)
            <form action="{{ route('owner.notifications.read', $n->id) }}" method="POST">
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
