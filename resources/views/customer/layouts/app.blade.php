<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Zomato Clone')</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#e8342a">
    <link rel="apple-touch-icon" href="/pwa-icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ZomatoClone">
    <link rel="icon" href="/pwa-icons/icon-192.png">
    <script>
        // Applied before CSS renders, so there's no flash of light mode
        // for a person who has dark mode saved.
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }

        /* Dark mode — a full hand-painted dark theme would mean touching
           every inline color across every page, so instead we invert the
           whole page's colors and invert images/maps/icons a second time
           to cancel it back out. Works everywhere, no per-page changes. */
        html.dark-mode {
            filter: invert(1) hue-rotate(180deg);
            background: #fff;
        }
        html.dark-mode img,
        html.dark-mode .leaflet-container,
        html.dark-mode video,
        html.dark-mode iframe {
            filter: invert(1) hue-rotate(180deg);
        }
        html.dark-mode ::-webkit-scrollbar { background: #1a1a1a; }

        .theme-toggle-btn {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #eee; background: #f8f8f8; cursor: pointer;
        }

        /* Bootstrap's default .container caps out around 1320px, which
           leaves huge empty margins on wider screens (1600px+, ultrawide).
           Zomato/Swiggy-style layouts use much more of the viewport, so we
           widen it here — still capped so it doesn't stretch absurdly thin
           content on 4K monitors, and still responsive below that via
           Bootstrap's normal breakpoints (this only kicks in above 1400px). */
        @media (min-width: 1400px) {
            .container { max-width: 1600px; }
        }
        .navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 12px 0;
        }
        .navbar-brand { font-size: 22px; font-weight: 800; color: #e8342a !important; }
        .navbar-brand span { color: #333; }
        .cart-btn {
            position: relative; background: #e8342a;
            color: white; border: none; border-radius: 25px;
            padding: 8px 18px; font-size: 14px; font-weight: 500;
            text-decoration: none; display: flex; align-items: center; gap: 6px;
        }
        .cart-btn:hover { background: #c62828; color: white; }
        .cart-count {
            background: white; color: #e8342a;
            border-radius: 50%; width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
        }
        .main { padding: 28px 0; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }

        /* ─── Location switcher ─── */
        .location-btn {
            display: flex; align-items: center; gap: 6px;
            background: #FFF1EE; border: 1px solid #FBD9D3; color: #C62828;
            border-radius: 20px; padding: 7px 14px; font-size: 13px; font-weight: 700;
            max-width: 220px; cursor: pointer;
        }
        .location-btn i.bi-geo-alt-fill { font-size: 14px; }
        .location-btn .loc-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .location-btn:hover { background: #FDE3DC; }
        .saved-address-pick {
            display: flex; align-items: center; gap: 10px; width: 100%; text-align: left;
            border: 1.5px solid #EEE3D6; border-radius: 12px; padding: 10px 14px;
            background: white; cursor: pointer; margin-bottom: 8px; font-size: 13px;
        }
        .saved-address-pick:hover { border-color: #E8342A; background: #FFF8F6; }
        #locationMap { height: 210px; border-radius: 12px; z-index: 1; }
        .loc-search-item {
            display: block; width: 100%; text-align: left; border: none; background: white;
            padding: 9px 12px; font-size: 12.5px; color: #241C15; border-bottom: 1px solid #F3ECE3;
        }
        .loc-search-item:hover { background: #FFF1EE; }
    </style>
    @yield('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
            🍔 Zomato<span>Clone</span>
        </a>

        <button type="button" class="location-btn ms-lg-2 me-auto" data-bs-toggle="modal" data-bs-target="#locationModal">
            <i class="bi bi-geo-alt-fill"></i>
            <span class="loc-text">{{ session('customer_location_label') ?? 'Select delivery location' }}</span>
            <i class="bi bi-chevron-down" style="font-size:10px"></i>
        </button>

        <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap">
            <button type="button" id="installAppBtn" class="btn btn-outline-danger btn-sm d-none">
                <i class="bi bi-download me-1"></i>Install App
            </button>
            <button type="button" id="themeToggleBtn" class="theme-toggle-btn" title="Toggle dark mode">
                <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
            </button>
            @auth
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                            style="width:22px;height:22px;border-radius:50%;object-fit:cover;margin-right:6px;vertical-align:-5px">
                    @else
                        <i class="bi bi-person-circle me-1"></i>
                    @endif
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('customer.profile.edit') }}">
                        <i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.favorites.index') }}">
                        <i class="bi bi-heart me-2"></i>My Favorites</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.wallet.index') }}">
                        <i class="bi bi-wallet2 me-2"></i>My Wallet
                        @if(auth()->user()->wallet_balance > 0)
                            <span class="badge bg-success rounded-pill ms-1" style="font-size:10px">₹{{ number_format(auth()->user()->wallet_balance, 0) }}</span>
                        @endif
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.referral.index') }}">
                        <i class="bi bi-gift me-2"></i>Refer & Earn</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.addresses.index') }}">
                        <i class="bi bi-geo-alt me-2"></i>My Addresses</a></li>
                    <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}">
                        <i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-left me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="width:320px;max-height:400px;overflow-y:auto">
                    <li class="d-flex justify-content-between align-items-center px-2 pb-2">
                        <span class="fw-700" style="font-size:14px">Notifications</span>
                        @if($unreadCount > 0)
                        <form action="{{ route('customer.notifications.readAll') }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-link btn-sm p-0" style="font-size:12px">Mark all read</button>
                        </form>
                        @endif
                    </li>
                    @forelse(auth()->user()->notifications()->latest()->take(6)->get() as $n)
                    <li>
                        <a href="{{ route('customer.orders.show', $n->data['order_id']) }}"
                            class="dropdown-item rounded-3 py-2 {{ $n->read_at ? '' : 'bg-light' }}" style="white-space:normal;font-size:12.5px">
                            <div class="fw-600">{{ $n->data['restaurant'] ?? 'Order Update' }}</div>
                            <div class="text-muted">{{ $n->data['message'] ?? '' }}</div>
                            <div class="text-muted" style="font-size:10px">{{ $n->created_at->diffForHumans() }}</div>
                        </a>
                    </li>
                    @empty
                    <li class="text-center text-muted py-3" style="font-size:13px">No notifications yet</li>
                    @endforelse
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('customer.notifications.index') }}" class="dropdown-item text-center text-danger fw-600" style="font-size:13px">View All</a></li>
                </ul>
            </div>

            <a href="{{ route('customer.favorites.index') }}" class="btn btn-outline-secondary btn-sm d-none d-md-flex align-items-center gap-1">
                <i class="bi bi-heart"></i><span>Favorites</span>
            </a>

            <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm d-none d-md-flex align-items-center gap-1">
                <i class="bi bi-bag-check"></i><span>Orders</span>
            </a>

            <a href="{{ route('customer.cart.index') }}" class="cart-btn">
                <i class="bi bi-bag"></i>
                <span class="d-none d-md-inline">Cart</span>
                @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                @if($cartCount > 0)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>
            @else
            <a href="{{ route('login') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-600">
                Log in
            </a>
            <a href="{{ route('register') }}" class="btn btn-danger btn-sm rounded-pill px-3 fw-600">
                Sign up
            </a>
            @endauth
        </div>
    </div>
</nav>

<div class="main">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" data-auto-dismiss>
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" data-auto-dismiss>
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-3 mb-4">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif
        @yield('content')
    </div>
</div>

<footer style="background:#241C15;color:#D8CCBF;margin-top:60px;padding:48px 0 0;">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span style="font-size:24px">🍔</span>
                    <span style="font-family:'Fraunces',serif;font-size:20px;font-weight:600;color:white">ZomatoClone</span>
                </div>
                <p style="font-size:13px;color:#B0A296;line-height:1.7">
                    Order food from the best restaurants near you — fast delivery, easy tracking, and great taste, every time.
                </p>
            </div>
            <div class="col-md-2">
                <div style="font-size:13px;font-weight:700;color:white;margin-bottom:14px;letter-spacing:0.05em;text-transform:uppercase">Company</div>
                <div class="d-flex flex-column gap-2" style="font-size:13px">
                    <a href="{{ route('customer.dashboard') }}" class="text-decoration-none" style="color:#B0A296">About Us</a>
                    <a href="#" class="text-decoration-none" style="color:#B0A296">Careers</a>
                    <a href="#" class="text-decoration-none" style="color:#B0A296">Blog</a>
                </div>
            </div>
            <div class="col-md-2">
                <div style="font-size:13px;font-weight:700;color:white;margin-bottom:14px;letter-spacing:0.05em;text-transform:uppercase">For You</div>
                <div class="d-flex flex-column gap-2" style="font-size:13px">
                    <a href="{{ auth()->check() ? route('customer.orders.index') : route('login') }}" class="text-decoration-none" style="color:#B0A296">My Orders</a>
                    <a href="{{ auth()->check() ? route('customer.favorites.index') : route('login') }}" class="text-decoration-none" style="color:#B0A296">Favorites</a>
                    <a href="{{ auth()->check() ? route('customer.addresses.index') : route('login') }}" class="text-decoration-none" style="color:#B0A296">My Addresses</a>
                </div>
            </div>
            <div class="col-md-4">
                <div style="font-size:13px;font-weight:700;color:white;margin-bottom:14px;letter-spacing:0.05em;text-transform:uppercase">Get In Touch</div>
                <div class="d-flex flex-column gap-2" style="font-size:13px;color:#B0A296">
                    <div><i class="bi bi-envelope me-2"></i>support@zomatoclone.test</div>
                    <div><i class="bi bi-telephone me-2"></i>+91 98765 43210</div>
                    <div><i class="bi bi-geo-alt me-2"></i>Sector 17, Chandigarh, India</div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" style="width:34px;height:34px;border-radius:50%;background:#3A2F26;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none"><i class="bi bi-facebook"></i></a>
                    <a href="#" style="width:34px;height:34px;border-radius:50%;background:#3A2F26;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none"><i class="bi bi-instagram"></i></a>
                    <a href="#" style="width:34px;height:34px;border-radius:50%;background:#3A2F26;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
        <div style="border-top:1px solid #3A2F26;padding:18px 0;text-align:center;font-size:12px;color:#8A7A6D">
            © {{ date('Y') }} ZomatoClone. All rights reserved.
        </div>
    </div>
</footer>

<!-- Location Switcher Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;border:none;overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid #EEE3D6;">
                <h5 class="modal-title fw-700" style="font-size:17px;">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>Change delivery location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px 22px;max-height:80vh;overflow-y:auto;">

                @auth
                    @php $savedAddresses = auth()->user()->addresses()->latest()->get(); @endphp
                    @if($savedAddresses->count())
                        <div class="text-muted fw-700" style="font-size:12px;letter-spacing:.05em;text-transform:uppercase;margin-bottom:10px;">
                            Your saved addresses
                        </div>
                        @foreach($savedAddresses as $addr)
                        <form action="{{ route('customer.location.set') }}" method="POST">
                            @csrf
                            <input type="hidden" name="pincode" value="{{ $addr->pincode }}">
                            <input type="hidden" name="full_address" value="{{ $addr->full_address }}">
                            <input type="hidden" name="lat" value="{{ $addr->lat }}">
                            <input type="hidden" name="lng" value="{{ $addr->lng }}">
                            <input type="hidden" name="label" value="{{ $addr->label }}">
                            <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                            <button type="submit" class="saved-address-pick">
                                <i class="bi {{ $addr->is_default ? 'bi-house-fill text-danger' : 'bi-geo-alt' }}"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-700" style="color:#241C15;">{{ $addr->label }}</div>
                                    <div class="text-muted" style="font-size:12px;">{{ Str::limit($addr->full_address, 60) }} · {{ $addr->pincode }}</div>
                                </div>
                            </button>
                        </form>
                        @endforeach
                        <hr style="border-color:#EEE3D6;">
                    @endif
                @endauth

                <div class="text-muted fw-700" style="font-size:12px;letter-spacing:.05em;text-transform:uppercase;margin-bottom:10px;">
                    Search or pick on map
                </div>

                <div class="position-relative mb-2">
                    <input type="text" id="locSearchBox" class="form-control" placeholder="Search area, street, landmark..."
                        autocomplete="off" style="border-radius:10px;border:1.5px solid #EEE3D6;">
                    <div id="locSearchResults"
                        style="display:none;position:absolute;top:44px;left:0;right:0;background:white;border-radius:10px;
                               box-shadow:0 12px 28px rgba(36,28,21,0.18);z-index:1200;max-height:200px;overflow-y:auto;"></div>
                </div>

                <button type="button" id="useMyLocationBtn" class="btn btn-sm btn-outline-danger mb-2 rounded-pill">
                    <i class="bi bi-crosshair me-1"></i>Use my current location
                </button>

                <div id="locationMap" class="mb-1"></div>
                <div id="geocodeStatus" class="text-muted mb-2" style="font-size:11.5px;min-height:14px;"></div>

                <form id="locationForm" action="{{ route('customer.location.set') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                    <input type="hidden" name="lat" id="locLat" value="{{ session('customer_lat') }}">
                    <input type="hidden" name="lng" id="locLng" value="{{ session('customer_lng') }}">

                    <div class="mb-2">
                        <label class="form-label fw-600" style="font-size:12.5px;">Full address</label>
                        <textarea name="full_address" id="locFullAddress" class="form-control" rows="2"
                            placeholder="Flat / house no, building, street, area..."
                            style="border-radius:10px;border:1.5px solid #EEE3D6;">{{ session('customer_full_address') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:12.5px;">Pincode</label>
                        <input type="text" name="pincode" id="locPincode" class="form-control" maxlength="10" required
                            value="{{ session('customer_pincode') }}" placeholder="e.g. 160017"
                            style="border-radius:10px;border:1.5px solid #EEE3D6;">
                    </div>

                    <button type="submit" class="btn text-white fw-600 w-100" style="background:#E8342A;border-radius:10px;">
                        <i class="bi bi-check-circle me-1"></i>Confirm &amp; use this location
                    </button>
                </form>

                @if(session('customer_pincode'))
                <form action="{{ route('customer.location.clear') }}" method="POST" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                    <button type="submit" class="btn btn-link btn-sm text-muted p-0" style="font-size:12.5px;">
                        <i class="bi bi-x-circle me-1"></i>Clear location filter (show all restaurants)
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    // These reflect the LAST SAVED location (session) — used to reset the
    // form every time the modal is (re)opened, so an unconfirmed pick from
    // a previous open never lingers and looks like it was saved.
    const SESSION_LAT     = {{ session('customer_lat') ? session('customer_lat') : 'null' }};
    const SESSION_LNG     = {{ session('customer_lng') ? session('customer_lng') : 'null' }};
    const SESSION_ADDRESS = @json(session('customer_full_address'));
    const SESSION_PINCODE = @json(session('customer_pincode'));

    const DEFAULT_LAT = SESSION_LAT ?? 30.7333; // Chandigarh — only used when nothing is saved yet
    const DEFAULT_LNG = SESSION_LNG ?? 76.7794;

    let map, marker;
    const modalEl = document.getElementById('locationModal');
    const statusEl = document.getElementById('geocodeStatus');
    const latInput = document.getElementById('locLat');
    const lngInput = document.getElementById('locLng');
    const addressInput = document.getElementById('locFullAddress');
    const pincodeInput = document.getElementById('locPincode');

    // Tracks the last value WE auto-filled (via reverse geocode) — if the
    // person has since typed something different (e.g. their own detailed
    // "1st Floor, F-33, Phase-8..." address), we must not clobber it just
    // because they moved the pin or picked a simplified search result.
    let lastAutoAddress = SESSION_ADDRESS ?? '';
    let lastAutoPincode = SESSION_PINCODE ?? '';

    // Discard any unconfirmed edits from a previous open, every time the
    // modal is about to show — this is what stops a picked-but-not-saved
    // location from silently "sticking".
    modalEl.addEventListener('show.bs.modal', function () {
        latInput.value = SESSION_LAT ?? '';
        lngInput.value = SESSION_LNG ?? '';
        addressInput.value = SESSION_ADDRESS ?? '';
        pincodeInput.value = SESSION_PINCODE ?? '';
        lastAutoAddress = SESSION_ADDRESS ?? '';
        lastAutoPincode = SESSION_PINCODE ?? '';
        statusEl.textContent = '';

        const lat = SESSION_LAT ?? DEFAULT_LAT;
        const lng = SESSION_LNG ?? DEFAULT_LNG;
        if (map && marker) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], SESSION_LAT ? 15 : 12);
        }
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        if (!map) {
            map = L.map('locationMap').setView([DEFAULT_LAT, DEFAULT_LNG], SESSION_LAT ? 15 : 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([DEFAULT_LAT, DEFAULT_LNG], { draggable: true }).addTo(map);
            marker.on('dragend', () => useLatLng(marker.getLatLng().lat, marker.getLatLng().lng));
            map.on('click', (e) => {
                marker.setLatLng(e.latlng);
                useLatLng(e.latlng.lat, e.latlng.lng);
            });
        } else {
            setTimeout(() => map.invalidateSize(), 150);
        }
    });

    function useLatLng(lat, lng) {
        latInput.value = lat;
        lngInput.value = lng;
        reverseGeocode(lat, lng);
    }

    // Turns Nominatim's address breakdown into a short, human-readable line
    // (e.g. "Sector 17, Chandigarh, Chandigarh, 160017") instead of dumping
    // its very long, technical `display_name` (which includes county/state
    // codes, country, sometimes plus-codes) into the address box.
    function buildFriendlyAddress(data) {
        const a = (data && data.address) || {};
        const parts = [
            a.road || a.pedestrian || a.neighbourhood || a.suburb,
            a.suburb && a.suburb !== (a.road || a.pedestrian) ? a.suburb : null,
            a.city || a.town || a.village || a.state_district,
            a.state,
            a.postcode,
        ].filter(Boolean);

        // De-duplicate consecutive repeats (Nominatim sometimes repeats city/suburb)
        const clean = parts.filter((p, i) => p !== parts[i - 1]);
        return clean.length ? clean.join(', ') : (data.display_name || '');
    }

    function reverseGeocode(lat, lng) {
        statusEl.textContent = 'Fetching address…';
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&zoom=18`)
            .then(res => res.json())
            .then(data => {
                const friendly = buildFriendlyAddress(data);

                // Only overwrite the address box if it's empty or still holds
                // OUR last auto-filled value — never stomp on text the person
                // typed themselves (e.g. a detailed floor/plot address that
                // OpenStreetMap wouldn't know about anyway).
                if (!addressInput.value.trim() || addressInput.value === lastAutoAddress) {
                    addressInput.value = friendly;
                    lastAutoAddress = friendly;
                }

                if (data && data.address && data.address.postcode) {
                    if (!pincodeInput.value.trim() || pincodeInput.value === lastAutoPincode) {
                        pincodeInput.value = data.address.postcode;
                        lastAutoPincode = data.address.postcode;
                    }
                    statusEl.textContent = 'Location updated — edit the address above if needed.';
                } else {
                    statusEl.textContent = 'Pin placed, but no pincode found here — please type it in.';
                }
            })
            .catch(() => {
                statusEl.textContent = 'Could not auto-fetch address, please type it manually.';
            });
    }

    document.getElementById('useMyLocationBtn').addEventListener('click', function () {
        if (!navigator.geolocation) {
            statusEl.textContent = 'Geolocation is not supported by this browser.';
            return;
        }
        statusEl.textContent = 'Detecting your current location…';
        navigator.geolocation.getCurrentPosition(function (pos) {
            const { latitude, longitude, accuracy } = pos.coords;
            if (map) {
                marker.setLatLng([latitude, longitude]);
                map.setView([latitude, longitude], 16);
            }
            useLatLng(latitude, longitude);
            // Accuracy is in meters — desktop/WiFi-based location can easily be
            // off by 1-5km since there's no GPS chip; let the person know so
            // they know to nudge the pin rather than assume it's broken.
            if (accuracy && accuracy > 500) {
                statusEl.textContent += ` (accuracy ~${Math.round(accuracy)}m — drag the pin to fine-tune if it's off)`;
            }
        }, function (err) {
            statusEl.textContent = err.code === 1
                ? 'Location permission denied — allow it in your browser, or pick on the map.'
                : 'Could not detect your location — pick it on the map instead.';
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
        });
    });

    // Address search box (forward geocoding via Nominatim).
    // Nominatim is built on OpenStreetMap data, which usually doesn't have
    // building/floor/plot-level entries (e.g. "1st Floor, F-33, Phase-8...").
    // So if the full text finds nothing, we progressively drop the most
    // specific leading part (floor, plot no.) and retry with what's left,
    // until we land on an area/sector/city Nominatim actually has.
    let searchTimer;
    let searchToken = 0;
    let lastSearchQuery = '';
    const searchBox = document.getElementById('locSearchBox');
    const searchResults = document.getElementById('locSearchResults');

    function nominatimSearch(query) {
        return fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&countrycodes=in`)
            .then(res => res.json());
    }

    async function progressiveSearch(rawQuery) {
        // Split on commas — typical Indian address order goes
        // [floor/unit, plot/building, area, sector, city, state] i.e.
        // most-specific-first, so dropping from the FRONT walks toward
        // the general area, which is what OSM is most likely to have.
        let segments = rawQuery.split(',').map(s => s.trim()).filter(Boolean);
        if (!segments.length) return { list: [], usedQuery: rawQuery, simplified: false };

        let list = await nominatimSearch(segments.join(', '));
        let simplified = false;

        while (!list.length && segments.length > 1) {
            segments.shift();
            simplified = true;
            list = await nominatimSearch(segments.join(', '));
        }

        return { list, usedQuery: segments.join(', '), simplified };
    }

    searchBox.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        lastSearchQuery = q;
        if (q.length < 3) { searchResults.style.display = 'none'; return; }

        const myToken = ++searchToken;
        searchResults.innerHTML = '<div class="p-2 text-muted" style="font-size:12px">Searching…</div>';
        searchResults.style.display = 'block';

        searchTimer = setTimeout(() => {
            progressiveSearch(q)
                .then(({ list, usedQuery, simplified }) => {
                    if (myToken !== searchToken) return; // a newer search superseded this one

                    if (!list.length) {
                        searchResults.innerHTML = `<div class="p-2 text-muted" style="font-size:12px">
                            No matches found. This app searches OpenStreetMap, which usually doesn't have
                            floor/plot-level addresses — try just the area or sector name, or drop the pin
                            on the map manually and keep your full address typed below.</div>`;
                        return;
                    }

                    let html = '';
                    if (simplified) {
                        html += `<div class="p-2" style="font-size:11px;color:#B5722C;background:#FBEEDD;">
                            Exact address not found — showing matches for "<strong>${escapeHtml(usedQuery)}</strong>".
                            Pick the closest one, then adjust the pin on the map if needed.</div>`;
                    }
                    html += list.map(item => {
                        const primary = item.name || item.display_name.split(',')[0];
                        return `<button type="button" class="loc-search-item" data-lat="${item.lat}" data-lng="${item.lon}">
                            <div style="font-weight:700;color:#241C15;">${escapeHtml(primary)}</div>
                            <div style="color:#A9998A;font-size:11px;">${escapeHtml(item.display_name)}</div>
                        </button>`;
                    }).join('');
                    searchResults.innerHTML = html;
                })
                .catch(() => {
                    if (myToken !== searchToken) return;
                    searchResults.innerHTML = '<div class="p-2 text-muted" style="font-size:12px">Search failed — check your connection and try again.</div>';
                });
        }, 400);
    });

    searchResults.addEventListener('click', function (e) {
        const btn = e.target.closest('.loc-search-item');
        if (!btn) return;
        const lat = parseFloat(btn.dataset.lat), lng = parseFloat(btn.dataset.lng);
        if (map) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
        }

        // If the address box is still empty, seed it with what the person
        // actually typed (their full "1st Floor, F-33, Phase-8..." text) —
        // NOT the generic area name from the search result — since their
        // typed text is the real, precise address and OSM only matched the
        // general sector. The pin marks the sector; the text keeps the detail.
        if (!addressInput.value.trim() && lastSearchQuery) {
            addressInput.value = lastSearchQuery;
            // Deliberately NOT updating lastAutoAddress here — leaving it as
            // whatever it was means the guard in reverseGeocode() sees this
            // box value as "not ours", so it won't get auto-overwritten next.
        }

        useLatLng(lat, lng);
        searchResults.style.display = 'none';
        searchBox.value = '';
    });

    document.addEventListener('click', function (e) {
        if (!searchResults.contains(e.target) && e.target !== searchBox) {
            searchResults.style.display = 'none';
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }
})();

// Auto-dismiss flash messages after a few seconds — same treatment used
// across the owner/delivery/admin panels for a consistent feel.
document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
    setTimeout(function () {
        bootstrap.Alert.getOrCreateInstance(el)?.close();
    }, 5000);
});

// Dark mode toggle
(function () {
    const btn = document.getElementById('themeToggleBtn');
    const icon = document.getElementById('themeToggleIcon');

    function syncIcon() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
    }

    syncIcon();

    btn?.addEventListener('click', function () {
        const isDark = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        syncIcon();
    });
})();

// PWA — register the service worker so the site can be installed and
// shows a friendly page instead of a browser error when offline.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function (err) {
            console.warn('Service worker registration failed:', err);
        });
    });
}

// Show an explicit "Install App" button when the browser says the site
// is installable — most people don't notice the small address-bar icon.
let deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredInstallPrompt = e;
    document.getElementById('installAppBtn')?.classList.remove('d-none');
});

document.getElementById('installAppBtn')?.addEventListener('click', async function () {
    if (!deferredInstallPrompt) return;
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    this.classList.add('d-none');
});

window.addEventListener('appinstalled', function () {
    document.getElementById('installAppBtn')?.classList.add('d-none');
});
</script>
@yield('scripts')
</body>
</html>
