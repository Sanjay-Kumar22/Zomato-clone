@extends('admin.layouts.app')
@section('title', 'Admin Audit Log')
@section('page-title', 'Admin Audit Log')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="fw-bold mb-1">Admin audit log</h4><p class="text-muted mb-0">A record of administrative settings and payout decisions.</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.settings.index') }}"><i class="bi bi-arrow-left me-1"></i>Settings</a>
</div>
<div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Subject</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
        <tr><td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td><td>{{ $log->actor->name ?? 'Deleted user' }}</td><td><code>{{ $log->action }}</code></td><td>{{ $log->subject_type }} {{ $log->subject_id }}</td><td>{{ $log->description }}</td><td>{{ $log->ip_address }}</td></tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted py-5">No admin actions have been logged.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-body">{{ $logs->links() }}</div></div>
@endsection
