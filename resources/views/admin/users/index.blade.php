@extends('admin.layouts.app')

@section('title', 'Users')
@section('page-title', 'Manage Users')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" style="font-size:12px;font-weight:600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name or email">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;font-weight:600">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="restaurant_owner" {{ request('role') == 'restaurant_owner' ? 'selected' : '' }}>Restaurant Owner</option>
                    <option value="delivery_agent" {{ request('role') == 'delivery_agent' ? 'selected' : '' }}>Delivery Agent</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger btn-sm w-100">Filter</button>
            </div>
            @if(request('search') || request('role'))
            <div class="col-md-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2 text-danger"></i>All Users</span>
        <span class="badge bg-danger">{{ $users->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Name</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Delivery verification</th>
                    <th>Joined</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $roleColors = [
                        'customer'         => ['#e3f2fd', '#1565c0'],
                        'restaurant_owner' => ['#fff3e0', '#e65100'],
                        'delivery_agent'   => ['#e8f5e9', '#2e7d32'],
                        'admin'            => ['#fce4ec', '#880e4f'],
                    ];
                    $rc = $roleColors[$user->role] ?? ['#f5f5f5', '#666'];
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge px-2 py-1 rounded-pill" style="background:{{ $rc[0] }};color:{{ $rc[1] }};font-size:11px">
                            {{ ucwords(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td>
                        @if($user->role === 'delivery_agent')
                        <div class="d-flex flex-column gap-1">
                            <span class="badge {{ $user->kyc_status === 'approved' ? 'bg-success' : ($user->kyc_status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ ucfirst(str_replace('_', ' ', $user->kyc_status)) }}
                            </span>
                            @if($user->kyc_document)
                            <a class="small" href="{{ route('admin.users.kyc-document', $user) }}">View submitted KYC</a>
                            @endif
                            @if($user->kyc_status !== 'not_submitted' && $user->kyc_document)
                            <form action="{{ route('admin.users.kyc', $user) }}" method="POST" class="d-flex gap-1">
                                @csrf @method('PATCH')
                                <button name="kyc_status" value="approved" class="btn btn-success btn-sm">Approve</button>
                                <button name="kyc_status" value="rejected" class="btn btn-outline-danger btn-sm">Reject</button>
                            </form>
                            @endif
                        </div>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                    <td>
                        @if($user->id === auth()->id())
                            <span class="text-muted" style="font-size:12px">— (you)</span>
                        @else
                        <form action="{{ route('admin.users.role', $user) }}" method="POST" class="d-flex gap-1"
                              onsubmit="return confirm('Change role for {{ $user->name }}?');">
                            @csrf @method('PATCH')
                            <select name="role" class="form-select form-select-sm" style="width:160px;font-size:12px">
                                @foreach(['customer','restaurant_owner','delivery_agent','admin'] as $r)
                                <option value="{{ $r }}" {{ $user->role == $r ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $r)) }}
                                </option>
                                @endforeach
                            </select>
                            <button class="btn btn-danger btn-sm">✓</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection