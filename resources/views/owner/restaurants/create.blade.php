@extends('owner.layouts.app')
@section('title', 'Add Restaurant')
@section('page-title', 'Add Restaurant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shop me-2 text-danger"></i>Add New Restaurant
            </div>
            <div class="card-body p-4">
                <form action="{{ route('owner.restaurants.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-500">
                            Restaurant Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. Punjabi Dhaba">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">
                            Address <span class="text-danger">*</span>
                        </label>
                        <textarea name="address" rows="2"
                            class="form-control @error('address') is-invalid @enderror"
                            placeholder="Full address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Cuisines</label>
                        <input type="text" name="cuisines" class="form-control"
                            value="{{ old('cuisines') }}"
                            placeholder="e.g. North Indian, Chinese, Fast Food (comma-separated)">
                        <small class="text-muted">Shown as tags to customers, and used for cuisine filtering.</small>
                    </div>

                    @include('owner.restaurants._map-picker', ['restaurant' => null])

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">
                                Pincode <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="pincode"
                                class="form-control @error('pincode') is-invalid @enderror"
                                value="{{ old('pincode') }}"
                                placeholder="e.g. 110001">
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Restaurant Image</label>
                            <input type="file" name="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-plus-lg me-1"></i>Submit for Approval
                        </button>
                        <a href="{{ route('owner.restaurants.index') }}"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection