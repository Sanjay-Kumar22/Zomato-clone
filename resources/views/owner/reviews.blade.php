@extends('owner.layouts.app')
@section('title', 'Reviews')
@section('page-title', 'Customer Reviews')

@section('styles')
<style>
    .review-card { border-bottom: 1px solid #f0f0f0; padding: 18px 0; }
    .review-card:last-child { border-bottom: none; }
    .reply-box { background: #FFF8F5; border-radius: 10px; padding: 12px 14px; margin-top: 10px; font-size: 13px; }
    .stars { color: #f9a825; font-size: 13px; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-star me-2 text-danger"></i>Customer Reviews</span>
        <span class="badge bg-danger">{{ $reviews->total() }} total</span>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <form action="{{ route('owner.reviews.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="restaurant" class="form-select form-select-sm">
                        <option value="">All Restaurants</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" {{ request('restaurant') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select form-select-sm">
                        <option value="">All Reviews</option>
                        <option value="unreplied" {{ request('filter') === 'unreplied' ? 'selected' : '' }}>Not Replied Yet</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1"><i class="bi bi-search"></i> Filter</button>
                    @if(request()->hasAny(['restaurant','filter']))
                        <a href="{{ route('owner.reviews.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>

        <div class="p-4">
            @forelse($reviews as $review)
            <div class="review-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-600">{{ $review->user->name }}</div>
                        <div class="text-muted" style="font-size:12px">{{ $review->restaurant->name }} · {{ $review->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </span>
                </div>
                @if($review->comment)
                <div class="mt-2" style="font-size:14px">{{ $review->comment }}</div>
                @endif

                @if($review->owner_reply)
                <div class="reply-box">
                    <div class="fw-600 mb-1"><i class="bi bi-reply-fill me-1 text-danger"></i>Your reply</div>
                    <div>{{ $review->owner_reply }}</div>
                    <div class="text-muted mt-1" style="font-size:11px">{{ $review->owner_reply_at->diffForHumans() }}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                    data-bs-toggle="collapse" data-bs-target="#replyForm{{ $review->id }}">
                    <i class="bi bi-pencil me-1"></i>Edit Reply
                </button>
                @else
                <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                    data-bs-toggle="collapse" data-bs-target="#replyForm{{ $review->id }}">
                    <i class="bi bi-reply me-1"></i>Reply
                </button>
                @endif

                <div class="collapse mt-2" id="replyForm{{ $review->id }}">
                    <form action="{{ route('owner.reviews.reply', $review) }}" method="POST">
                        @csrf
                        <textarea name="owner_reply" class="form-control form-control-sm mb-2" rows="2"
                            placeholder="Write a reply to this review..." required>{{ $review->owner_reply }}</textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Post Reply</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-star" style="font-size:40px"></i>
                <p class="mt-2 mb-0">No reviews yet.</p>
            </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
        <div class="p-3">{{ $reviews->links() }}</div>
        @endif
    </div>
</div>
@endsection
