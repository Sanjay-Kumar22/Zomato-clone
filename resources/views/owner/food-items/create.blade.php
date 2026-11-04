@extends('owner.layouts.app')
@section('title', 'Add Food Item')
@section('page-title', 'Add Food Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2 text-danger"></i>
                Add Food Item — {{ $restaurant->name }}
            </div>
            <div class="card-body p-4">
                <form action="{{ route('owner.restaurants.food-items.store', $restaurant) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-500">
                                Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="e.g. Butter Chicken">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-500">
                                Price (₹) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="price" step="0.01"
                                class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price') }}" placeholder="0.00">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id"
                            class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" rows="3"
                            class="form-control"
                            placeholder="Describe the dish...">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Image</label>
                            <input type="file" name="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Food Type</label>
                            <div class="mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        name="is_veg" id="is_veg" value="1"
                                        {{ old('is_veg') ? 'checked' : '' }}>
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
                            value="{{ old('tags') }}"
                            placeholder="e.g. Spicy, Best Seller, Chef's Special (comma-separated)">
                        <small class="text-muted">Shown as badges on the menu to catch customers' attention.</small>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-plus-lg me-1"></i>Add Item
                        </button>
                        <a href="{{ route('owner.restaurants.food-items.index', $restaurant) }}"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection