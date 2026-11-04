@extends('owner.layouts.app')
@section('title', 'My Restaurants')
@section('page-title', 'My Restaurants')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-shop me-2 text-danger"></i>My Restaurants</span>
        <a href="{{ route('owner.restaurants.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Restaurant
        </a>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('owner.restaurants.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search by name, pincode, cuisine, or address..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i> Search</button>
                    @if(request()->hasAny(['search']))
                        <a href="{{ route('owner.restaurants.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Restaurant</th>
                    <th>Pincode</th>
                    <th>Approved</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restaurants as $r)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            @if($r->image)
                                <img src="{{ $r->image_url }}" alt="{{ $r->name }}"
                                    width="36" height="36" class="rounded-circle">
                            @else
                                <div class="rounded-circle bg-danger d-flex align-items-center
                                    justify-content-center text-white"
                                    style="width:36px;height:36px">
                                    {{ strtoupper(substr($r->name,0,1)) }}
                                </div>
                            @endif
                            <span>{{ $r->name }}</span>
                        </div>
                    </td>
                    <td>{{ $r->pincode }}</td>
                    <td>
                        @if($r->is_approved)
                            <span class="badge badge-approved px-2 py-1 rounded-pill">
                                ✅ Approved
                            </span>
                        @else
                            <span class="badge badge-pending px-2 py-1 rounded-pill">
                                ⏳ Pending
                            </span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('owner.restaurants.toggle', $r) }}"
                            method="POST">
                            @csrf @method('PATCH')
                            <button class="badge border-0 px-2 py-1 rounded-pill
                                {{ $r->is_open ? 'badge-open' : 'badge-closed' }}">
                                {{ $r->is_open ? '🟢 Open' : '🔴 Closed' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('owner.restaurants.food-items.index', $r) }}"
                                class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-egg-fried me-1"></i>Menu
                            </a>
                            <a href="{{ route('owner.restaurants.delivery-zones.index', $r) }}"
                                class="btn btn-sm btn-outline-success">
                                <i class="bi bi-geo-alt me-1"></i>Delivery Zones
                            </a>
                            <a href="{{ route('owner.restaurants.hours.edit', $r) }}"
                                class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-clock me-1"></i>Hours
                            </a>
                            <a href="{{ route('owner.restaurants.edit', $r) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-shop fs-1 d-block mb-2"></i>
                        @if(request()->filled('search'))
                            No restaurants match "{{ request('search') }}"
                            <br>
                            <a href="{{ route('owner.restaurants.index') }}" class="btn btn-outline-danger btn-sm mt-2">Clear search</a>
                        @else
                            No restaurants yet
                            <br>
                            <a href="{{ route('owner.restaurants.create') }}"
                                class="btn btn-danger btn-sm mt-2">Add Restaurant</a>
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