@extends('customer.layouts.app')
@section('title', $restaurant->name)

@section('styles')
<style>
    .rest-header {
        background: white; border-radius: 16px; padding: 24px;
        margin-bottom: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }
    .rest-header img { width: 90px; height: 90px; border-radius: 14px; object-fit: cover; }
    .rest-badge { font-size: 12px; padding: 5px 12px; border-radius: 20px; font-weight: 600; }
    .rating-star { color: #f9a825; }

    /* Veg filter */
    .veg-toggle { display: flex; gap: 8px; align-items: center; margin-bottom: 20px; }
    .veg-btn {
        border: 2px solid #ddd; background: white; border-radius: 50px;
        padding: 6px 16px; font-size: 13px; font-weight: 600; color: #555;
        cursor: pointer; text-decoration: none; transition: all 0.2s;
    }
    .veg-btn.active { border-color: #2e7d32; color: #2e7d32; background: #f1f8e9; }
    .veg-btn.nonveg-active { border-color: #c62828; color: #c62828; background: #fce4ec; }

    .category-title {
        font-size: 18px; font-weight: 700; color: #1a1a2e;
        margin: 28px 0 16px; padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
    }
    .food-card {
        background: white; border-radius: 12px; padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex; gap: 16px; align-items: flex-start;
        margin-bottom: 12px;
    }
    .food-thumb {
        width: 90px; height: 90px; border-radius: 10px;
        object-fit: cover; flex-shrink: 0;
    }
    .food-thumb-placeholder {
        width: 90px; height: 90px; border-radius: 10px;
        background: #f5f5f5; display: flex;
        align-items: center; justify-content: center;
        font-size: 30px; flex-shrink: 0;
    }
    .food-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
    .food-desc { font-size: 12px; color: #888; margin: 4px 0 8px; line-height: 1.4; }
    .food-price { font-size: 17px; font-weight: 700; color: #e8342a; }
    .add-btn {
        background: #e8342a; color: white; border: none;
        border-radius: 8px; padding: 8px 20px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        white-space: nowrap;
    }
    .add-btn:hover { background: #c62828; }
    .veg-dot {
        width: 12px; height: 12px; border-radius: 3px;
        border: 2px solid; display: inline-block; margin-right: 5px;
    }
    .veg-dot.veg { border-color: #2e7d32; background: #2e7d32; }
    .veg-dot.nonveg { border-color: #c62828; background: #c62828; }

    /* Sticky sidebar cart */
    .cart-sidebar {
        position: sticky; top: 90px;
    }
    .cart-sidebar .card { border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.1); }

    .fav-heart-btn {
        background: white; border: 1.5px solid #eee; border-radius: 50%;
        width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
        color: #ccc; font-size: 18px; flex-shrink: 0;
    }
    .fav-heart-btn.active { color: #e8342a; border-color: #ffd6d6; }

    .option-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f5f5f5; }
    .option-row:last-child { border-bottom:none; }
</style>
@endsection

@section('content')
@if($customerPincode && !$deliversToCustomer)
<div class="alert border-0 rounded-3 mb-3 d-flex align-items-center gap-2" style="background:#fce4ec;color:#c62828;font-size:13.5px;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        This restaurant doesn't deliver to your selected location (<strong>{{ $customerPincode }}</strong>).
        You can still browse the menu, but you won't be able to place an order here unless you
        <button type="button" class="btn btn-link p-0 align-baseline" style="font-size:13.5px;color:#c62828;text-decoration:underline;" data-bs-toggle="modal" data-bs-target="#locationModal">change your location</button>
        or use a different delivery address at checkout.
    </div>
</div>
@endif
<!-- Restaurant Header -->
<div class="rest-header">
    <div class="d-flex align-items-start gap-4 flex-wrap">
        @if($restaurant->image)
            <img src="{{ $restaurant->image_url }}" alt="{{ $restaurant->name }}">
        @else
            <div style="width:90px;height:90px;background:#fff0f0;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:38px;flex-shrink:0">🍽️</div>
        @endif
        <div class="flex-grow-1">
            <h3 class="fw-bold mb-1">{{ $restaurant->name }}</h3>
            <p class="text-muted mb-2" style="font-size:14px">
                <i class="bi bi-geo-alt me-1"></i>{{ $restaurant->address }}, {{ $restaurant->pincode }}
                @if($restaurant->lat && $restaurant->lng)
                    · <a href="https://www.openstreetmap.org/?mlat={{ $restaurant->lat }}&mlon={{ $restaurant->lng }}#map=16/{{ $restaurant->lat }}/{{ $restaurant->lng }}"
                        target="_blank" rel="noopener" class="text-danger fw-600" style="text-decoration:none">
                        View on Map <i class="bi bi-box-arrow-up-right" style="font-size:11px"></i>
                    </a>
                @endif
            </p>
            @if($restaurant->cuisines_list)
            <p class="text-danger mb-2" style="font-size:13px;font-weight:600">
                {{ implode(' · ', $restaurant->cuisines_list) }}
            </p>
            @endif
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($restaurant->isCurrentlyOpen())
                    <span class="rest-badge" style="background:#e8f5e9;color:#2e7d32">🟢 Open Now</span>
                @else
                    <span class="rest-badge" style="background:#fce4ec;color:#c62828">⛔ Currently Closed</span>
                @endif
                @php $rating = round($restaurant->avg_rating, 1); @endphp
                @if($rating > 0)
                <span class="rest-badge" style="background:#fffde7;color:#f57f17">
                    <i class="bi bi-star-fill rating-star me-1"></i>{{ $rating }}
                </span>
                @endif
                <span class="rest-badge" style="background:#f3f4f6;color:#555">
                    <i class="bi bi-egg-fried me-1"></i>{{ $menuByCategory->flatten()->count() }} items
                </span>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <form action="{{ route('customer.favorites.toggle', $restaurant) }}" method="POST">
                @csrf
                <button type="submit" class="fav-heart-btn {{ $isFavorited ? 'active' : '' }}" title="Toggle favorite">
                    <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                </button>
            </form>
            @auth
            @if($restaurant->isCurrentlyOpen())
            <form action="{{ route('customer.group-orders.start', $restaurant) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" style="white-space:nowrap">
                    <i class="bi bi-people-fill me-1"></i>Start Group Order
                </button>
            </form>
            @endif
            @endauth
        </div>
    </div>
</div>

@if($restaurant->photos->isNotEmpty())
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;padding-bottom:4px;">
    @foreach($restaurant->photos as $photo)
    <a href="{{ $photo->url }}" target="_blank" rel="noopener" style="flex-shrink:0;">
        <img src="{{ $photo->url }}" alt="{{ $restaurant->name }} photo"
            style="height:90px;width:130px;object-fit:cover;border-radius:10px;">
    </a>
    @endforeach
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Menu search -->
        <div class="mb-3">
            <div class="position-relative">
                <i class="bi bi-search position-absolute" style="left:14px;top:50%;transform:translateY(-50%);color:#B0A296"></i>
                <input type="text" id="menuSearchInput" class="form-control" style="padding-left:38px;border-radius:12px"
                    placeholder="Search for dishes in this menu...">
            </div>
            <div id="menuSearchEmpty" class="text-center text-muted py-4 d-none" style="font-size:14px">
                <i class="bi bi-emoji-frown" style="font-size:28px"></i>
                <p class="mt-2 mb-0">No dishes match your search.</p>
            </div>
        </div>

        <!-- Veg/Non-veg filter -->
        <div class="veg-toggle">
            <span style="font-size:13px;font-weight:600;color:#666">Filter:</span>
            <a href="{{ route('customer.restaurant.show', $restaurant) }}"
                class="veg-btn {{ !request('type') ? 'active' : '' }}">All</a>
            <a href="{{ route('customer.restaurant.show', [$restaurant, 'type' => 'veg']) }}"
                class="veg-btn {{ request('type') == 'veg' ? 'active' : '' }}">🟢 Veg Only</a>
            <a href="{{ route('customer.restaurant.show', [$restaurant, 'type' => 'nonveg']) }}"
                class="veg-btn {{ request('type') == 'nonveg' ? 'nonveg-active' : '' }}">🔴 Non-Veg</a>
        </div>

        <div id="menuList">
        @forelse($menuByCategory as $categoryName => $items)
            @php
                $filteredItems = $items;
                if(request('type') === 'veg') $filteredItems = $items->where('is_veg', true);
                if(request('type') === 'nonveg') $filteredItems = $items->where('is_veg', false);
            @endphp
            @if($filteredItems->isNotEmpty())
            <div class="menu-category" data-category>
            <div class="category-title">{{ $categoryName ?? 'Other' }}</div>
            @foreach($filteredItems as $item)
            <div class="food-card" data-food-name="{{ strtolower($item->name) }}">
                @if($item->image)
                    <img src="{{ $item->image_url }}" class="food-thumb" alt="{{ $item->name }}">
                @else
                    <div class="food-thumb-placeholder">🍽️</div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap mb-1" style="gap:6px">
                        <span class="veg-dot {{ $item->is_veg ? 'veg' : 'nonveg' }}"></span>
                        <span class="food-name">{{ $item->name }}</span>
                        @if($bestsellerIds->contains($item->id))
                            <span style="background:#fff3e0;color:#e65100;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">
                                🔥 Bestseller
                            </span>
                        @endif
                        @foreach($item->tags_list as $tag)
                            @php
                                $tagStyle = match(true) {
                                    str_contains(strtolower($tag), 'spicy') => 'background:#fce4ec;color:#c62828',
                                    str_contains(strtolower($tag), 'best') => 'background:#fff3e0;color:#e65100',
                                    str_contains(strtolower($tag), 'new') => 'background:#e8f5e9;color:#2e7d32',
                                    default => 'background:#f3e5f5;color:#6a1b9a',
                                };
                            @endphp
                            <span style="{{ $tagStyle }};font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                    @if($item->description)
                        <div class="food-desc">{{ Str::limit($item->description, 80) }}</div>
                    @endif
                    <div class="food-price">₹{{ number_format($item->price, 2) }}</div>
                </div>
                @if($restaurant->isCurrentlyOpen())
                    @if($item->options->isNotEmpty())
                    <button type="button" class="add-btn" data-bs-toggle="modal" data-bs-target="#optionsModal{{ $item->id }}">
                        <i class="bi bi-plus"></i> Add
                    </button>
                    <div class="modal fade" id="optionsModal{{ $item->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="border-radius:16px;border:none">
                                <form action="{{ route('customer.cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="food_item_id" value="{{ $item->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <div class="modal-header" style="border-bottom:1px solid #f0f0f0">
                                        <h6 class="modal-title fw-bold">{{ $item->name }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-3" style="font-size:12px">Customize your order</p>
                                        @foreach($item->options as $opt)
                                        <label class="option-row" style="cursor:pointer">
                                            <span style="font-size:14px">
                                                <input type="checkbox" name="option_ids[]" value="{{ $opt->id }}" class="form-check-input me-2">
                                                {{ $opt->label }}: {{ $opt->value }}
                                            </span>
                                            <span class="text-danger fw-600" style="font-size:13px">
                                                @if($opt->extra_price > 0)+₹{{ number_format($opt->extra_price, 2) }}@else Free @endif
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <div class="modal-footer" style="border-top:1px solid #f0f0f0">
                                        <button type="submit" class="add-btn w-100">Add to Cart</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <form action="{{ route('customer.cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="food_item_id" value="{{ $item->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="add-btn">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </form>
                    @endif
                @else
                    <button class="add-btn" disabled style="opacity:0.5;cursor:not-allowed">Closed</button>
                @endif
            </div>
            @endforeach
            </div>
            @endif
        @empty
            <div class="text-center py-5 text-muted">
                <div style="font-size:48px">🍽️</div>
                <p class="mt-2">No menu items available</p>
            </div>
        @endforelse
        </div>

        <!-- Reviews -->
        <div class="category-title">Customer Reviews ({{ $reviews->total() }})</div>

        @if($reviews->total() > 0)
        <div class="d-flex gap-4 mb-3 p-3" style="background:#faf9f9;border-radius:12px">
            <div>
                <div class="text-muted" style="font-size:11px;font-weight:600">FOOD QUALITY</div>
                <div style="font-size:18px;font-weight:700;color:#e8342a">
                    {{ round($restaurant->avg_food_rating, 1) ?: '—' }} <i class="bi bi-star-fill" style="font-size:13px;color:#f9a825"></i>
                </div>
            </div>
            <div>
                <div class="text-muted" style="font-size:11px;font-weight:600">DELIVERY EXPERIENCE</div>
                <div style="font-size:18px;font-weight:700;color:#e8342a">
                    {{ round($restaurant->avg_delivery_rating, 1) ?: '—' }} <i class="bi bi-star-fill" style="font-size:13px;color:#f9a825"></i>
                </div>
            </div>
        </div>
        @endif

        @forelse($reviews as $review)
        <div class="food-card" style="align-items:flex-start;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="food-name">{{ $review->user->name }}</span>
                    <span style="color:#f9a825;font-size:13px;font-weight:700">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </span>
                </div>
                @if($review->food_rating || $review->delivery_rating)
                <div class="text-muted mb-1" style="font-size:11px">
                    @if($review->food_rating) Food: {{ $review->food_rating }}/5 @endif
                    @if($review->food_rating && $review->delivery_rating) · @endif
                    @if($review->delivery_rating) Delivery: {{ $review->delivery_rating }}/5 @endif
                </div>
                @endif
                @if($review->comment)
                <div class="food-desc mb-0">{{ $review->comment }}</div>
                @endif
                @if($review->owner_reply)
                <div class="mt-2 p-2 rounded-3" style="background:#FFF8F5;border-left:3px solid #e8342a">
                    <div style="font-size:11.5px;font-weight:700;color:#e8342a">
                        <i class="bi bi-reply-fill me-1"></i>Reply from {{ $restaurant->name }}
                    </div>
                    <div style="font-size:13px;color:#3A2F26;margin-top:2px">{{ $review->owner_reply }}</div>
                </div>
                @endif
                <div class="text-muted" style="font-size:11px;margin-top:4px">{{ $review->created_at->diffForHumans() }}</div>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-muted">
            <p class="mb-0" style="font-size:14px">No reviews yet. Be the first to rate after your order!</p>
        </div>
        @endforelse
        <div class="mt-3">{{ $reviews->links() }}</div>
    </div>

    <!-- Sidebar Cart Summary -->
    <div class="col-lg-4 d-none d-lg-block">
        <div class="cart-sidebar">
            @php $cartService = app(\App\Services\CartService::class); $cartItems = $cartService->get(); @endphp
            @if(!empty($cartItems))
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-bag me-2 text-danger"></i>Your Cart</h6>
                    @foreach($cartItems as $item)
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span class="fw-600">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total</span>
                        <span class="text-danger">₹{{ number_format($cartService->total(), 2) }}</span>
                    </div>
                    <a href="{{ route('customer.cart.index') }}" class="btn btn-danger w-100 rounded-pill">
                        View Cart <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body p-4 text-center text-muted">
                    <div style="font-size:40px">🛒</div>
                    <p class="mt-2 mb-0" style="font-size:13px">Add items to see your cart here</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($similarRestaurants->isNotEmpty())
<div class="mt-5">
    <h5 class="fw-700 mb-3"><i class="bi bi-stars me-2 text-danger"></i>You might also like</h5>
    <div class="row g-3">
        @foreach($similarRestaurants as $sr)
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('customer.restaurant.show', $sr) }}" class="text-decoration-none" style="color:inherit;">
                <div class="card h-100 border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);transition:transform .2s;">
                    @if($sr->image)
                        <img src="{{ $sr->image_url }}" style="height:130px;object-fit:cover;width:100%;" alt="{{ $sr->name }}">
                    @else
                        <div style="height:130px;background:linear-gradient(135deg,#fff0f0,#ffe5e5);display:flex;align-items:center;justify-content:center;font-size:40px;">🍽️</div>
                    @endif
                    <div class="p-3">
                        <div class="fw-700" style="font-size:14.5px;color:#241C15;">{{ $sr->name }}</div>
                        <div class="text-muted" style="font-size:11.5px;margin-bottom:6px;">{{ $sr->cuisines }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size:11px;">{{ $sr->food_items_count }} items</span>
                            @php $srRating = round($sr->reviews_avg_rating, 1); @endphp
                            @if($srRating > 0)
                            <span style="background:#e8f5e9;color:#2e7d32;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">
                                <i class="bi bi-star-fill" style="font-size:9px"></i> {{ $srRating }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.getElementById('menuSearchInput')?.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    const categories = document.querySelectorAll('[data-category]');
    let anyVisible = false;

    categories.forEach(cat => {
        let categoryHasMatch = false;
        cat.querySelectorAll('.food-card').forEach(card => {
            const matches = !query || (card.dataset.foodName || '').includes(query);
            card.style.display = matches ? '' : 'none';
            if (matches) categoryHasMatch = true;
        });
        cat.style.display = categoryHasMatch ? '' : 'none';
        if (categoryHasMatch) anyVisible = true;
    });

    document.getElementById('menuSearchEmpty').classList.toggle('d-none', anyVisible || !query);
});
</script>
@endsection
