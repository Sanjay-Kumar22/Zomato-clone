@extends('admin.layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-circle me-2 text-danger"></i>My Profile
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center mb-4">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                style="width:96px;height:96px;border-radius:50%;object-fit:cover;">
                        @else
                            <div style="width:96px;height:96px;border-radius:50%;background:#FDECEA;
                                display:flex;align-items:center;justify-content:center;font-size:38px;margin:0 auto;color:#e8342a">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="mt-3">
                            <label class="btn btn-outline-danger btn-sm rounded-pill">
                                <i class="bi bi-camera me-1"></i>Change Photo
                                <input type="file" name="photo" accept="image/*" hidden>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>

                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
