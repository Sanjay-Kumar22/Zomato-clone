@extends('owner.layouts.app')
@section('title', 'Add Coupon')
@section('page-title', 'Add Coupon')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2 text-danger"></i>Add Coupon
            </div>
            <div class="card-body p-4">
                <form action="{{ route('owner.coupons.store') }}" method="POST">
                    @csrf
                    @include('owner.coupons._form', ['coupon' => null])
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-plus-lg me-1"></i>Create Coupon
                        </button>
                        <a href="{{ route('owner.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
