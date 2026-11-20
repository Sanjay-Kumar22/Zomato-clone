@extends('admin.layouts.app')
@section('title', 'Homepage Banners')
@section('page-title', 'Homepage Banners')

@section('content')
<style>
    .banner-admin-hero { position:relative; overflow:hidden; padding:24px; border-radius:20px; color:#fff; background:radial-gradient(circle at 85% 15%,rgba(255,255,255,.25),transparent 25%),linear-gradient(120deg,#2c1a17,#b9362d 68%,#ee7a4c); box-shadow:0 16px 36px rgba(114,40,31,.18); }
    .banner-admin-hero::after { position:absolute; width:210px;height:210px;right:9%;top:-105px;border:1px solid rgba(255,255,255,.2);border-radius:50%;box-shadow:0 0 0 22px rgba(255,255,255,.05),0 0 0 45px rgba(255,255,255,.035);content:""; }
    .banner-admin-hero > * { position:relative;z-index:1; }
    .banner-admin-layout { display:grid;grid-template-columns:minmax(290px, .82fr) minmax(0, 1.6fr);gap:22px;align-items:start; }
    .banner-preview { position:relative;display:flex;align-items:flex-end;min-height:180px;overflow:hidden;padding:18px;border-radius:16px;background:linear-gradient(135deg,#3b211c,#ad493a);color:white;isolation:isolate; }
    .banner-preview::after { position:absolute;inset:0;z-index:-1;background:linear-gradient(90deg,rgba(17,12,10,.82),rgba(17,12,10,.12)),linear-gradient(0deg,rgba(17,12,10,.38),transparent 65%);content:""; }
    .banner-preview img { position:absolute;inset:0;z-index:-2;width:100%;height:100%;object-fit:cover; }
    .banner-preview-title { max-width:90%;font-size:20px;font-weight:800;line-height:1.15; }
    .banner-preview-subtitle { max-width:90%;margin-top:5px;color:rgba(255,255,255,.85);font-size:12px; }
    .banner-preview-label { position:absolute;top:12px;left:12px;padding:5px 9px;border:1px solid rgba(255,255,255,.3);border-radius:30px;background:rgba(31,22,18,.3);font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase; }
    .banner-list-card { overflow:hidden;border:1px solid #eee8e2;border-radius:17px;background:#fff;box-shadow:0 8px 24px rgba(36,28,21,.045); }
    .banner-list-image { display:block;width:100%;height:148px;object-fit:cover; }
    .banner-input:focus { border-color:#e99c88;box-shadow:0 0 0 .2rem rgba(232,52,42,.1); }
    @media(max-width:991.98px) { .banner-admin-layout { grid-template-columns:1fr; } }
</style>

<div class="banner-admin-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="small text-uppercase fw-bold mb-2" style="letter-spacing:.14em;color:#ffe0cb">Customer homepage</div>
            <h2 class="h3 fw-bold mb-1">Make a delicious first impression.</h2>
            <div class="small" style="color:rgba(255,255,255,.78)">Create, preview and publish the campaigns customers see while discovering food.</div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge rounded-pill text-bg-light px-3 py-2">{{ $banners->where('is_active', true)->count() }} live</span>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,.17)">{{ $banners->count() }} total</span>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><strong>Please check the banner details.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="banner-admin-layout">
    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><div class="fw-bold"><i class="bi bi-magic me-2 text-danger"></i>Create a campaign</div><div class="small text-muted mt-1">A wide food photo works best (recommended 1400 × 560 px).</div></div>
        <div class="card-body">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="banner-preview mb-3" id="newBannerPreview">
                    <img id="newBannerPreviewImage" alt="" class="d-none">
                    <span class="banner-preview-label">Homepage preview</span>
                    <div><div class="banner-preview-title" id="newBannerPreviewTitle">Your banner headline</div><div class="banner-preview-subtitle" id="newBannerPreviewSubtitle">A short line that makes people hungry.</div></div>
                </div>
                <label class="form-label small fw-semibold" for="bannerTitle">Headline</label>
                <input id="bannerTitle" class="form-control banner-input mb-3" name="title" maxlength="255" value="{{ old('title') }}" placeholder="e.g. Make it a pizza night" required>
                <label class="form-label small fw-semibold" for="bannerSubtitle">Supporting text</label>
                <input id="bannerSubtitle" class="form-control banner-input mb-3" name="subtitle" maxlength="255" value="{{ old('subtitle') }}" placeholder="Keep it short, appetizing and clear">
                <label class="form-label small fw-semibold" for="bannerUrl">Where should this banner take customers? <span class="text-muted fw-normal">(optional)</span></label>
                <input id="bannerUrl" class="form-control banner-input mb-1" type="text" name="url" maxlength="2048" value="{{ old('url') }}" placeholder="/?search=Pizza or https://example.com">
                <div class="small text-muted mb-3">Use a site path to open a search or restaurant page, or paste a full https:// link.</div>
                <label class="form-label small fw-semibold" for="bannerImage">Food image</label>
                <input id="bannerImage" class="form-control banner-input mb-2" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
                <div class="small text-muted mb-3">JPG, PNG or WebP · up to 5 MB</div>
                <div class="row g-3">
                    <div class="col-5"><label class="form-label small fw-semibold" for="bannerSortOrder">Display order</label><input id="bannerSortOrder" class="form-control banner-input" type="number" min="0" max="65535" name="sort_order" value="{{ old('sort_order', 0) }}" required></div>
                    <div class="col-7 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="bannerActive" checked><label class="form-check-label small fw-semibold" for="bannerActive">Publish immediately</label></div></div>
                </div>
                <button class="btn btn-danger w-100 mt-3 py-2"><i class="bi bi-send-fill me-2"></i>Publish to homepage</button>
            </form>
        </div>
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div><h4 class="fw-bold mb-1">Your campaigns</h4><div class="text-muted small">Edit copy, update photos or hide a campaign without deleting it.</div></div>
        </div>
        <div class="d-flex flex-column gap-3">
            @forelse($banners as $banner)
            <article class="banner-list-card">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-list-image">
                <div class="p-3 p-lg-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div><div class="fw-bold">{{ $banner->title }}</div><div class="small text-muted mt-1">{{ $banner->subtitle ?: 'No supporting text' }}</div></div>
                        <span class="badge rounded-pill text-bg-{{ $banner->is_active ? 'success' : 'secondary' }}">{{ $banner->is_active ? 'Live on homepage' : 'Hidden' }}</span>
                    </div>
                    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf @method('PATCH')
                        <div class="col-md-4"><label class="form-label small">Headline</label><input class="form-control form-control-sm" name="title" maxlength="255" value="{{ $banner->title }}" required></div>
                        <div class="col-md-4"><label class="form-label small">Supporting text</label><input class="form-control form-control-sm" name="subtitle" maxlength="255" value="{{ $banner->subtitle }}"></div>
                        <div class="col-md-4"><label class="form-label small">Click destination</label><input class="form-control form-control-sm" type="text" name="url" maxlength="2048" value="{{ $banner->url }}" placeholder="/?search=Pizza or https://example.com"></div>
                        <div class="col-md-5"><label class="form-label small">Replace photo</label><input class="form-control form-control-sm" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
                        <div class="col-md-2"><label class="form-label small">Order</label><input class="form-control form-control-sm" type="number" min="0" max="65535" name="sort_order" value="{{ $banner->sort_order }}" required></div>
                        <div class="col-md-2"><div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active{{ $banner->id }}" {{ $banner->is_active ? 'checked' : '' }}><label class="form-check-label small" for="active{{ $banner->id }}">Visible</label></div></div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-grow-1"><i class="bi bi-check2 me-1"></i>Save</button>
                            <button form="deleteBanner{{ $banner->id }}" class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $banner->title }}" onclick="return confirm('Remove this banner?')"><i class="bi bi-trash"></i></button>
                        </div>
                    </form>
                    <form id="deleteBanner{{ $banner->id }}" action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                </div>
            </article>
            @empty
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="bi bi-images fs-1 d-block mb-2 text-danger"></i><div class="fw-semibold text-dark">Your homepage is ready for its first campaign</div><div class="small mt-1">Use the form to publish a banner with a food photo and destination.</div></div></div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const title = document.getElementById('bannerTitle');
    const subtitle = document.getElementById('bannerSubtitle');
    const imageInput = document.getElementById('bannerImage');
    const previewTitle = document.getElementById('newBannerPreviewTitle');
    const previewSubtitle = document.getElementById('newBannerPreviewSubtitle');
    const previewImage = document.getElementById('newBannerPreviewImage');
    let previewUrl = null;

    title.addEventListener('input', () => {
        previewTitle.textContent = title.value.trim() || 'Your banner headline';
    });
    subtitle.addEventListener('input', () => {
        previewSubtitle.textContent = subtitle.value.trim() || 'A short line that makes people hungry.';
    });
    imageInput.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = imageInput.files[0];
        if (!file) {
            previewImage.removeAttribute('src');
            previewImage.classList.add('d-none');
            return;
        }
        previewUrl = URL.createObjectURL(file);
        previewImage.src = previewUrl;
        previewImage.classList.remove('d-none');
    });
})();
</script>
@endsection
