@extends('admin.layouts.app')
@section('title', 'Restaurants')
@section('page-title', 'Restaurants')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-shop me-2 text-danger"></i>All Restaurants</span>
        <a href="{{ route('admin.restaurants.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Restaurant
        </a>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('admin.restaurants.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search by name, pincode, or owner..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="approval" class="form-select form-select-sm">
                        <option value="">All (Approved + Pending)</option>
                        <option value="pending" {{ request('approval') === 'pending' ? 'selected' : '' }}>⏳ Pending only</option>
                        <option value="approved" {{ request('approval') === 'approved' ? 'selected' : '' }}>✅ Approved only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i> Search</button>
                    @if(request()->hasAny(['search','approval']))
                        <a href="{{ route('admin.restaurants.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Restaurant</th>
                    <th>Owner</th>
                    <th>Pincode</th>
                    <th>Approved</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restaurants as $r)
                <tr>
                    <td class="ps-4 text-muted">{{ $r->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($r->image)
                                <img src="{{ $r->image_url }}" alt="{{ $r->name }}" width="36" height="36" class="rounded-circle">
                            @else
                                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white" style="width:36px;height:36px">
                                    {{ strtoupper(substr($r->name,0,1)) }}
                                </div>
                            @endif
                            <span>{{ $r->name }}</span>
                        </div>
                    </td>
                    <td>{{ $r->owner->name ?? 'N/A' }}</td>
                    <td>{{ $r->pincode }}</td>
                    <td>
                        @if($r->is_approved)
                            <span class="badge badge-approved px-2 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i>Approved</span>
                        @else
                            <span class="badge badge-pending px-2 py-1 rounded-pill"><i class="bi bi-clock me-1"></i>Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($r->is_open)
                            <span class="badge badge-open px-2 py-1 rounded-pill">Open</span>
                        @else
                            <span class="badge badge-closed px-2 py-1 rounded-pill">Closed</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.restaurants.edit', $r) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.restaurants.approve', $r) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $r->is_approved ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    <i class="bi bi-{{ $r->is_approved ? 'x-circle' : 'check-circle' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.restaurants.destroy', $r) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-shop fs-1 d-block mb-2"></i>
                        @if(request()->hasAny(['search','approval']))
                            No restaurants match your search/filters. <a href="{{ route('admin.restaurants.index') }}">Clear</a>
                        @else
                            No restaurants found
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($restaurants->hasPages())
    <div class="card-footer bg-white">
        {{ $restaurants->links() }}
    </div>
    @endif
</div>
@endsection