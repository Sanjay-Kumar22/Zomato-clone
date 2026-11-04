@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('page-title', 'Coupons')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-ticket-perforated me-2 text-danger"></i>All Coupons</span>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Coupon
        </a>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('admin.coupons.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search by coupon code or description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i> Search</button>
                    @if(request()->hasAny(['search','status']))
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Code</th>
                    <th>Discount</th>
                    <th>Restaurant</th>
                    <th>Min Order</th>
                    <th>Used</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $c)
                <tr>
                    <td class="ps-4"><strong>{{ $c->code }}</strong>
                        @if($c->description)<div class="text-muted" style="font-size:12px">{{ $c->description }}</div>@endif
                    </td>
                    <td>
                        @if($c->discount_type === 'percent')
                            {{ $c->discount_value }}% off @if($c->max_discount)(max ₹{{ $c->max_discount }})@endif
                        @else
                            ₹{{ number_format($c->discount_value, 2) }} off
                        @endif
                    </td>
                    <td>{{ $c->restaurant->name ?? 'All Restaurants' }}</td>
                    <td>₹{{ number_format($c->min_order_amount, 2) }}</td>
                    <td>{{ $c->used_count }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}</td>
                    <td>{{ $c->expires_at ? $c->expires_at->format('d M Y') : 'Never' }}</td>
                    <td>
                        @if($c->is_active)
                            <span class="badge badge-approved px-2 py-1 rounded-pill">Active</span>
                        @else
                            <span class="badge badge-closed px-2 py-1 rounded-pill">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.coupons.edit', $c) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.coupons.destroy', $c) }}" method="POST"
                                onsubmit="return confirm('Delete this coupon?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">
                    @if(request()->hasAny(['search','status']))
                        No coupons match your search/filters. <a href="{{ route('admin.coupons.index') }}">Clear</a>
                    @else
                        No coupons yet.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $coupons->links() }}</div>
@endsection
