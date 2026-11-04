@extends('customer.layouts.app')
@section('title', 'Order #' . $order->id)

@section('styles')
<style>
    .tracking-steps { position: relative; padding-left: 30px; }
    .tracking-steps::before {
        content: ''; position: absolute; left: 10px; top: 0; bottom: 0;
        width: 2px; background: #eee;
    }
    .tracking-step { position: relative; padding-bottom: 24px; }
    .tracking-step:last-child { padding-bottom: 0; }
    .step-dot {
        position: absolute; left: -25px; top: 2px;
        width: 18px; height: 18px; border-radius: 50%;
        background: #ddd; border: 2px solid white;
        box-shadow: 0 0 0 2px #ddd;
        display: flex; align-items: center; justify-content: center;
        font-size: 8px; color: white;
    }
    .step-dot.done { background: #2e7d32; box-shadow: 0 0 0 2px #2e7d32; }
    .step-dot.active { background: #e8342a; box-shadow: 0 0 0 2px #e8342a; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{box-shadow:0 0 0 2px #e8342a} 50%{box-shadow:0 0 0 5px rgba(232,52,42,0.3)} }
    .step-label { font-size: 14px; font-weight: 600; color: #333; }
    .step-label.inactive { color: #aaa; font-weight: 400; }
    .step-desc { font-size: 12px; color: #888; margin-top: 2px; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-bold mb-0">Order #{{ $order->id }}</h4>
    </div>
    @if(in_array($order->status, ['pending', 'confirmed']))
    <form action="{{ route('customer.orders.cancel', $order) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to cancel this order?');">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
            <i class="bi bi-x-circle me-1"></i>Cancel Order
        </button>
    </form>
    @endif
</div>

<div class="row g-4">
    <div class="col-md-8">
        <!-- Order Tracking -->
        <div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-truck me-2 text-danger"></i>Order Tracking</h6>
                @php
                    $steps = [
                        'pending'          => ['label' => 'Order Placed', 'desc' => 'We received your order', 'icon' => '✓'],
                        'confirmed'        => ['label' => 'Order Confirmed', 'desc' => 'Restaurant accepted your order', 'icon' => '✓'],
                        'preparing'        => ['label' => 'Being Prepared', 'desc' => 'Chef is cooking your food', 'icon' => '✓'],
                        'out_for_delivery' => [
                            'label' => 'Out for Delivery',
                            'desc'  => $order->deliveryAgent
                                ? $order->deliveryAgent->name . ' is on the way' . ($order->deliveryAgent->phone ? ' · ' . $order->deliveryAgent->phone : '')
                                : 'Rider is on the way',
                            'icon' => '✓',
                        ],
                        'delivered'        => ['label' => 'Delivered', 'desc' => 'Enjoy your meal!', 'icon' => '✓'],
                    ];
                    $stepKeys = array_keys($steps);
                    $currentIdx = array_search($order->status, $stepKeys);
                @endphp

                @if($order->status === 'cancelled')
                    <div class="text-center py-3">
                        <div style="font-size:48px">❌</div>
                        <h6 class="mt-2 text-danger fw-bold">Order Cancelled</h6>
                    </div>
                @else
                <div class="tracking-steps">
                    @foreach($steps as $key => $step)
                    @php
                        $idx = array_search($key, $stepKeys);
                        $isDone = $idx < $currentIdx;
                        $isActive = $idx === $currentIdx;
                    @endphp
                    <div class="tracking-step">
                        <div class="step-dot {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                            @if($isDone || $isActive) ✓ @endif
                        </div>
                        <div class="step-label {{ (!$isDone && !$isActive) ? 'inactive' : '' }}">
                            {{ $step['label'] }}
                            @if($isActive) <span class="badge bg-danger ms-1" style="font-size:10px">Current</span> @endif
                        </div>
                        <div class="step-desc">{{ $step['desc'] }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        @php
            $showMap = in_array($order->status, ['confirmed', 'preparing', 'out_for_delivery'])
                && $order->restaurant?->lat && $order->restaurant?->lng;
        @endphp
        @if($showMap)
        <!-- Live Tracking Map -->
        <div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-danger"></i>Live Tracking</h6>
                    <span id="mapStatusBadge" class="badge bg-light text-dark" style="font-size:11px"></span>
                </div>
                <div id="trackMap" style="height:280px;border-radius:12px;overflow:hidden"></div>
                <div id="etaRow" class="d-none mt-3 d-flex align-items-center gap-4" style="font-size:13px">
                    <div>
                        <div class="text-muted" style="font-size:11px">DISTANCE</div>
                        <div class="fw-bold" id="etaDistance">—</div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:11px">ARRIVING IN</div>
                        <div class="fw-bold text-danger" id="etaTime">—</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Order Items -->
        <div class="card border-0 rounded-4 mb-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-bag me-2 text-danger"></i>Order Items</h6>
                @foreach($order->items as $item)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div>
                        <span style="font-size:14px">{{ $item->name }} <span class="text-muted">× {{ $item->quantity }}</span></span>
                        @if(!empty($item->selected_options))
                            <div style="font-size:11px;color:#aaa">
                                {{ collect($item->selected_options)->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                            </div>
                        @endif
                    </div>
                    <span class="fw-600">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</span>
                </div>
                @endforeach
                <div class="d-flex justify-content-between py-2 text-muted" style="font-size:13px">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="d-flex justify-content-between py-2" style="font-size:13px;color:#2e7d32">
                    <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                    <span>- ₹{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                @if($order->points_redeemed > 0)
                <div class="d-flex justify-content-between py-2" style="font-size:13px;color:#2e7d32">
                    <span><i class="bi bi-gem me-1"></i>Points Redeemed ({{ $order->points_redeemed }} pts)</span>
                    <span>- ₹{{ number_format($order->points_discount, 2) }}</span>
                </div>
                @endif
                @if($order->wallet_used > 0)
                <div class="d-flex justify-content-between py-2" style="font-size:13px;color:#2e7d32">
                    <span><i class="bi bi-wallet2 me-1"></i>Paid from Wallet</span>
                    <span>- ₹{{ number_format($order->wallet_used, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 text-muted" style="font-size:13px">
                    <span>Delivery Fee</span>
                    <span>₹{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @if($order->tip_amount > 0)
                <div class="d-flex justify-content-between py-2 text-muted" style="font-size:13px">
                    <span><i class="bi bi-heart-fill text-danger me-1" style="font-size:11px"></i>Delivery Partner Tip</span>
                    <span>₹{{ number_format($order->tip_amount, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 fw-bold fs-5">
                    <span>Total</span>
                    <span class="text-danger">₹{{ number_format($order->total, 2) }}</span>
                </div>
                @if($order->points_earned > 0)
                <div class="text-muted mt-1" style="font-size:11.5px">
                    <i class="bi bi-gem me-1" style="color:#c9971e"></i>
                    @if($order->status === 'delivered')
                        You earned {{ $order->points_earned }} loyalty points on this order!
                    @else
                        You'll earn {{ $order->points_earned }} loyalty points once this order is delivered.
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Delivery Address -->
        <div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Delivery Address</h6>
                <p class="mb-0" style="font-size:14px">
                    <strong>{{ $order->address->label }}</strong><br>
                    {{ $order->address->full_address }}<br>
                    <span class="text-muted">Pincode: {{ $order->address->pincode }}</span>
                </p>
            </div>
        </div>
        <!-- Rate & Review -->
        @if($order->status === 'delivered')
        <div class="card border-0 rounded-4 mt-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i>
                    {{ $order->review ? 'Your Rating' : 'Rate this Order' }}
                </h6>

                <form action="{{ $order->review
                        ? route('customer.reviews.update', $order->review)
                        : route('customer.reviews.store', $order) }}"
                      method="POST" id="reviewForm">
                    @csrf
                    @if($order->review) @method('PATCH') @endif

                    <div class="mb-3" id="starPicker">
                        <div class="text-muted mb-1" style="font-size:12px">Overall Rating</div>
                        @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $order->review && $order->review->rating >= $i ? 'bi-star-fill' : 'bi-star' }} star-input"
                           data-value="{{ $i }}"
                           style="font-size:28px;color:#f9a825;cursor:pointer;"></i>
                        @endfor
                        <input type="hidden" name="rating" id="ratingInput" value="{{ $order->review->rating ?? 0 }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-muted mb-1" style="font-size:12px">Food Quality</div>
                            <div id="foodStarPicker">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $order->review && $order->review->food_rating >= $i ? 'bi-star-fill' : 'bi-star' }} food-star-input"
                                   data-value="{{ $i }}"
                                   style="font-size:20px;color:#f9a825;cursor:pointer;"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="food_rating" id="foodRatingInput" value="{{ $order->review->food_rating ?? 0 }}">
                        </div>
                        <div class="col-6">
                            <div class="text-muted mb-1" style="font-size:12px">Delivery Experience</div>
                            <div id="deliveryStarPicker">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $order->review && $order->review->delivery_rating >= $i ? 'bi-star-fill' : 'bi-star' }} delivery-star-input"
                                   data-value="{{ $i }}"
                                   style="font-size:20px;color:#f9a825;cursor:pointer;"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="delivery_rating" id="deliveryRatingInput" value="{{ $order->review->delivery_rating ?? 0 }}">
                        </div>
                    </div>

                    <textarea name="comment" class="form-control mb-3" rows="3" maxlength="1000"
                        placeholder="Share your experience (optional)">{{ $order->review->comment ?? '' }}</textarea>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            {{ $order->review ? 'Update Review' : 'Submit Review' }}
                        </button>
                        @if($order->review)
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            onclick="removeReview()">Remove</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Report an Issue -->
        <div class="card border-0 rounded-4 mt-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                @if($order->dispute)
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Reported Issue</h6>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-dark border">{{ $order->dispute->reason }}</span>
                        @if($order->dispute->status === 'open')
                            <span class="badge bg-warning text-dark">Under Review</span>
                        @elseif($order->dispute->status === 'resolved')
                            <span class="badge bg-success">Refunded ₹{{ number_format($order->dispute->refund_amount, 2) }}</span>
                        @else
                            <span class="badge bg-secondary">Rejected</span>
                        @endif
                    </div>
                    <p class="text-muted mb-0" style="font-size:13.5px">{{ $order->dispute->description }}</p>
                    @if($order->dispute->resolution_note)
                        <div class="mt-2 p-2 rounded-3" style="background:#F5F5F5;font-size:13px">
                            <strong>Response:</strong> {{ $order->dispute->resolution_note }}
                        </div>
                    @endif
                @else
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Something Wrong with This Order?</h6>
                    <p class="text-muted mb-3" style="font-size:13px">Missing item, wrong order, or a quality issue? Let us know and we'll review it.</p>
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#reportIssueModal">
                        Report an Issue
                    </button>
                @endif
            </div>
        </div>

        <div class="modal fade" id="reportIssueModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-2" style="border-radius:16px;border:none">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Report an Issue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('customer.disputes.store', $order) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px">What went wrong?</label>
                                <select name="reason" class="form-select" required>
                                    <option value="Item Missing">Item Missing</option>
                                    <option value="Wrong Item">Wrong Item</option>
                                    <option value="Quality Issue">Quality Issue</option>
                                    <option value="Late Delivery">Late Delivery</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px">Describe the issue</label>
                                <textarea name="description" class="form-control" rows="4" maxlength="1000"
                                    placeholder="Tell us what happened..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 rounded-pill">Submit Report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Standalone form built fresh on click (never nested inside #reviewForm) —
        // see the fix note above the Remove button for why that matters.
        function removeReview() {
            if (!confirm('Remove this review?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json($order->review ? route('customer.reviews.destroy', $order->review) : '#');
            form.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = @json(csrf_token());
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            document.body.appendChild(form);
            form.submit();
        }

        (function() {
            const stars = document.querySelectorAll('#starPicker .star-input');
            const input = document.getElementById('ratingInput');
            function paint(val) {
                stars.forEach(s => {
                    s.classList.toggle('bi-star-fill', s.dataset.value <= val);
                    s.classList.toggle('bi-star', s.dataset.value > val);
                });
            }
            stars.forEach(s => {
                s.addEventListener('click', function() {
                    input.value = this.dataset.value;
                    paint(this.dataset.value);
                });
            });

            function wireSubPicker(selector, inputId) {
                const subStars = document.querySelectorAll(selector);
                const subInput = document.getElementById(inputId);
                function subPaint(val) {
                    subStars.forEach(s => {
                        s.classList.toggle('bi-star-fill', s.dataset.value <= val);
                        s.classList.toggle('bi-star', s.dataset.value > val);
                    });
                }
                subStars.forEach(s => {
                    s.addEventListener('click', function() {
                        subInput.value = this.dataset.value;
                        subPaint(this.dataset.value);
                    });
                });
            }
            wireSubPicker('#foodStarPicker .food-star-input', 'foodRatingInput');
            wireSubPicker('#deliveryStarPicker .delivery-star-input', 'deliveryRatingInput');
        })();
        </script>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card border-0 rounded-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Order Info</h6>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Restaurant</div>
                    <div class="fw-600" style="font-size:14px">{{ $order->restaurant->name }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Order Status</div>
                    @php
                        $colors = ['pending'=>['#fff8e1','#f57f17'],'confirmed'=>['#e3f2fd','#1565c0'],'preparing'=>['#fff3e0','#e65100'],'out_for_delivery'=>['#f3e5f5','#6a1b9a'],'delivered'=>['#e8f5e9','#2e7d32'],'cancelled'=>['#fce4ec','#880e4f']];
                        $c = $colors[$order->status] ?? ['#f5f5f5','#666'];
                    @endphp
                    <span class="badge px-3 py-2 rounded-pill" style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:12px">
                        {{ ucfirst(str_replace('_',' ',$order->status)) }}
                    </span>
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Order ID</div>
                    <div class="fw-600" style="font-size:14px">#{{ $order->id }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Payment</div>
                    @if(in_array($order->payment_method, ['stripe', 'razorpay']))
                        @if($order->payment_status === 'paid')
                            <span class="badge px-3 py-2 rounded-pill" style="background:#e8f5e9;color:#2e7d32;font-size:12px">
                                <i class="bi bi-check-circle-fill me-1"></i>Paid Online
                            </span>
                        @else
                            <span class="badge px-3 py-2 rounded-pill" style="background:#fce4ec;color:#c62828;font-size:12px">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>Payment Pending
                            </span>
                        @endif
                    @else
                        <span class="badge px-3 py-2 rounded-pill" style="background:#fff8e1;color:#f57f17;font-size:12px">
                            <i class="bi bi-cash me-1"></i>Cash on Delivery
                        </span>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="text-muted" style="font-size:12px">Delivery Time</div>
                    <div class="fw-600" style="font-size:14px">
                        @if($order->delivery_slot === 'ASAP' || !$order->delivery_slot)
                            <i class="bi bi-lightning-fill text-warning me-1"></i>As soon as possible
                        @else
                            <i class="bi bi-clock me-1"></i>{{ $order->delivery_slot }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px">Placed On</div>
                    <div class="fw-600" style="font-size:14px">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

        @if($order->deliveryAgent)
        <div class="card border-0 rounded-4 mt-3" style="box-shadow:0 2px 12px rgba(0,0,0,0.07)">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-bicycle me-2 text-danger"></i>Delivery Partner</h6>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:50%;background:#fce4ec;color:#e8342a;
                                display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px">
                        {{ strtoupper(substr($order->deliveryAgent->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:14px">{{ $order->deliveryAgent->name }}</div>
                        @if($order->deliveryAgent->phone)
                        <a href="tel:{{ $order->deliveryAgent->phone }}" class="text-decoration-none" style="font-size:13px">
                            <i class="bi bi-telephone me-1"></i>{{ $order->deliveryAgent->phone }}
                        </a>
                        @endif
                    </div>
                </div>
                @if(!in_array($order->status, ['delivered', 'cancelled']))
                @php
                    $unreadChatCount = \App\Models\OrderMessage::where('order_id', $order->id)
                        ->where('sender_id', '!=', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                @endphp
                <a href="{{ route('customer.chat.show', $order) }}" class="btn btn-outline-danger btn-sm w-100 rounded-pill position-relative">
                    <i class="bi bi-chat-dots-fill me-1"></i>Chat with Delivery Partner
                    <span id="chatUnreadBadge" class="badge bg-danger rounded-pill ms-1 {{ $unreadChatCount > 0 ? '' : 'd-none' }}">{{ $unreadChatCount }}</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{--
            Online-payment orders are now only ever created once the payment
            actually succeeds (see PendingCheckout / CheckoutFinalizer), so a
            stripe/razorpay order can no longer be sitting here unpaid — there
            is nothing to "retry" any more. If you still have old orders from
            before this change with payment_status = pending, cancel them or
            follow up with the customer directly.
        --}}

        <form action="{{ route('customer.orders.reorder', $order) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger w-100 rounded-pill mt-3">
                <i class="bi bi-arrow-repeat me-1"></i>Order Again
            </button>
        </form>
    </div>
</div>

@if($showMap ?? false)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Live tracking map — restaurant + drop location always shown; the rider marker
// uses the delivery agent's real GPS position (sent from their phone while the
// order is out_for_delivery) once available, and otherwise sits at the restaurant.
(function() {
    const restaurant = { lat: {{ $order->restaurant->lat }}, lng: {{ $order->restaurant->lng }} };
    const drop = @json($order->address && $order->address->lat && $order->address->lng
        ? ['lat' => (float) $order->address->lat, 'lng' => (float) $order->address->lng]
        : null);

    const map = L.map('trackMap', { zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);

    const restaurantIcon = L.divIcon({ html: '🍽️', className: '', iconSize: [28, 28] });
    const dropIcon = L.divIcon({ html: '📍', className: '', iconSize: [28, 28] });
    const riderIcon = L.divIcon({ html: '🛵', className: '', iconSize: [28, 28] });

    const restaurantMarker = L.marker([restaurant.lat, restaurant.lng], { icon: restaurantIcon })
        .addTo(map).bindPopup('{{ addslashes($order->restaurant->name) }}');

    let dropMarker = null;
    const bounds = [[restaurant.lat, restaurant.lng]];
    if (drop) {
        dropMarker = L.marker([drop.lat, drop.lng], { icon: dropIcon }).addTo(map).bindPopup('Delivery Address');
        bounds.push([drop.lat, drop.lng]);
    }

    let riderMarker = null;
    let routeLine = null;
    const badge = document.getElementById('mapStatusBadge');
    const etaRow = document.getElementById('etaRow');
    const etaDistanceEl = document.getElementById('etaDistance');
    const etaTimeEl = document.getElementById('etaTime');

    // Straight-line distance (Haversine) — used only as a fallback if the
    // road-routing call below fails (offline, rate-limited, etc).
    function distanceKm(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function showEtaFallback(riderLat, riderLng) {
        const AVG_SPEED_KMH = 20;
        const straightLineKm = distanceKm(riderLat, riderLng, drop.lat, drop.lng);
        const roadKm = straightLineKm * 1.3; // roads are never a straight line
        const minutes = Math.max(1, Math.round((roadKm / AVG_SPEED_KMH) * 60));
        etaDistanceEl.textContent = (roadKm < 1 ? Math.round(roadKm * 1000) + ' m' : roadKm.toFixed(1) + ' km') + ' (est.)';
        etaTimeEl.textContent = minutes <= 1 ? '< 1 min' : '~' + minutes + ' min';
        etaRow.classList.remove('d-none');
    }

    // Real road route + ETA via OSRM's free public routing server — no API key needed.
    // This is a shared demo server, so it can occasionally be slow or rate-limited;
    // if that happens we quietly fall back to a straight-line estimate instead.
    function updateEta(riderLat, riderLng) {
        if (!drop) { etaRow.classList.add('d-none'); return; }

        const url = `https://router.project-osrm.org/route/v1/driving/${riderLng},${riderLat};${drop.lng},${drop.lat}?overview=full&geometries=geojson`;

        fetch(url).then(res => res.json()).then(data => {
            if (data.code !== 'Ok' || !data.routes || !data.routes[0]) {
                showEtaFallback(riderLat, riderLng);
                return;
            }
            const route = data.routes[0];
            const km = route.distance / 1000;
            const minutes = Math.max(1, Math.round(route.duration / 60));

            etaDistanceEl.textContent = km < 1 ? Math.round(route.distance) + ' m' : km.toFixed(1) + ' km';
            etaTimeEl.textContent = minutes <= 1 ? '< 1 min' : '~' + minutes + ' min';
            etaRow.classList.remove('d-none');

            const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);
            if (routeLine) {
                routeLine.setLatLngs(coords);
            } else {
                routeLine = L.polyline(coords, { color: '#e8342a', weight: 4, opacity: 0.7 }).addTo(map);
                map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
            }
        }).catch(() => showEtaFallback(riderLat, riderLng));
    }

    function placeRider(lat, lng, label, showEta) {
        if (!riderMarker) {
            riderMarker = L.marker([lat, lng], { icon: riderIcon }).addTo(map);
        } else {
            riderMarker.setLatLng([lat, lng]);
        }
        if (label) badge.textContent = label;
        if (showEta) {
            updateEta(lat, lng);
        } else {
            etaRow.classList.add('d-none');
            if (routeLine) { map.removeLayer(routeLine); routeLine = null; }
        }
    }

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [30, 30] });
    } else {
        map.setView([restaurant.lat, restaurant.lng], 14);
    }

    const statusUrl = "{{ route('customer.orders.status', $order) }}";
    const currentStatus = @json($order->status);

    function refresh() {
        fetch(statusUrl).then(res => res.json()).then(data => {
            if (data.status !== currentStatus) {
                window.location.reload();
                return;
            }
            if (data.status === 'out_for_delivery' && data.agent_lat && data.agent_lng) {
                placeRider(data.agent_lat, data.agent_lng, 'Live · updated ' + (data.agent_updated || 'just now'), true);
            } else if (data.status === 'out_for_delivery') {
                placeRider(restaurant.lat, restaurant.lng, 'Waiting for rider GPS…', false);
            } else {
                badge.textContent = 'Preparing your order';
            }
        }).catch(() => {});
    }

    refresh();
    setInterval(refresh, 8000);
})();
</script>
@endif

@if(!in_array($order->status, ['delivered', 'cancelled']) && !($showMap ?? false))
<script>
// Live order tracking — poll for status changes every 8s and reload when it changes,
// so the tracker updates without the customer needing to manually refresh.
(function() {
    const currentStatus = @json($order->status);
    const statusUrl = "{{ route('customer.orders.status', $order) }}";

    const poll = setInterval(function() {
        fetch(statusUrl)
            .then(res => res.json())
            .then(data => {
                if (data.status !== currentStatus) {
                    clearInterval(poll);
                    window.location.reload();
                }
            })
            .catch(() => {}); // silently ignore transient network errors
    }, 8000);
})();
</script>
@endif

@if($order->deliveryAgent && !in_array($order->status, ['delivered', 'cancelled']))
<script>
// Chat notifications — lets the customer know a new message arrived even
// while they're on this page (not the chat screen itself). Uses the browser's
// Notification API, which only works while a tab of the site is open — this
// is not the same as a true "push notification" that reaches a closed browser.
(function() {
    let lastUnread = {{ $unreadChatCount ?? 0 }};
    const unreadUrl = "{{ route('customer.chat.unread', $order) }}";
    const badge = document.getElementById('chatUnreadBadge');

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function checkUnread() {
        fetch(unreadUrl).then(res => res.json()).then(data => {
            const unread = data.unread || 0;

            if (badge) {
                badge.textContent = unread;
                badge.classList.toggle('d-none', unread === 0);
            }

            if (unread > lastUnread && 'Notification' in window && Notification.permission === 'granted') {
                new Notification('New message from your delivery partner', {
                    body: 'You have ' + unread + ' unread message' + (unread > 1 ? 's' : '') + ' — tap to reply.',
                    icon: '/pwa-icons/icon-192.png',
                });
            }

            lastUnread = unread;
        }).catch(() => {});
    }

    setInterval(checkUnread, 6000);
})();
</script>
@endif
@endsection
