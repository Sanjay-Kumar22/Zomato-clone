@extends('admin.layouts.app')
@section('title', 'Review Moderation')
@section('page-title', 'Review Moderation')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-shield-check me-2 text-danger"></i>Reported reviews</span>
        <form method="GET" action="{{ route('admin.review-reports.index') }}">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All reports</option>
                @foreach(['open', 'reviewed', 'closed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        @forelse($reports as $report)
        <article class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <strong>{{ $report->review->user->name ?? 'Deleted user' }}</strong>
                    <span class="text-muted">reviewed {{ $report->review->restaurant->name ?? 'a restaurant' }}</span>
                    <div class="text-warning">{{ str_repeat('★', (int) $report->review->rating) }}</div>
                </div>
                <span class="badge {{ $report->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                    {{ ucfirst($report->status) }}
                </span>
            </div>
            <p class="my-2">{{ $report->review->comment ?: 'No written comment.' }}</p>
            @if($report->review->photo)
            <img src="{{ asset('storage/'.$report->review->photo) }}" alt="Review attachment" style="max-width:180px;border-radius:12px">
            @endif
            <div class="small text-muted mt-2">
                Reported for {{ ucfirst($report->reason) }} by {{ $report->reporter->name ?? 'a user' }}
                @if($report->description) · {{ $report->description }} @endif
            </div>
            @if($report->status === 'open')
            <form action="{{ route('admin.review-reports.update', $report) }}" method="POST" class="d-flex flex-wrap gap-2 mt-3">
                @csrf @method('PATCH')
                <button name="action" value="hide" class="btn btn-danger btn-sm">Hide review</button>
                <button name="action" value="keep" class="btn btn-outline-success btn-sm">Keep visible</button>
                <button name="action" value="close" class="btn btn-outline-secondary btn-sm">Close report</button>
            </form>
            @endif
        </article>
        @empty
        <p class="text-center text-muted py-5 mb-0">No reports found.</p>
        @endforelse
        {{ $reports->links() }}
    </div>
</div>
@endsection
