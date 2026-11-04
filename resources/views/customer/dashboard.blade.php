@extends('customer.layouts.app')
@section('title', 'Order Food Online')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --ink: #241C15;
        --paper: #FBF5EC;
        --brand: #E8342A;
        --brand-dark: #C62828;
        --saffron: #E8A93D;
        --sage: #1FA855;
        --taupe: #8A7A6D;
        --hairline: #EEE3D6;
    }

    body { background: var(--paper) !important; font-family: 'Plus Jakarta Sans', sans-serif; }

    /* ─── Hero ─── */
    .hero {
        background: linear-gradient(120deg, #C62828 0%, #E8342A 45%, #E8A93D 130%);
        padding: 56px 48px;
        border-radius: 28px;
        color: white;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 24px 48px -16px rgba(196, 40, 30, 0.4);
    }
    .hero::before {
        /* signature scattered-spice texture — no clipart, just tone */
        content: '';
        position: absolute; inset: 0;
        background-image:
            radial-gradient(circle, rgba(255,255,255,0.32) 1.5px, transparent 1.5px),
            radial-gradient(circle, rgba(255,255,255,0.18) 1px, transparent 1px);
        background-size: 30px 30px, 16px 16px;
        background-position: 0 0, 15px 11px;
        pointer-events: none;
    }
    .hero::after {
        content: '';
        position: absolute; right: -70px; top: -70px;
        width: 280px; height: 280px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.16), transparent 70%);
        pointer-events: none;
    }
    .hero-eyebrow {
        font-size: 12px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase;
        opacity: 0.85; margin-bottom: 12px; position: relative; z-index: 1;
    }
    .hero h1 {
        font-family: 'Fraunces', serif; font-weight: 600;
        font-size: 46px; line-height: 1.08; letter-spacing: -0.01em;
        margin-bottom: 12px; position: relative; z-index: 1;
    }
    .hero p { font-size: 16px; font-weight: 500; opacity: 0.92; margin-bottom: 30px; position: relative; z-index: 1; }

    .search-box {
        background: white;
        border-radius: 18px;
        padding: 8px 8px 8px 22px;
        display: flex;
        align-items: center;
        max-width: 560px;
        box-shadow: 0 20px 44px rgba(30, 12, 8, 0.24);
        position: relative; z-index: 1;
    }
    .search-box input {
        border: none; outline: none; flex: 1;
        font-size: 15px; background: transparent; color: var(--ink);
        font-family: 'Plus Jakarta Sans';
    }
    .search-box input::placeholder { color: #C4B6A8; }
    .search-box button {
        background: var(--ink); color: white; border: none;
        border-radius: 12px; padding: 12px 26px;
        font-weight: 700; font-size: 14px; cursor: pointer;
        transition: background 0.2s; font-family: 'Plus Jakarta Sans';
    }
    .search-box button:hover { background: var(--brand); }

    /* ─── Category rail (circular icon chips) ─── */
    .category-rail {
        display: flex; gap: 22px; overflow-x: auto;
        padding: 6px 2px 16px; margin-bottom: 4px;
        scrollbar-width: thin;
    }
    .cat-chip {
        display: flex; flex-direction: column; align-items: center; gap: 9px;
        text-decoration: none; flex-shrink: 0; width: 74px;
    }
    .cat-chip-icon {
        width: 64px; height: 64px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 27px; background: white;
        border: 2px solid var(--hairline);
        transition: all 0.22s ease;
        box-shadow: 0 2px 8px rgba(36,28,21,0.05);
        overflow: hidden;
    }
    .cat-chip-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .cat-chip span {
        font-size: 12px; font-weight: 600; color: #5C5044; text-align: center;
    }
    .cat-chip:hover .cat-chip-icon, .cat-chip.active .cat-chip-icon {
        border-color: var(--brand); background: #FFF1EE; transform: translateY(-3px);
    }
    .cat-chip.active span { color: var(--brand); font-weight: 700; }

    /* ─── Filter bar ─── */
    .filter-bar { display: flex; gap: 10px; align-items: center; margin-bottom: 30px; flex-wrap: wrap; }
    .filter-label { font-size: 13px; font-weight: 700; color: var(--taupe); }
    .filter-chip {
        background: white; border: 1.5px solid var(--hairline);
        border-radius: 50px; padding: 8px 16px;
        font-size: 13px; font-weight: 600; color: #5C5044;
        cursor: pointer; text-decoration: none;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.2s;
    }
    .filter-chip:hover { border-color: var(--brand); color: var(--brand); }
    .filter-chip.active { background: var(--ink); border-color: var(--ink); color: white; }
    .filter-chip.veg-active { background: var(--sage); border-color: var(--sage); color: white; }
    .filter-bar select.form-select {
        border-radius: 50px; border: 1.5px solid var(--hairline);
        font-size: 13px; font-weight: 600; color: #5C5044; padding: 8px 16px;
    }

    .section-title {
        font-family: 'Fraunces', serif; font-size: 25px; font-weight: 600; color: var(--ink);
    }
    .section-title span { font-family: 'Plus Jakarta Sans'; font-size: 14px; font-weight: 500; color: #C4B6A8; margin-left: 8px; }

    /* ─── Restaurant card ─── */
    .restaurant-card {
        border: none; border-radius: 20px; overflow: hidden;
        box-shadow: 0 2px 16px rgba(36,28,21,0.07);
        transition: transform 0.3s cubic-bezier(.2,.8,.2,1), box-shadow 0.3s;
        text-decoration: none; color: inherit; display: block;
        background: white; position: relative;
    }
    .restaurant-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 22px 40px rgba(36,28,21,0.16);
        color: inherit;
    }
    .restaurant-img-wrap { position: relative; height: 190px; overflow: hidden; }
    .restaurant-img { height: 100%; width: 100%; object-fit: cover; transition: transform 0.45s ease; display: block; }
    .restaurant-card:hover .restaurant-img { transform: scale(1.07); }
    .restaurant-img-placeholder {
        height: 190px;
        background: linear-gradient(135deg, #FFF1EE, #FDE3DC);
        display: flex; align-items: center; justify-content: center; font-size: 52px;
    }
    .rating-float {
        position: absolute; left: 12px; top: 12px; z-index: 2;
        background: rgba(31,168,85,0.95); color: white; font-size: 12px; font-weight: 800;
        padding: 5px 10px; border-radius: 8px;
        display: flex; align-items: center; gap: 4px;
        box-shadow: 0 4px 10px rgba(31,168,85,0.3);
    }
    .fav-heart-btn {
        position: absolute; top: 12px; right: 12px; z-index: 3;
        background: rgba(255,255,255,0.92);
        border: none; border-radius: 50%;
        width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: #CBBFB2; font-size: 15px;
        transition: transform 0.2s;
    }
    .fav-heart-btn:hover { transform: scale(1.1); }
    .fav-heart-btn.active { color: var(--brand); }
    .closed-overlay {
        position: absolute; inset: 0; z-index: 1;
        background: rgba(36,28,21,0.55);
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 13px; letter-spacing: 0.6px;
    }

    .restaurant-body { padding: 20px 16px 18px; }
    .restaurant-name {
        font-family: 'Fraunces', serif; font-size: 18px; font-weight: 600;
        color: var(--ink); margin-bottom: 5px;
    }
    .restaurant-meta { font-size: 12px; color: #A9998A; margin-bottom: 11px; font-weight: 500; }
    .cuisine-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 13px; }
    .cuisine-tag {
        font-size: 10.5px; font-weight: 700; color: #B5722C;
        background: #FBEEDD; padding: 3.5px 10px; border-radius: 20px;
    }
    .status-row { display: flex; justify-content: space-between; align-items: center; }
    .open-dot, .closed-dot { font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px; }
    .open-dot { color: var(--sage); }
    .closed-dot { color: #C4382F; }
    .open-dot::before, .closed-dot::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
</style>
@endsection

@section('content')
<!-- Hero -->
<div class="hero">
    <div class="hero-eyebrow">Order Food Online</div>
    <h1>Hungry?</h1>
    <p>Order food from the best restaurants near you</p>
    <form action="{{ route('customer.dashboard') }}" method="GET" autocomplete="off">
        <input type="hidden" name="category" value="{{ request('category') }}">
        <input type="hidden" name="filter" value="{{ request('filter') }}">
        <div class="search-box position-relative">
            <i class="bi bi-search text-muted me-2" style="font-size:16px"></i>
            <input type="text" name="search" id="searchInput"
                value="{{ request('search') }}"
                placeholder="Search restaurants or dishes...">
            <button type="submit">Search</button>
            <div id="searchSuggestions" class="text-start"
                style="display:none;position:absolute;top:56px;left:0;right:0;background:white;
                       border-radius:14px;box-shadow:0 16px 36px rgba(36,28,21,0.2);z-index:50;
                       max-height:360px;overflow-y:auto;padding:8px;"></div>
        </div>
    </form>
</div>

@if($reorderSuggestions->isNotEmpty())
<div class="mt-4 mb-1">
    <h6 class="fw-700 mb-3" style="color:var(--ink);"><i class="bi bi-arrow-repeat me-2 text-danger"></i>Order it again</h6>
    <div class="d-flex gap-3" style="overflow-x:auto;padding-bottom:6px;">
        @foreach($reorderSuggestions as $order)
        <div class="flex-shrink-0" style="width:230px;">
            <div class="d-flex align-items-center gap-2 p-2" style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(36,28,21,0.07);">
                @if($order->restaurant->image)
                    <img src="{{ $order->restaurant->image_url }}" alt="{{ $order->restaurant->name }}"
                        style="width:48px;height:48px;object-fit:cover;border-radius:10px;flex-shrink:0;">
                @else
                    <div style="width:48px;height:48px;border-radius:10px;background:#fff0f0;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🍽️</div>
                @endif
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-700 text-truncate" style="font-size:13px;color:var(--ink);">{{ $order->restaurant->name }}</div>
                    <div class="text-muted text-truncate" style="font-size:11px;">{{ $order->created_at->diffForHumans() }}</div>
                </div>
                <form action="{{ route('customer.orders.reorder', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger rounded-circle" style="width:32px;height:32px;flex-shrink:0;" title="Reorder">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Category Rail -->
@if($categories->count())
<div class="category-rail">
    <a href="{{ route('customer.dashboard', array_merge(request()->except('category'), [])) }}"
        class="cat-chip {{ !request('category') ? 'active' : '' }}">
        <span class="cat-chip-icon">🍽️</span>
        <span>All</span>
    </a>
    @foreach($categories as $cat)
    <a href="{{ route('customer.dashboard', array_merge(request()->except('category'), ['category' => $cat->id])) }}"
        class="cat-chip {{ request('category') == $cat->id ? 'active' : '' }}">
        <span class="cat-chip-icon">
            @if($cat->icon && str_starts_with($cat->icon, 'http'))
                <img src="{{ $cat->icon }}" alt="{{ $cat->name }}">
            @else
                {{ $cat->icon ?? '🍴' }}
            @endif
        </span>
        <span>{{ $cat->name }}</span>
    </a>
    @endforeach
</div>
@endif

<!-- Filter Bar -->
<div class="filter-bar">
    <span class="filter-label">Filters</span>
    <a href="{{ route('customer.dashboard', array_merge(request()->except('filter'), [])) }}"
        class="filter-chip {{ !request('filter') ? 'active' : '' }}">
        All
    </a>
    <a href="{{ route('customer.dashboard', array_merge(request()->all(), ['filter' => 'veg'])) }}"
        class="filter-chip {{ request('filter') == 'veg' ? 'veg-active' : '' }}">
        <i class="bi bi-circle-fill" style="font-size:7px"></i> Pure Veg
    </a>
    <a href="{{ route('customer.dashboard', array_merge(request()->all(), ['filter' => 'top_rated'])) }}"
        class="filter-chip {{ request('filter') == 'top_rated' ? 'active' : '' }}">
        <i class="bi bi-star-fill" style="font-size:11px"></i> Top Rated
    </a>
    <a href="{{ route('customer.dashboard', array_merge(request()->all(), ['filter' => 'open'])) }}"
        class="filter-chip {{ request('filter') == 'open' ? 'active' : '' }}">
        <i class="bi bi-circle-fill" style="font-size:7px;color:#1FA855"></i> Open Now
    </a>
    <a href="{{ route('customer.dashboard', array_merge(request()->except('min_rating'), ['min_rating' => '4.0'])) }}"
        class="filter-chip {{ request('min_rating') == '4.0' ? 'active' : '' }}">
        <i class="bi bi-star-fill" style="font-size:11px"></i> 4.0+
    </a>
    <a href="{{ route('customer.dashboard', array_merge(request()->except('min_rating'), ['min_rating' => '4.5'])) }}"
        class="filter-chip {{ request('min_rating') == '4.5' ? 'active' : '' }}">
        <i class="bi bi-star-fill" style="font-size:11px"></i> 4.5+
    </a>

    @if($cuisines->isNotEmpty())
    <form action="{{ route('customer.dashboard') }}" method="GET" class="d-inline-block ms-2">
        @foreach(request()->except('cuisine') as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <select name="cuisine" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
            <option value="">All Cuisines</option>
            @foreach($cuisines as $c)
                <option value="{{ $c }}" {{ request('cuisine') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </form>
    @endif

    <form action="{{ route('customer.dashboard') }}" method="GET" class="d-inline-block ms-2">
        @foreach(request()->except('sort') as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <select name="sort" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
            <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Sort: Relevance</option>
            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Sort: Rating (High to Low)</option>
            <option value="cost_low" {{ $sort === 'cost_low' ? 'selected' : '' }}>Sort: Cost (Low to High)</option>
            <option value="cost_high" {{ $sort === 'cost_high' ? 'selected' : '' }}>Sort: Cost (High to Low)</option>
        </select>
    </form>
</div>

<!-- Restaurants -->
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div class="section-title mb-0">
        {{ request('search') ? 'Results for "'.request('search').'"' : 'All Restaurants' }}
        <span>({{ $restaurants->total() }})</span>
    </div>
    @if(request()->hasAny(['search','category','filter']))
        <a href="{{ route('customer.dashboard') }}"
            class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-x me-1"></i>Clear All
        </a>
    @endif
</div>

@if($pincode && !$showAll)
<div class="mb-4" style="font-size:13px;color:var(--taupe);">
    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
    Showing restaurants delivering to
    <strong style="color:var(--ink)">{{ session('customer_full_address') ? \Illuminate\Support\Str::limit(session('customer_full_address'), 55) : $pincode }}</strong>
    @if(session('customer_full_address')) <span style="color:var(--taupe)">({{ $pincode }})</span> @endif
    · <button type="button" class="btn btn-link btn-sm p-0" style="font-size:13px;" data-bs-toggle="modal" data-bs-target="#locationModal">Change</button>
</div>
@elseif($pincode && $showAll)
<div class="mb-4" style="font-size:13px;color:var(--taupe);">
    <i class="bi bi-geo-alt me-1"></i>
    Browsing restaurants from <strong style="color:var(--ink)">all locations</strong>
    (your delivery location is still <strong style="color:var(--ink)">{{ $pincode }}</strong> — you'll only be able to
    order from restaurants that deliver there)
    · <a href="{{ route('customer.dashboard') }}" class="btn btn-link btn-sm p-0" style="font-size:13px;">Back to {{ $pincode }}</a>
</div>
@else
<div class="mb-4" style="font-size:13px;color:var(--taupe);">
    <i class="bi bi-geo-alt me-1"></i>
    Showing restaurants from all locations ·
    <button type="button" class="btn btn-link btn-sm p-0" style="font-size:13px;" data-bs-toggle="modal" data-bs-target="#locationModal">Set your location</button>
</div>
@endif

@php $favIds = auth()->check() ? auth()->user()->favoriteRestaurants()->pluck('restaurants.id')->all() : []; @endphp
<div class="row g-4">
    @forelse($restaurants as $restaurant)
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div style="position:relative">
        <form action="{{ route('customer.favorites.toggle', $restaurant) }}" method="POST" style="position:absolute;top:0;right:0;margin:0;z-index:3;">
            @csrf
            <button type="submit" class="fav-heart-btn {{ in_array($restaurant->id, $favIds) ? 'active' : '' }}" title="Toggle favorite">
                <i class="bi {{ in_array($restaurant->id, $favIds) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
            </button>
        </form>
        <a href="{{ route('customer.restaurant.show', $restaurant) }}"
            class="restaurant-card">
            <div class="restaurant-img-wrap">
                @if($restaurant->image)
                    <img src="{{ $restaurant->image_url }}"
                        class="restaurant-img" alt="{{ $restaurant->name }}">
                @else
                    <div class="restaurant-img-placeholder">🍽️</div>
                @endif
                @if(!$restaurant->isCurrentlyOpen())
                    <div class="closed-overlay">Currently Closed</div>
                @endif
                @php $rating = round($restaurant->avg_rating, 1); @endphp
                @if($rating > 0)
                <div class="rating-float">
                    <i class="bi bi-star-fill" style="font-size:10px"></i> {{ $rating }}
                </div>
                @endif
            </div>
            <div class="restaurant-body">
                <div class="restaurant-name">{{ $restaurant->name }}</div>
                <div class="restaurant-meta">
                    <i class="bi bi-geo-alt me-1"></i>{{ $restaurant->pincode }}
                    · {{ $restaurant->food_items_count }} items
                </div>
                @if($restaurant->cuisines_list)
                <div class="cuisine-tags">
                    @foreach(array_slice($restaurant->cuisines_list, 0, 3) as $tag)
                        <span class="cuisine-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
                <div class="status-row">
                    @if($restaurant->isCurrentlyOpen())
                        <span class="open-dot">Open now</span>
                    @else
                        <span class="closed-dot">Closed</span>
                    @endif
                </div>
            </div>
        </a>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="background:white;border-radius:24px;box-shadow:0 2px 16px rgba(36,28,21,0.07)">
            <div style="font-size:60px">🍽️</div>
            <h5 class="mt-3" style="font-family:'Fraunces',serif;font-weight:600;color:#241C15">No restaurants found</h5>
            @if($pincode && !$showAll)
                <p style="color:#A9998A">No restaurants deliver to <strong>{{ $pincode }}</strong> yet — try a different search/filter, or browse all restaurants below.</p>
                <a href="{{ route('customer.dashboard', ['show_all' => 1]) }}"
                    class="btn rounded-pill px-4" style="background:#E8342A;border:none;color:white;font-weight:600">View All Restaurants</a>
            @else
                <p style="color:#A9998A">Try a different search or filter</p>
                <a href="{{ route('customer.dashboard', ['show_all' => 1]) }}"
                    class="btn rounded-pill px-4" style="background:#E8342A;border:none;color:white;font-weight:600">View All</a>
            @endif
        </div>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $restaurants->links() }}</div>
@endsection

@section('scripts')
<script>
(function() {
    const input = document.getElementById('searchInput');
    const box = document.getElementById('searchSuggestions');
    let debounceTimer;

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const term = this.value.trim();

        if (term.length < 2) {
            box.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('customer.search.suggestions') }}?q=${encodeURIComponent(term)}`)
                .then(res => res.json())
                .then(data => renderSuggestions(data, term))
                .catch(() => { box.style.display = 'none'; });
        }, 250);
    });

    function renderSuggestions(data, term) {
        const hasResults = data.restaurants.length || data.dishes.length;
        if (!hasResults) {
            box.innerHTML = `<div class="text-muted p-2" style="font-size:13px">No matches for "${escapeHtml(term)}"</div>`;
            box.style.display = 'block';
            return;
        }

        let html = '';

        if (data.restaurants.length) {
            html += `<div class="text-muted px-2 pt-1 pb-1" style="font-size:11px;font-weight:600">RESTAURANTS</div>`;
            data.restaurants.forEach(r => {
                html += `<a href="${r.url}" class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none suggestion-item">
                    <span style="font-size:18px">🍽️</span>
                    <div>
                        <div style="font-size:14px;color:#241C15;font-weight:600">${escapeHtml(r.name)}</div>
                        <div style="font-size:11px;color:#A9998A">${escapeHtml(r.pincode ?? '')}</div>
                    </div>
                </a>`;
            });
        }

        if (data.dishes.length) {
            html += `<div class="text-muted px-2 pt-2 pb-1" style="font-size:11px;font-weight:600">DISHES</div>`;
            data.dishes.forEach(d => {
                html += `<a href="${d.url}" class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none suggestion-item">
                    <span style="font-size:18px">🍲</span>
                    <div>
                        <div style="font-size:14px;color:#241C15;font-weight:600">${escapeHtml(d.name)}</div>
                        <div style="font-size:11px;color:#A9998A">${escapeHtml(d.restaurant)} · ₹${Number(d.price).toFixed(2)}</div>
                    </div>
                </a>`;
            });
        }

        box.innerHTML = html;
        box.style.display = 'block';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    document.addEventListener('click', function(e) {
        if (!box.contains(e.target) && e.target !== input) {
            box.style.display = 'none';
        }
    });
})();
</script>
<style>
    .suggestion-item:hover { background: #FFF1EE; }
</style>
@endsection
