@extends('owner.layouts.app')
@section('title', 'Edit Coupon')
@section('page-title', 'Edit Coupon')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-danger"></i>Edit Coupon
            </div>
            <div class="card-body p-4">
                <form action="{{ route('owner.coupons.update', $coupon) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('owner.coupons._form', ['coupon' => $coupon])
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('owner.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
