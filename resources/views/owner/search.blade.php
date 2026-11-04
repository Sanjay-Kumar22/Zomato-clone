@extends('owner.layouts.app')
@section('title', 'Search')
@section('page-title', 'Search Results')

@section('content')

@if(mb_strlen($term) < 2)
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-search fs-1 d-block mb-2"></i>
            Type at least 2 characters to search your restaurants, menu items, and orders.
        </div>
    </div>
@else
    @php $totalResults = $restaurants->count() + $foodItems->count() + $orders->count(); @endphp

    @if($totalResults === 0)
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-emoji-frown fs-1 d-block mb-2"></i>
                No results for "<strong>{{ $term }}</strong>" — try a different order #, item name, or restaurant name.
            </div>
        </div>
    @else

    @if($restaurants->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-shop me-2 text-danger"></i>Restaurants ({{ $restaurants->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <tbody>
                    @foreach($restaurants as $r)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @if($r->image)
                                    <img src="{{ $r->image_url }}" width="36" height="36" class="rounded-circle" style="object-fit:cover">
                                @else
                                    <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white" style="width:36px;height:36px">
                                        {{ strtoupper(substr($r->name,0,1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-500">{{ $r->name }}</div>
                                    <small class="text-muted">{{ $r->pincode }} · {{ $r->cuisines }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('owner.restaurants.edit', $r) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="{{ route('owner.restaurants.food-items.index', $r) }}" class="btn btn-sm btn-outline-danger">Menu</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($foodItems->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-egg-fried me-2 text-danger"></i>Food Items ({{ $foodItems->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <tbody>
                    @foreach($foodItems as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-500">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->restaurant->name ?? '' }} · ₹{{ number_format($item->price, 2) }}</small>
                        </td>
                        <td class="text-end pe-4">
                            @if($item->restaurant)
                            <a href="{{ route('owner.restaurants.food-items.edit', [$item->restaurant, $item]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($orders->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-bag-check me-2 text-danger"></i>Orders ({{ $orders->count() }})</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-500">#{{ $order->id }} — {{ $order->user->name ?? 'Customer' }}</div>
                            <small class="text-muted">{{ $order->restaurant->name ?? '' }} · {{ ucfirst(str_replace('_',' ',$order->status)) }} · {{ $order->created_at->format('d M, h:i A') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('owner.orders.index', ['search' => $order->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @endif
@endif
@endsection
