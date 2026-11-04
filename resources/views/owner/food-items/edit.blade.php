@extends('owner.layouts.app')
@section('title', 'Edit Food Item')
@section('page-title', 'Edit Food Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-danger"></i>
                Edit — {{ $foodItem->name }}
            </div>
            <div class="card-body p-4">
                <form action="{{ route('owner.restaurants.food-items.update',
                    [$restaurant, $foodItem]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-500">Item Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $foodItem->name) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-500">Price (₹)</label>
                            <input type="number" name="price" step="0.01"
                                class="form-control"
                                value="{{ old('price', $foodItem->price) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Category</label>
                        <select name="category_id" class="form-select">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ $foodItem->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" rows="3" class="form-control">
                            {{ old('description', $foodItem->description) }}
                        </textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Image</label>
                            @if($foodItem->image)
                                <div class="mb-2">
                                    <img src="{{ $foodItem->image_url }}" alt="{{ $foodItem->name }}"
                                        height="60" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="image"
                                class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Food Type</label>
                            <div class="mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        name="is_veg" id="is_veg" value="1"
                                        {{ $foodItem->is_veg ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_veg">
                                        🟢 Vegetarian
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Tags</label>
                        <input type="text" name="tags" class="form-control"
                            value="{{ old('tags', $foodItem->tags) }}"
                            placeholder="e.g. Spicy, Best Seller, Chef's Special (comma-separated)">
                        <small class="text-muted">Shown as badges on the menu to catch customers' attention.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i>Update Item
                        </button>
                        <a href="{{ route('owner.restaurants.food-items.index', $restaurant) }}"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add-ons / Variants -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-sliders me-2 text-danger"></i>
                Add-ons &amp; Variants (e.g. Size: Large, Extra Cheese)
            </div>
            <div class="card-body p-4">
                @forelse($foodItem->options as $opt)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong style="font-size:14px">{{ $opt->label }}</strong>: {{ $opt->value }}
                        <span class="text-muted" style="font-size:13px">
                            (@if($opt->extra_price > 0)+₹{{ number_format($opt->extra_price, 2) }}@else Free @endif)
                        </span>
                    </div>
                    <form action="{{ route('owner.restaurants.food-items.options.destroy', [$restaurant, $foodItem, $opt]) }}"
                        method="POST" onsubmit="return confirm('Remove this option?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-muted mb-3" style="font-size:13px">No add-ons yet. Add options like size or toppings that customers can choose from.</p>
                @endforelse

                <form action="{{ route('owner.restaurants.food-items.options.store', [$restaurant, $foodItem]) }}"
                    method="POST" class="row g-2 mt-3">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="label" class="form-control form-control-sm" placeholder="Label (e.g. Size)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="value" class="form-control form-control-sm" placeholder="Value (e.g. Large)" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="extra_price" step="0.01" min="0" class="form-control form-control-sm" placeholder="Extra ₹" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger btn-sm w-100">Add Option</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection