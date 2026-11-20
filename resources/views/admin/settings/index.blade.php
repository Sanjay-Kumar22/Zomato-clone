@extends('admin.layouts.app')
@section('title', 'Platform Settings')
@section('page-title', 'Platform Settings')
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div><h4 class="fw-bold mb-1">Platform settings</h4><p class="text-muted mb-0">These rates are snapshotted into new checkouts and settlement requests.</p></div>
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-clock-history me-1"></i>Audit log</a>
    </div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">GST (%)</label><input class="form-control" type="number" step="0.01" min="0" max="100" name="gst_percent" value="{{ old('gst_percent', $settings['gst_percent']) }}" required><div class="form-text">Applied to the discounted food subtotal.</div></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Platform fee (%)</label><input class="form-control" type="number" step="0.01" min="0" max="100" name="platform_fee_percent" value="{{ old('platform_fee_percent', $settings['platform_fee_percent']) }}" required><div class="form-text">Charged to customers on eligible orders.</div></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Restaurant commission (%)</label><input class="form-control" type="number" step="0.01" min="0" max="100" name="commission_percent" value="{{ old('commission_percent', $settings['commission_percent']) }}" required><div class="form-text">Deducted from net food value for settlement calculation.</div></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Monthly free-delivery membership (₹)</label><input class="form-control" type="number" step="0.01" min="0.01" name="membership_monthly_price" value="{{ old('membership_monthly_price', $settings['membership_monthly_price']) }}" required><div class="form-text">Membership is paid from the customer's wallet; it does not auto-renew.</div></div>
            </div>
            <button class="btn btn-danger mt-4"><i class="bi bi-save me-1"></i>Save settings</button>
        </form>
    </div></div>
</div></div>
@endsection
