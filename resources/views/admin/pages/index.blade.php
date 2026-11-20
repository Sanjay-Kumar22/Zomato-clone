@extends('admin.layouts.app')
@section('title', 'CMS Pages')
@section('page-title', 'CMS Pages')
@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Create a page</div><div class="card-body">
            <form action="{{ route('admin.pages.store') }}" method="POST">
                @csrf
                <label class="form-label">Title</label><input class="form-control mb-3" name="title" maxlength="180" required>
                <label class="form-label">URL slug <span class="text-muted">(optional)</span></label><input class="form-control mb-3" name="slug" maxlength="200" placeholder="auto-generated-from-title">
                <label class="form-label">Page content</label><textarea class="form-control mb-3" name="body" rows="10" maxlength="50000" required></textarea>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" id="publishPage" checked><label class="form-check-label" for="publishPage">Publish page</label></div>
                <button class="btn btn-danger w-100">Save page</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="fw-bold mb-1">Content pages</h4><div class="text-muted small">Published pages are available at <code>/pages/{slug}</code>.</div></div></div>
        <div class="d-flex flex-column gap-3">
            @forelse($pages as $page)
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2"><strong>{{ $page->title }}</strong><span class="badge text-bg-{{ $page->is_published ? 'success' : 'secondary' }}">{{ $page->is_published ? 'Published' : 'Draft' }}</span></div>
                <form action="{{ route('admin.pages.update', $page) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label small">Title</label><input class="form-control form-control-sm" name="title" maxlength="180" value="{{ $page->title }}" required></div>
                        <div class="col-md-6"><label class="form-label small">Slug</label><input class="form-control form-control-sm" name="slug" maxlength="200" value="{{ $page->slug }}" required></div>
                        <div class="col-12"><label class="form-label small">Content (plain text)</label><textarea class="form-control form-control-sm" name="body" rows="5" maxlength="50000" required>{{ $page->body }}</textarea></div>
                        <div class="col-md-6 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_published" value="1" id="pagePublished{{ $page->id }}" {{ $page->is_published ? 'checked' : '' }}><label class="form-check-label" for="pagePublished{{ $page->id }}">Published</label></div></div>
                        <div class="col-md-6 d-flex gap-2 justify-content-end">
                            @if($page->is_published)<a class="btn btn-sm btn-outline-secondary" href="{{ route('cms.pages.show', $page->slug) }}" target="_blank" rel="noopener">View page</a>@endif
                            <button class="btn btn-sm btn-outline-primary">Save changes</button>
                        </div>
                    </div>
                </form>
                <form class="text-end mt-2" action="{{ route('admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Remove this CMS page?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
            </div></div>
            @empty
            <div class="card"><div class="card-body text-center text-muted py-5">No pages created yet.</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
