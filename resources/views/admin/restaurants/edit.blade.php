@extends('admin.layouts.app')

@section('title', 'Edit Restaurant')
@section('page-title', 'Edit Restaurant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-danger"></i>Edit — {{ $restaurant->name }}
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.restaurants.update', $restaurant) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-500">Restaurant Name</label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $restaurant->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Address</label>
                        <textarea name="address" rows="2"
                            class="form-control">{{ old('address', $restaurant->address) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Pincode</label>
                            <input type="text" name="pincode" class="form-control"
                                value="{{ old('pincode', $restaurant->pincode) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Restaurant Image</label>
                            @if($restaurant->image)
                                <div class="mb-2">
                                    <img src="{{ $restaurant->image_url }}" alt="{{ $restaurant->name }}"
                                        height="60" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i>Update Restaurant
                        </button>
                        <a href="{{ route('admin.restaurants.index') }}"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection