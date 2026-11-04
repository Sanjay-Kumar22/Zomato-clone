@extends('customer.layouts.app')
@section('title', 'Group Order — ' . $groupOrder->restaurant->name)

@section('content')
<style>
    .go-food-card { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
    .go-food-thumb { width: 64px; height: 64px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
    .go-food-thumb-ph { width: 64px; height: 64px; border-radius: 10px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
    .go-category { font-weight: 700; font-size: 15px; margin: 20px 0 6px; color: #333; }
    .go-add-btn { background: white; border: 1.5px solid #e8342a; color: #e8342a; border-radius: 8px; padding: 5px 16px; font-weight: 600; font-size: 13px; white-space: nowrap; }
    .participant-badge { display: inline-flex; align-items: center; gap: 6px; background: #FDECEA; color: #e8342a; padding: 4px 12px; border-radius: 20px; font-size: 12.5px; font-weight: 600; margin: 3px; }
</style>

<div class="row">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-danger me-2"></i>Group Order</h4>
                <div class="text-muted" style="font-size:13.5px">{{ $groupOrder->restaurant->name }} · Hosted by {{ $groupOrder->host->name }}</div>
            </div>
            @if($groupOrder->status === 'placed')
                <span class="badge bg-success">Order Placed</span>
            @elseif($groupOrder->status === 'cancelled')
                <span class="badge bg-secondary">Cancelled</span>
            @else
                <span class="badge bg-warning text-dark">Open — Adding Items</span>
            @endif
        </div>

        @if($groupOrder->status === 'open')
        <div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 10px rgba(0,0,0,0.06)">
            <div class="card-body p-3">
                <label class="fw-600" style="font-size:12.5px;color:#888">SHARE THIS LINK WITH YOUR GROUP</label>
                <div class="d-flex gap-2 mt-1">
                    <input type="text" class="form-control form-control-sm" id="shareLink" readonly
                        value="{{ route('customer.group-orders.show', $groupOrder->code) }}">
                    <button class="btn btn-outline-danger btn-sm" onclick="copyShareLink()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($groupOrder->status === 'placed')
            <div class="alert alert-success">
                This group order has been placed! <a href="{{ route('customer.orders.show', $groupOrder->order_id) }}" class="alert-link">View order details</a>
            </div>
        @elseif($groupOrder->status === 'cancelled')
            <div class="alert alert-secondary">This group order was cancelled by the host.</div>
        @else
            @forelse($menuByCategory as $categoryName => $items)
                <div class="go-category">{{ $categoryName }}</div>
                @foreach($items as $item)
                <div class="go-food-card">
                    @if($item->image)
                        <img src="{{ $item->image_url }}" class="go-food-thumb" alt="{{ $item->name }}">
                    @else
                        <div class="go-food-thumb-ph">🍽️</div>
                    @endif
                    <div class="flex-grow-1">
                        <div style="font-weight:600;font-size:14px">
                            <span class="veg-dot {{ $item->is_veg ? 'veg' : 'nonveg' }}" style="display:inline-block;width:10px;height:10px;border:1.5px solid {{ $item->is_veg ? '#1e8e4e' : '#c62828' }};margin-right:4px"></span>
                            {{ $item->name }}
                        </div>
                        <div class="text-danger fw-600" style="font-size:13.5px">₹{{ number_format($item->price, 2) }}</div>
                    </div>
                    <form action="{{ route('customer.group-orders.items.add', $groupOrder->code) }}" method="POST">
                        @csrf
                        <input type="hidden" name="food_item_id" value="{{ $item->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="go-add-btn"><i class="bi bi-plus"></i> Add</button>
                    </form>
                </div>
                @endforeach
            @empty
                <p class="text-muted text-center py-4">No menu items available.</p>
            @endforelse
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card border-0 rounded-4 mb-3" style="box-shadow:0 2px 10px rgba(0,0,0,0.06)">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2">Participants</h6>
                @foreach($itemsByUser->keys() as $name)
                    <span class="participant-badge"><i class="bi bi-person-fill"></i> {{ $name }}</span>
                @endforeach
                @if($itemsByUser->isEmpty())
                    <p class="text-muted mb-0" style="font-size:13px">No one has added items yet — be the first!</p>
                @endif
            </div>
        </div>

        <div class="card border-0 rounded-4" style="box-shadow:0 2px 10px rgba(0,0,0,0.06)">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3">All Items ({{ $groupOrder->items->count() }})</h6>
                @forelse($itemsByUser as $userName => $userItems)
                    <div class="mb-3">
                        <div class="fw-600 text-danger" style="font-size:13px">{{ $userName }}</div>
                        @foreach($userItems as $item)
                        <div class="d-flex justify-content-between align-items-center py-1" style="font-size:13.5px">
                            <span>{{ $item->quantity }}x {{ $item->name }}</span>
                            <div class="d-flex align-items-center gap-2">
                                <span>₹{{ number_format($item->price * $item->quantity, 2) }}</span>
                                @if($item->user_id === auth()->id() && $groupOrder->status === 'open')
                                <form action="{{ route('customer.group-orders.items.remove', [$groupOrder->code, $item]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0" style="font-size:12px"><i class="bi bi-x-circle"></i></button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted" style="font-size:13px">Nothing added yet.</p>
                @endforelse

                @if($groupOrder->items->isNotEmpty())
                <div class="d-flex justify-content-between pt-2 border-top fw-bold" style="font-size:15px">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($groupOrder->total(), 2) }}</span>
                </div>
                @endif

                @if($groupOrder->status === 'open' && $groupOrder->host_id === auth()->id())
                    <hr>
                    @if($groupOrder->items->isEmpty())
                        <p class="text-muted text-center" style="font-size:12.5px">Waiting for items before you can place the order.</p>
                    @else
                        <form action="{{ route('customer.group-orders.place', $groupOrder->code) }}" method="POST">
                            @csrf
                            <label class="fw-600 mb-1" style="font-size:12.5px">Deliver to</label>
                            <select name="address_id" class="form-select form-select-sm mb-2" required>
                                <option value="">Select address</option>
                                @foreach($addresses as $addr)
                                <option value="{{ $addr->id }}">{{ $addr->label }} — {{ Str::limit($addr->full_address, 40) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-danger w-100 rounded-pill">
                                <i class="bi bi-bag-check me-1"></i>Place Group Order (COD)
                            </button>
                        </form>
                        <form action="{{ route('customer.group-orders.cancel', $groupOrder->code) }}" method="POST"
                            onsubmit="return confirm('Cancel this group order for everyone?')" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Cancel Group Order</button>
                        </form>
                    @endif
                @elseif($groupOrder->status === 'open')
                    <p class="text-muted text-center mt-2" style="font-size:12px">Only {{ $groupOrder->host->name }} (the host) can place the final order.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function copyShareLink() {
    const el = document.getElementById('shareLink');
    el.select();
    navigator.clipboard.writeText(el.value);
}
</script>
@endsection
