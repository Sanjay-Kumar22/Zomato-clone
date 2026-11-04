@extends('owner.layouts.app')
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
                <form action="{{ route('owner.restaurants.update', $restaurant) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-500">Restaurant Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $restaurant->name) }}">
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $restaurant->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Cuisines</label>
                        <input type="text" name="cuisines" class="form-control"
                            value="{{ old('cuisines', $restaurant->cuisines) }}"
                            placeholder="e.g. North Indian, Chinese, Fast Food (comma-separated)">
                        <small class="text-muted">Shown as tags to customers, and used for cuisine filtering.</small>
                    </div>

                    @include('owner.restaurants._map-picker', ['restaurant' => $restaurant])

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Pincode</label>
                            <input type="text" name="pincode" class="form-control"
                                value="{{ old('pincode', $restaurant->pincode) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Image</label>
                            @if($restaurant->image)
                                <div class="mb-2">
                                    <img src="{{ $restaurant->image_url }}" alt="{{ $restaurant->name }}"
                                        height="60" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i>Update
                        </button>
                        <a href="{{ route('owner.restaurants.index') }}"
                            class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Photo Gallery -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-images me-2 text-danger"></i>Photo Gallery
                <small class="text-muted d-block" style="font-size:12px;font-weight:400;">
                    Show off your food, interior, and ambience — customers browse these before ordering.
                </small>
            </div>
            <div class="card-body">
                @if($restaurant->photos->isNotEmpty())
                <div class="row g-2 mb-3">
                    @foreach($restaurant->photos as $photo)
                    <div class="col-4 col-md-3">
                        <div class="position-relative">
                            <img src="{{ $photo->url }}" class="w-100 rounded" style="height:100px;object-fit:cover;">
                            <form action="{{ route('owner.restaurants.photos.destroy', [$restaurant, $photo]) }}" method="POST"
                                class="position-absolute top-0 end-0 m-1"
                                onsubmit="return confirm('Remove this photo?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger rounded-circle p-0"
                                    style="width:22px;height:22px;line-height:1;"><i class="bi bi-x"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('owner.restaurants.photos.store', $restaurant) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple required>
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-upload me-1"></i>Upload</button>
                    </div>
                    <small class="text-muted">You can select multiple photos at once (max 10, 2MB each).</small>
                    @error('photos.*')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </form>
            </div>
        </div>
    </div>
</div>
@endsection