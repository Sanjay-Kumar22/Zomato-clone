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

    /* ─── Discovery hero ─── */
    .hero {
        background:
            radial-gradient(ellipse at 82% 18%, rgba(244, 154, 87, .22), transparent 34%),
            radial-gradient(ellipse at 12% 100%, rgba(131, 43, 35, .48), transparent 48%),
            #241715;
        padding: 26px 42px;
        border-radius: 30px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 28px 60px -28px rgba(76, 26, 22, .72);
        isolation: isolate;
        min-height: 310px;
        display: flex;
        align-items: center;
    }
    .hero::before {
        content: '';
        position: absolute; inset: 0; opacity: .46;
        background-image:
            radial-gradient(circle, rgba(255,255,255,.17) 1px, transparent 1.2px),
            linear-gradient(115deg, transparent 48%, rgba(255,255,255,.035) 48.2%, transparent 48.5%);
        background-size: 28px 28px, 100% 100%;
        pointer-events: none;
        z-index: -1;
    }
    .hero::after {
        content: '';
        position: absolute; right: 30%; bottom: -240px;
        width: 420px; height: 420px; border-radius: 50%;
        border: 1px solid rgba(255,255,255,.09);
        box-shadow: 0 0 0 35px rgba(255,255,255,.025), 0 0 0 70px rgba(255,255,255,.02);
        pointer-events: none;
        z-index: -1;
    }
    .hero-content { position: relative; z-index: 2; width: min(62%, 610px); }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 10px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase;
        color: #ffe2b7; background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.15); border-radius: 30px;
        padding: 6px 10px; margin-bottom: 10px;
    }
    .hero-eyebrow i { color: #ffb768; }
    .hero h1 {
        font-family: 'Fraunces', serif; font-weight: 600;
        font-size: clamp(42px, 3.8vw, 52px); line-height: .92; letter-spacing: -.055em;
        margin-bottom: 8px; max-width: 600px;
    }
    .hero h1 span { color: #ffc078; font-style: italic; }
    .hero p { font-size: 13px; font-weight: 500; line-height: 1.5; color: rgba(255,255,255,.72); max-width: 460px; margin-bottom: 12px; }

    .search-box {
        background: #fffdf9;
        border-radius: 17px;
        padding: 7px 7px 7px 17px;
        display: flex;
        align-items: center;
        width: min(100%, 510px);
        min-height: 52px;
        box-shadow: 0 18px 38px rgba(0,0,0,.24), 0 0 0 1px rgba(255,255,255,.45);
        position: relative;
        z-index: 5;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .search-box:focus-within { box-shadow: 0 20px 42px rgba(0,0,0,.28), 0 0 0 4px rgba(255,192,120,.2); transform: translateY(-2px); }
    .search-box > .bi-search { color: #9c897b !important; flex: 0 0 auto; font-size: 16px; }
    .search-box input {
        border: none; outline: none; flex: 1;
        min-width: 0; font-size: 13px; background: transparent; color: var(--ink); padding: 9px 3px;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .search-box input::placeholder { color: #C4B6A8; }
    .search-box button {
        background: linear-gradient(135deg, #ef493a, #cf2929); color: white; border: none;
        border-radius: 12px; padding: 12px 19px;
        font-weight: 800; font-size: 12px; cursor: pointer; white-space: nowrap;
        transition: transform .2s, box-shadow .2s, filter .2s; font-family: 'Plus Jakarta Sans', sans-serif;
        box-shadow: 0 7px 16px rgba(216, 48, 43, .28);
    }
    .search-box button:hover { filter: brightness(1.05); transform: translateY(-1px); box-shadow: 0 7px 16px rgba(216,48,43,.3); }
    .popular-searches { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-top:8px; }
    .popular-searches-label { font-size:10px; font-weight:700; color:rgba(255,255,255,.58); margin-right:2px; text-transform:uppercase; letter-spacing:.08em; }
    .popular-search {
        color:#fff8f1; text-decoration:none; font-size:10px; font-weight:700;
        padding:5px 9px; border:1px solid rgba(255,255,255,.18); border-radius:30px;
        background:rgba(255,255,255,.07); transition:background .18s, transform .18s;
    }
    .popular-search:hover { color:white; background:rgba(255,255,255,.2); transform:translateY(-1px); }
    .hero-art {
        position:absolute; z-index:1; right:5%; top:50%; transform:translateY(-50%);
        width:38%; max-width:410px; height:285px; pointer-events:none;
    }
    .hero-art-glow { position:absolute; inset:12% 8%; border-radius:50%; background:rgba(236,126,69,.38); filter:blur(48px); }
    .hero-food-card {
        position:absolute; overflow:hidden; border:5px solid rgba(255,255,255,.94);
        box-shadow:0 26px 56px rgba(0,0,0,.36); background:#fff; border-radius:30px;
    }
    .hero-food-card img { width:100%; height:100%; object-fit:cover; display:block; }
    .hero-food-card--main { width:68%; height:88%; left:18%; top:4%; transform:rotate(3deg); }
    .hero-food-card--left { width:31%; height:36%; left:0; top:18%; transform:rotate(-11deg); }
    .hero-food-card--right { width:34%; height:38%; right:0; bottom:7%; transform:rotate(10deg); }
    .hero-art-tag {
        position:absolute; z-index:2; right:1%; top:2%;
        display:flex; align-items:center; gap:8px; padding:11px 14px;
        color:#33251f; background:rgba(255,255,255,.97); border-radius:15px;
        box-shadow:0 12px 28px rgba(0,0,0,.2); font-size:10px; font-weight:800;
    }
    .hero-art-tag i { color:#e33c32; font-size:14px; }
    .search-suggestions {
        display:none; position:absolute; top:calc(100% + 10px); left:0; right:0;
        background:white; border-radius:14px; box-shadow:0 16px 36px rgba(36,28,21,.2);
        z-index:50; max-height:360px; overflow-y:auto; padding:8px;
    }
    @media (max-width: 991px) {
        .hero { padding:24px 30px; min-height:290px; }
        .hero-content { width:63%; }
        .hero-art { right:1%; width:34%; height:250px; opacity:.9; }
    }
    @media (max-width: 767px) {
        .hero { padding:22px 20px 19px; min-height:0; border-radius:22px; margin-bottom:22px; }
        .hero-content { width:100%; }
        .hero h1 { font-size:clamp(36px, 9vw, 44px); max-width:430px; }
        .hero p { font-size:12px; max-width:300px; margin-bottom:12px; }
        .hero-art { display:none; }
        .search-box { width:100%; min-height:48px; }
        .search-box button { padding:9px 13px; }
        .popular-searches { gap:6px; margin-top:8px; }
    }

    /* ─── Category rail (circular icon chips) ─── */
    .category-rail {
        display: flex; gap: 15px; overflow-x: auto;
        padding: 12px 3px 20px; margin-bottom: 22px;
        scrollbar-width: none;
    }
    .category-rail::-webkit-scrollbar { display:none; }
    .cat-chip {
        position:relative; display:flex; flex-direction:column; align-items:stretch;
        text-decoration:none; flex-shrink:0; width:126px; height:108px;
    }
    .cat-chip-icon {
        position:absolute; inset:0; width:100%; height:100%; border-radius:18px;
        display:flex; align-items:center; justify-content:center;
        font-size:33px; color:white; background:#fffdf9;
        border:1px solid rgba(36,28,21,.08);
        transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        box-shadow:0 6px 16px rgba(36,28,21,.07); overflow:hidden;
    }
    .cat-chip-icon::after {
        content:''; position:absolute; inset:35% 0 0;
        background:linear-gradient(180deg,transparent,rgba(24,18,14,.76));
        pointer-events:none;
    }
    .cat-chip-icon img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .35s ease; }
    .cat-chip > span:last-child {
        position:absolute; left:12px; right:8px; bottom:11px; z-index:1;
        font-size:12px; line-height:1.15; font-weight:800; color:white; text-align:left;
        text-shadow:0 1px 5px rgba(0,0,0,.35);
    }
    .cat-chip:first-child .cat-chip-icon {
        background:linear-gradient(145deg,#e94434,#aa2926 62%,#773126);
    }
    .cat-chip:first-child .cat-chip-icon::after { display:none; }
    .cat-chip:hover .cat-chip-icon, .cat-chip.active .cat-chip-icon {
        border-color:rgba(232,52,42,.7); transform:translateY(-3px);
        box-shadow:0 12px 24px rgba(36,28,21,.16);
    }
    .cat-chip:hover .cat-chip-icon img { transform:scale(1.07); }
    .cat-chip:first-child:hover .cat-chip-icon,
    .cat-chip:first-child.active .cat-chip-icon { background:linear-gradient(145deg,#f05242,#b82f2c 62%,#85352e); }
    .cat-chip:focus-visible { outline:3px solid rgba(232,52,42,.38); outline-offset:4px; border-radius:20px; }

    /* ─── Filter bar ─── */
    .filter-bar {
        display:flex; gap:9px; align-items:center; margin-bottom:30px; flex-wrap:wrap;
        padding:11px 13px; border:1px solid rgba(36,28,21,.07); border-radius:18px;
        background:rgba(255,255,255,.72); box-shadow:0 6px 18px rgba(36,28,21,.035);
    }
    .filter-label { font-size:11px; font-weight:800; color:var(--taupe); text-transform:uppercase; letter-spacing:.08em; margin:0 3px 0 1px; }
    .filter-chip {
        background:white; border:1px solid var(--hairline);
        border-radius:50px; padding:8px 13px;
        font-size:11px; font-weight:700; color:#5C5044;
        cursor: pointer; text-decoration: none;
        display: flex; align-items: center; gap: 6px;
        white-space:nowrap; transition:all .2s ease;
    }
    .filter-chip:hover { border-color:var(--brand); color:var(--brand); transform:translateY(-1px); }
    .filter-chip.active { background:var(--ink); border-color:var(--ink); color:white; }
    .filter-chip.veg-active { background:var(--sage); border-color:var(--sage); color:white; }
    .filter-bar select.form-select {
        border-radius:50px; border:1px solid var(--hairline);
        font-size:11px; font-weight:700; color:#5C5044; padding:8px 32px 8px 13px;
        background-color:white; min-height:35px;
    }

    .section-title {
        font-family: 'Fraunces', serif; font-size: 29px; font-weight: 600; color: var(--ink); letter-spacing:-.03em;
    }
    .section-title span { font-family: 'Plus Jakarta Sans'; font-size: 14px; font-weight: 500; color: #C4B6A8; margin-left: 8px; }

    /* ─── Restaurant card ─── */
    .restaurant-card {
        border: 1px solid rgba(36,28,21,.055); border-radius: 22px; overflow: hidden;
        box-shadow: 0 8px 24px rgba(36,28,21,0.065);
        transition: transform 0.3s cubic-bezier(.2,.8,.2,1), box-shadow 0.3s;
        text-decoration: none; color: inherit; display: block;
        background: white; position: relative;
    }
    .restaurant-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 38px rgba(36,28,21,0.14);
        color: inherit;
    }
    .restaurant-img-wrap { position:relative; height:220px; overflow:hidden; background:#eadfd3; }
    .restaurant-img { height:100%; width:100%; object-fit:cover; object-position:center 48%; transition:transform .55s cubic-bezier(.2,.8,.2,1), filter .3s ease; display:block; filter:saturate(1.08) contrast(1.02); }
    .restaurant-img-wrap::after {
        content:''; position:absolute; inset:45% 0 0;
        background:linear-gradient(180deg,transparent,rgba(23,16,13,.42)); pointer-events:none;
    }
    .restaurant-card:hover .restaurant-img { transform: scale(1.07); }
    .restaurant-card:focus-visible { outline:3px solid rgba(232,52,42,.52); outline-offset:4px; }
    .restaurant-img-placeholder {
        height: 190px;
        background: linear-gradient(135deg, #FFF1EE, #FDE3DC);
        display: flex; align-items: center; justify-content: center; font-size: 52px;
    }
    .rating-float {
        position: absolute; left: 12px; top: 12px; z-index: 2;
        background: rgba(255,255,255,.96); color: #1d7b48; font-size: 11px; font-weight: 800;
        padding: 7px 9px; border-radius: 9px;
        display: flex; align-items: center; gap: 4px;
        box-shadow: 0 5px 14px rgba(0,0,0,.12);
    }
    .restaurant-img-wrap > .badge { z-index:2; }
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

    .restaurant-body { padding:18px 17px 17px; }
    .restaurant-name {
        font-family: 'Fraunces', serif; font-size: 19px; font-weight: 600;
        color: var(--ink); margin-bottom: 6px; letter-spacing:-.02em;
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
    .campaign-heading { display:flex;align-items:end;justify-content:space-between;gap:12px;margin:0 2px 10px; }
    .campaign-heading h2 { margin:0;color:var(--ink);font-family:'Fraunces',serif;font-size:23px;font-weight:600;letter-spacing:-.03em; }
    .campaign-heading p { margin:3px 0 0;color:#9a897b;font-size:11px; }
    .campaign-track { display:flex;gap:14px;overflow-x:auto;padding:2px 2px 18px;scroll-snap-type:x mandatory;scrollbar-width:thin;scrollbar-color:#d8c8b7 transparent; }
    .campaign-card { position:relative;display:flex;align-items:flex-end;isolation:isolate;flex:0 0 min(48%, 510px);min-width:330px;height:176px;overflow:hidden;padding:22px;border:1px solid rgba(36,28,21,.08);border-radius:22px;background:#291c17;color:#fff;text-decoration:none;box-shadow:0 10px 24px rgba(36,28,21,.1);scroll-snap-align:start;transition:transform .22s ease,box-shadow .22s ease; }
    .campaign-card::before { position:absolute;inset:0;z-index:-1;background:linear-gradient(90deg,rgba(24,16,13,.9) 0%,rgba(24,16,13,.65) 43%,rgba(24,16,13,.04) 100%),linear-gradient(0deg,rgba(24,16,13,.2),transparent 58%);content:""; }
    .campaign-card:hover { color:#fff;transform:translateY(-3px);box-shadow:0 16px 30px rgba(36,28,21,.17); }
    .campaign-card:focus-visible { outline:3px solid rgba(232,52,42,.55);outline-offset:3px; }
    .campaign-card img { position:absolute;inset:0;z-index:-2;width:100%;height:100%;object-fit:cover;transition:transform .45s ease; }
    .campaign-card:hover img { transform:scale(1.045); }
    .campaign-copy { max-width:76%; }
    .campaign-kicker { display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;color:#ffcf9f;font-size:9px;font-weight:800;letter-spacing:.14em;text-transform:uppercase; }
    .campaign-title { margin:0;color:#fff;font-family:'Fraunces',serif;font-size:25px;font-weight:600;line-height:1.04;letter-spacing:-.025em; }
    .campaign-subtitle { margin:6px 0 0;color:rgba(255,255,255,.82);font-size:11px;line-height:1.4; }
    .campaign-cta { position:absolute;right:15px;bottom:15px;display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(255,255,255,.38);border-radius:999px;background:rgba(28,20,16,.38);color:#fff;font-size:10px;font-weight:800;backdrop-filter:blur(8px); }
    @media (max-width: 767px) {
        .category-rail { gap:11px; margin-bottom:18px; }
        .cat-chip { width:112px; height:96px; }
        .cat-chip > span:last-child { left:10px; bottom:9px; font-size:11px; }
        .filter-bar { flex-wrap:nowrap; overflow-x:auto; margin:0 -4px 24px; padding:10px; scrollbar-width:none; }
        .filter-bar::-webkit-scrollbar { display:none; }
        .filter-bar > *, .filter-bar form { flex:0 0 auto; }
        .restaurant-img-wrap { height:190px; }
        .campaign-heading h2 { font-size:21px; }
        .campaign-track { gap:10px;margin-right:-10px;padding-right:10px; }
        .campaign-card { flex-basis:86%;min-width:0;height:158px;padding:17px;border-radius:18px; }
        .campaign-title { font-size:23px; }
        .campaign-copy { max-width:82%; }
    }
</style>
@endsection

@section('content')
@php
    $heroRestaurants = $restaurants->getCollection()
        ->filter(fn ($restaurant) => $restaurant->image)
        ->take(3)
        ->values();
@endphp
<div class="hero">
    <div class="hero-content">
        <div class="hero-eyebrow"><i class="bi bi-stars"></i>Good food, right around you</div>
        <h1>Make today<br><span>a little tastier.</span></h1>
        <p>Find standout restaurants, hidden gems, and dishes worth repeating.</p>
        <form action="{{ route('customer.dashboard') }}" method="GET" autocomplete="off">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="filter" value="{{ request('filter') }}">
            <div class="search-box">
                <i class="bi bi-search me-2" aria-hidden="true"></i>
                <input type="search" name="search" id="searchInput"
                    value="{{ request('search') }}"
                    placeholder="Search dishes, restaurants..."
                    aria-label="Search restaurants and dishes">
                <button type="submit"><i class="bi bi-arrow-right me-1"></i>Explore</button>
                <div id="searchSuggestions" class="search-suggestions text-start"></div>
            </div>
        </form>
        <div class="popular-searches">
            <span class="popular-searches-label">Trending</span>
            @foreach(['Biryani', 'Pizza', 'Burger'] as $popularSearch)
                <a class="popular-search" href="{{ route('customer.dashboard', ['search' => $popularSearch]) }}">{{ $popularSearch }}</a>
            @endforeach
        </div>
    </div>
    @if($heroRestaurants->isNotEmpty())
    <div class="hero-art" aria-hidden="true">
        <div class="hero-art-glow"></div>
        @foreach($heroRestaurants as $index => $heroRestaurant)
            <div class="hero-food-card hero-food-card--{{ ['main', 'left', 'right'][$index] }}">
                <img src="{{ $heroRestaurant->image_url }}" alt="" loading="lazy">
            </div>
        @endforeach
        <div class="hero-art-tag"><i class="bi bi-stars"></i><span>Local favourites, made fresh</span></div>
    </div>
    @endif
</div>

@if($banners->isNotEmpty())
<section class="campaign-section mb-4" aria-label="Featured food campaigns">
    <div class="campaign-heading">
        <div><h2>Something tasty is calling</h2><p>Hand-picked ideas for your next great meal.</p></div>
        <span class="small fw-bold text-muted">{{ $banners->count() }} picks</span>
    </div>
    <div class="campaign-track" role="list">
    @foreach($banners as $banner)
        <a href="{{ $banner->url ?: route('customer.dashboard') }}" class="campaign-card" role="listitem" aria-label="{{ $banner->title }}{{ $banner->subtitle ? ': ' . $banner->subtitle : '' }}">
            <img src="{{ $banner->image_url }}" alt="" loading="lazy">
            <div class="campaign-copy">
                <div class="campaign-kicker"><i class="bi bi-stars"></i> Picked for you</div>
                <h3 class="campaign-title">{{ $banner->title }}</h3>
                @if($banner->subtitle)<p class="campaign-subtitle">{{ $banner->subtitle }}</p>@endif
            </div>
            <span class="campaign-cta" aria-hidden="true">Explore dishes <i class="bi bi-arrow-up-right"></i></span>
        </a>
    @endforeach
    </div>
</section>
@endif

@if($recentSearches->isNotEmpty() && !request()->filled('search'))
<div class="d-flex align-items-center flex-wrap gap-2 mb-4">
    <span class="small fw-bold text-muted"><i class="bi bi-clock-history me-1"></i>Recent searches</span>
    @foreach($recentSearches as $recent)
    <a class="btn btn-sm btn-light rounded-pill" href="{{ route('customer.dashboard', ['search' => $recent->term]) }}">{{ $recent->term }}</a>
    @endforeach
</div>
@endif

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
<div class="d-flex align-items-end justify-content-between mb-1">
    <div>
        <div class="text-uppercase fw-bold mb-1" style="font-size:10px;letter-spacing:.13em;color:#a38b77;">Pick your craving</div>
        <h2 class="section-title mb-0">Explore by mood</h2>
    </div>
    <span class="small text-muted d-none d-sm-inline mb-1">Fresh ideas for your next bite</span>
</div>
<div class="category-rail">
    <a href="{{ route('customer.dashboard', array_merge(request()->except('category'), [])) }}"
        class="cat-chip {{ !request('category') ? 'active' : '' }}" aria-label="Browse all food">
        <span class="cat-chip-icon"><i class="bi bi-grid-fill"></i></span>
        <span>All food</span>
    </a>
    @foreach($categories as $cat)
    <a href="{{ route('customer.dashboard', array_merge(request()->except('category'), ['category' => $cat->id])) }}"
        class="cat-chip {{ request('category') == $cat->id ? 'active' : '' }}" aria-label="Browse {{ $cat->name }}">
        <span class="cat-chip-icon">
            @if($cat->icon && str_starts_with($cat->icon, 'http'))
                <img src="{{ $cat->icon }}" alt="" loading="lazy" decoding="async">
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
    <span class="filter-label"><i class="bi bi-sliders me-1"></i>Filters</span>
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
    <div>
        <div class="section-title mb-0">
            {{ request('search') ? 'Results for "'.request('search').'"' : 'Restaurants worth the trip' }}
            <span>({{ $restaurants->total() }})</span>
        </div>
        <div class="small text-muted mt-1">Good food, great people, and a new favourite waiting to happen.</div>
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
                        class="restaurant-img" alt="{{ $restaurant->name }} food" loading="lazy" decoding="async">
                @else
                    <div class="restaurant-img-placeholder">🍽️</div>
                @endif
                @if(!$restaurant->isCurrentlyOpen())
                    <div class="closed-overlay">Currently Closed</div>
                @endif
                @if($restaurant->is_featured)
                    <div class="position-absolute bottom-0 start-0 m-2 badge rounded-pill text-bg-warning"><i class="bi bi-stars me-1"></i>Featured</div>
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
