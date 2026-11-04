@extends('customer.layouts.app')
@section('title', 'My Profile')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-person-circle text-danger me-2"></i>My Profile</h4>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
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
                        <label class="form-label fw-semibold" style="font-size:13px">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 rounded-pill">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card border-0 rounded-4 mt-3" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div style="font-size:32px">💎</div>
                <div>
                    <div class="text-muted" style="font-size:12px;font-weight:600">LOYALTY POINTS</div>
                    <div style="font-size:22px;font-weight:800;color:#c9971e">{{ $user->loyalty_points }} pts</div>
                    <div class="text-muted" style="font-size:11.5px">Redeem points at checkout — 1 point = ₹1 off</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Saved Addresses</h5>
            <button class="btn btn-danger rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="bi bi-plus me-1"></i>Add New
            </button>
        </div>

        @forelse($addresses as $addr)
        <div class="card border-0 rounded-4 mb-3" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4 d-flex align-items-start gap-3">
                <div style="font-size:28px">
                    {{ $addr->label === 'Home' ? '🏠' : ($addr->label === 'Work' ? '🏢' : '📍') }}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-bold">{{ $addr->label }}</span>
                        @if($addr->is_default)
                            <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:11px">Default</span>
                        @endif
                    </div>
                    <div style="font-size:14px;color:#444">{{ $addr->full_address }}</div>
                    <div style="font-size:12px;color:#888">Pincode: {{ $addr->pincode }}</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Edit"
                        onclick="openEditAddress({{ $addr->id }}, {{ json_encode($addr->label) }}, {{ json_encode($addr->full_address) }}, {{ json_encode($addr->pincode) }}, {{ $addr->is_default ? 'true' : 'false' }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @if(!$addr->is_default)
                    <form action="{{ route('customer.addresses.default', $addr) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-outline-success btn-sm" title="Set Default">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('customer.addresses.destroy', $addr) }}" method="POST"
                        onsubmit="return confirm('Remove this address?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5" style="background:white;border-radius:20px">
            <div style="font-size:56px">📍</div>
            <h5 class="mt-3">No addresses saved yet</h5>
            <p class="text-muted">Add an address for faster checkout</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-2" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('customer.address.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Label</label>
                        <select name="label" class="form-select" required>
                            <option value="Home">🏠 Home</option>
                            <option value="Work">🏢 Work</option>
                            <option value="Other">📍 Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Full Address</label>
                        <textarea name="full_address" class="form-control" rows="3"
                            placeholder="Flat no, building, street, area..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Pincode</label>
                        <input type="text" name="pincode" class="form-control"
                            placeholder="e.g. 110001" maxlength="10" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defCheck">
                        <label class="form-check-label" for="defCheck" style="font-size:13px">Set as default</label>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill">Save Address</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Address Modal (shared — populated via JS) -->
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-2" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAddressForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Label</label>
                        <select name="label" id="editLabelSelect" class="form-select" required>
                            <option value="Home">🏠 Home</option>
                            <option value="Work">🏢 Work</option>
                            <option value="Other">📍 Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Full Address</label>
                        <textarea name="full_address" id="editFullAddress" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px">Pincode</label>
                        <input type="text" name="pincode" id="editPincode" class="form-control"
                            placeholder="e.g. 110001" maxlength="10" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editDefCheck">
                        <label class="form-check-label" for="editDefCheck" style="font-size:13px">Set as default</label>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const editAddrUpdateUrlTemplate = "{{ route('customer.address.update', ['address' => '__ID__']) }}";

function openEditAddress(id, label, fullAddress, pincode, isDefault) {
    document.getElementById('editAddressForm').action = editAddrUpdateUrlTemplate.replace('__ID__', id);
    document.getElementById('editLabelSelect').value = label;
    document.getElementById('editFullAddress').value = fullAddress;
    document.getElementById('editPincode').value = pincode;
    document.getElementById('editDefCheck').checked = !!isDefault;

    new bootstrap.Modal(document.getElementById('editAddressModal')).show();
}
</script>
@endsection
