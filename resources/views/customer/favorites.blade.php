@extends('customer.layouts.app')
@section('title', 'My Favorites')

@section('styles')
<style>
    .restaurant-card {
        border: none; border-radius: 16px; overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.25s, box-shadow 0.25s;
        text-decoration: none; color: inherit; display: block;
        background: white; position: relative;
    }
    .restaurant-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.13); color: inherit; }
    .restaurant-img { height: 170px; object-fit: cover; width: 100%; }
    .restaurant-img-placeholder {
        height: 170px; background: linear-gradient(135deg, #fff0f0, #ffe5e5);
        display: flex; align-items: center; justify-content: center; font-size: 56px;
    }
    .restaurant-body { padding: 16px; }
    .restaurant-name { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
    .restaurant-meta { font-size: 12px; color: #999; margin-bottom: 10px; }
    .open-badge { background: #e8f5e9; color: #2e7d32; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
    .closed-badge { background: #fce4ec; color: #c62828; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
    .rating-badge { background: #e8f5e9; color: #2e7d32; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: flex; align-items: center; gap: 3px; }
    .fav-heart-btn {
        position: absolute; top: 12px; right: 12px; z-index: 2;
        background: white; border: none; border-radius: 50%;
        width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15); color: #e8342a; font-size: 16px;
    }
</style>
@endsection

@section('content')
<h4 class="fw-700 mb-4"><i class="bi bi-heart-fill me-2 text-danger"></i>My Favorites</h4>

<div class="row g-4">
    @forelse($restaurants as $restaurant)
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div style="position:relative">
            <form action="{{ route('customer.favorites.toggle', $restaurant) }}" method="POST" class="fav-heart-btn-form" style="position:absolute;top:12px;right:12px;z-index:2;">
                @csrf
                <button type="submit" class="fav-heart-btn" style="position:static" title="Remove from favorites">
                    <i class="bi bi-heart-fill"></i>
                </button>
            </form>
            <a href="{{ route('customer.restaurant.show', $restaurant) }}" class="restaurant-card">
                @if($restaurant->image)
                    <img src="{{ $restaurant->image_url }}" class="restaurant-img" alt="{{ $restaurant->name }}">
                @else
                    <div class="restaurant-img-placeholder">🍽️</div>
                @endif
                <div class="restaurant-body">
                    <div class="restaurant-name">{{ $restaurant->name }}</div>
                    <div class="restaurant-meta">
                        <i class="bi bi-geo-alt me-1"></i>{{ $restaurant->pincode }}
                        · {{ $restaurant->food_items_count }} items
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        @if($restaurant->is_open)
                            <span class="open-badge">🟢 Open</span>
                        @else
                            <span class="closed-badge">⛔ Closed</span>
                        @endif
                        @php $rating = round($restaurant->avg_rating, 1); @endphp
                        @if($rating > 0)
                        <span class="rating-badge"><i class="bi bi-star-fill" style="font-size:10px"></i>{{ $rating }}</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="background:white;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06)">
            <div style="font-size:64px">💔</div>
            <h5 class="mt-3">No favorites yet</h5>
            <p class="text-muted">Tap the heart icon on any restaurant to save it here</p>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-danger rounded-pill px-4">Browse Restaurants</a>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $restaurants->links() }}</div>
@endsection
