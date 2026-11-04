@extends('owner.layouts.app')
@section('title', 'Disputes')
@section('page-title', 'Customer Complaints')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Complaints on Your Orders</span>
        <span class="badge bg-danger">{{ $disputes->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="p-3">
            <p class="text-muted" style="font-size:13px">
                Refunds are reviewed and processed by our support team. This is a read-only view so you can see
                what's been reported on your orders.
            </p>
            @forelse($disputes as $d)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-bold">{{ $d->user->name }}</span>
                        <span class="text-muted ms-2" style="font-size:12px">Order #{{ $d->order_id }}</span>
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border" style="font-size:11px">{{ $d->reason }}</span>
                            @if($d->status === 'open')
                                <span class="badge bg-warning text-dark" style="font-size:11px">Under Review</span>
                            @elseif($d->status === 'resolved')
                                <span class="badge bg-success" style="font-size:11px">Refunded</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:11px">Rejected</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-muted" style="font-size:11px">{{ $d->created_at->diffForHumans() }}</span>
                </div>
                <p class="mb-0 mt-2" style="font-size:14px">{{ $d->description }}</p>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <p class="mb-0">No complaints reported on your orders. 🎉</p>
            </div>
            @endforelse
        </div>

        <div class="p-3">{{ $disputes->links() }}</div>
    </div>
</div>
@endsection
