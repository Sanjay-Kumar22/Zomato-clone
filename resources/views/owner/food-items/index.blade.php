@extends('owner.layouts.app')
@section('title', 'Food Items')
@section('page-title', 'Food Items — ' . $restaurant->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-egg-fried me-2 text-danger"></i>{{ $restaurant->name }} — Menu</span>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.restaurants.index') }}"
                class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <a href="{{ route('owner.restaurants.food-items.export', $restaurant) }}" target="_blank"
                class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>Export Menu (PDF)
            </a>
            <a href="{{ route('owner.restaurants.food-items.create', $restaurant) }}"
                class="btn btn-danger btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Add Food Item
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('owner.restaurants.food-items.index', $restaurant) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:13px"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-4"
                            placeholder="Search item name, description, tags..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="veg" class="form-select form-select-sm">
                        <option value="">Veg / Non-Veg</option>
                        <option value="veg" {{ request('veg') === 'veg' ? 'selected' : '' }}>🟢 Veg only</option>
                        <option value="nonveg" {{ request('veg') === 'nonveg' ? 'selected' : '' }}>🔴 Non-Veg only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="availability" class="form-select form-select-sm">
                        <option value="">Any Availability</option>
                        <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>✅ Available</option>
                        <option value="unavailable" {{ request('availability') === 'unavailable' ? 'selected' : '' }}>⏸ Unavailable</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i></button>
                    @if(request()->hasAny(['search','category','veg','availability']))
                        <a href="{{ route('owner.restaurants.food-items.index', $restaurant) }}" class="btn btn-outline-secondary btn-sm" title="Clear filters"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Type</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($foodItems as $item)
                <tr>
                    <td class="ps-4 text-muted">{{ $item->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($item->image)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                    width="40" height="40"
                                    class="rounded" style="object-fit:cover">
                            @else
                                <div class="rounded bg-light d-flex align-items-center
                                    justify-content-center text-muted"
                                    style="width:40px;height:40px;font-size:18px">
                                    🍽️
                                </div>
                            @endif
                            <div>
                                <div class="fw-500">{{ $item->name }}</div>
                                @if($item->description)
                                    <small class="text-muted">
                                        {{ Str::limit($item->description, 40) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $item->category->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="fw-500">₹{{ number_format($item->price, 2) }}</td>
                    <td>
                        @if($item->is_veg)
                            <span class="badge badge-veg px-2 py-1 rounded-pill">
                                🟢 Veg
                            </span>
                        @else
                            <span class="badge badge-nonveg px-2 py-1 rounded-pill">
                                🔴 Non-Veg
                            </span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('owner.restaurants.food-items.toggle',
                            [$restaurant, $item]) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="badge border-0
                                {{ $item->is_available ? 'badge-available' : 'badge-unavailable' }}
                                px-2 py-1 rounded-pill">
                                {{ $item->is_available ? '✅ Available' : '⏸ Unavailable' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('owner.restaurants.food-items.edit',
                                [$restaurant, $item]) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('owner.restaurants.food-items.destroy',
                                [$restaurant, $item]) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this item?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <div style="font-size:40px">🍽️</div>
                        @if(request()->hasAny(['search','category','veg','availability']))
                            <div class="mt-2">No items match your search/filters</div>
                            <a href="{{ route('owner.restaurants.food-items.index', $restaurant) }}" class="btn btn-outline-danger btn-sm mt-2">Clear filters</a>
                        @else
                            <div class="mt-2">No food items yet</div>
                            <a href="{{ route('owner.restaurants.food-items.create', $restaurant) }}"
                                class="btn btn-danger btn-sm mt-2">Add First Item</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($foodItems->hasPages())
    <div class="card-footer bg-white">
        {{ $foodItems->links() }}
    </div>
    @endif
</div>
@endsection