@extends('admin.layouts.app')
@section('title', 'Edit Coupon')
@section('page-title', 'Edit Coupon')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2 text-danger"></i>Edit — {{ $coupon->code }}
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
                    @csrf @method('PUT')
                    @include('admin.coupons._form', ['coupon' => $coupon])
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i>Update Coupon
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
