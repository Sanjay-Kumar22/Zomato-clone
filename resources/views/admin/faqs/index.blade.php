@extends('admin.layouts.app')
@section('title', 'Help Center FAQs')
@section('page-title', 'Help Center FAQs')
@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Add help article</div><div class="card-body">
            <form action="{{ route('admin.faqs.store') }}" method="POST">
                @csrf
                <label class="form-label">Category</label><input class="form-control mb-3" name="category" maxlength="80" value="{{ old('category', 'General') }}" required>
                <label class="form-label">Question</label><input class="form-control mb-3" name="question" maxlength="255" required>
                <label class="form-label">Answer</label><textarea class="form-control mb-3" name="answer" rows="5" maxlength="5000" required></textarea>
                <label class="form-label">Display order</label><input class="form-control mb-3" type="number" name="sort_order" min="0" max="65535" value="0" required>
                <div class="form-check mb-3"><input class="form-check-input" id="faqPublish" type="checkbox" name="is_published" value="1" checked><label class="form-check-label" for="faqPublish">Publish to customer help center</label></div>
                <button class="btn btn-danger w-100">Add FAQ</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <h4 class="fw-bold mb-3">Help center content</h4>
        <div class="d-flex flex-column gap-3">
            @forelse($faqs as $faq)
            <div class="card"><div class="card-body">
                <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="row g-2">
                    @csrf @method('PATCH')
                    <div class="col-md-3"><label class="form-label small">Category</label><input class="form-control form-control-sm" name="category" maxlength="80" value="{{ $faq->category }}" required></div>
                    <div class="col-md-9"><label class="form-label small">Question</label><input class="form-control form-control-sm" name="question" maxlength="255" value="{{ $faq->question }}" required></div>
                    <div class="col-12"><label class="form-label small">Answer</label><textarea class="form-control form-control-sm" name="answer" rows="3" maxlength="5000" required>{{ $faq->answer }}</textarea></div>
                    <div class="col-md-3"><label class="form-label small">Order</label><input class="form-control form-control-sm" type="number" min="0" max="65535" name="sort_order" value="{{ $faq->sort_order }}" required></div>
                    <div class="col-md-5 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" id="faqPublish{{ $faq->id }}" type="checkbox" name="is_published" value="1" {{ $faq->is_published ? 'checked' : '' }}><label class="form-check-label" for="faqPublish{{ $faq->id }}">Published</label></div></div>
                    <div class="col-md-4 d-flex align-items-end gap-2"><button class="btn btn-sm btn-outline-primary flex-grow-1">Save changes</button></div>
                </form>
                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="mt-2 text-end" onsubmit="return confirm('Delete this help article?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
            </div></div>
            @empty
            <div class="card"><div class="card-body text-center text-muted py-5">No FAQ entries yet.</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
