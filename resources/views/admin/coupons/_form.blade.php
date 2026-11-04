@php $c = $coupon; @endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-500">Coupon Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $c->code ?? '') }}" placeholder="e.g. WELCOME50" style="text-transform:uppercase">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-500">Applies To</label>
        <select name="restaurant_id" class="form-select">
            <option value="">All Restaurants (platform-wide)</option>
            @foreach($restaurants as $r)
                <option value="{{ $r->id }}" {{ old('restaurant_id', $c->restaurant_id ?? '') == $r->id ? 'selected' : '' }}>
                    {{ $r->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-500">Description</label>
    <input type="text" name="description" class="form-control" value="{{ old('description', $c->description ?? '') }}" placeholder="e.g. Flat 50 off on first order">
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Discount Type <span class="text-danger">*</span></label>
        <select name="discount_type" class="form-select">
            <option value="flat" {{ old('discount_type', $c->discount_type ?? '') == 'flat' ? 'selected' : '' }}>Flat (₹)</option>
            <option value="percent" {{ old('discount_type', $c->discount_type ?? '') == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Discount Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
            value="{{ old('discount_value', $c->discount_value ?? '') }}">
        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Max Discount (₹)</label>
        <input type="number" step="0.01" name="max_discount" class="form-control" value="{{ old('max_discount', $c->max_discount ?? '') }}" placeholder="Only for %">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Min Order Amount (₹)</label>
        <input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $c->min_order_amount ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Usage Limit</label>
        <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $c->usage_limit ?? '') }}" placeholder="Leave blank = unlimited">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-500">Expires On</label>
        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $c?->expires_at?->format('Y-m-d') ?? '') }}">
    </div>
</div>

<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
        {{ old('is_active', $c->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Active</label>
</div>
