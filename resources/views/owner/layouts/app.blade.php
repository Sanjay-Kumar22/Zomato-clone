<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Owner Panel') — Zomato Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
    * { box-sizing: border-box; }
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }

    /* Sidebar */
    .sidebar {
        width:250px; height:100vh; overflow-y:auto; scrollbar-width:thin; background: #fff;
        border-right: 1px solid #eee; position: fixed;
        top: 0; left: 0; z-index: 100;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    }
    .sidebar-brand {
        padding: 22px 24px; border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; gap: 12px;
    }
    .brand-icon {
        width: 40px; height: 40px; background: linear-gradient(135deg, #e8342a, #ff6b6b);
        border-radius: 10px; display: flex; align-items: center;
        justify-content: center; font-size: 20px;
    }
    .brand-text { font-size: 16px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
    .brand-text small { font-size: 11px; color: #999; font-weight: 400; display: block; }
    .nav-label {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.1em; color: #bbb; padding: 20px 24px 6px;
    }
    .sidebar .nav-link {
        padding: 11px 24px; color: #666; font-size: 13.5px; font-weight: 500;
        display: flex; align-items: center; gap: 12px;
        transition: all 0.2s; border-left: 3px solid transparent;
        text-decoration: none; margin: 1px 0;
    }
    .sidebar .nav-link:hover { background: #fff4f4; color: #e8342a; border-left-color: #e8342a; }
    .sidebar .nav-link.active { background: #fff4f4; color: #e8342a; border-left-color: #e8342a; font-weight: 600; }
    .sidebar .nav-link i { font-size: 17px; width: 22px; }

    /* Topbar */
    .topbar {
        margin-left: 250px; height: 65px; background: #fff;
        border-bottom: 1px solid #f0f0f0; display: flex;
        align-items: center; justify-content: space-between;
        padding: 0 28px; position: sticky; top: 0; z-index: 99;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .topbar .page-title { font-size: 18px; font-weight: 600; color: #1a1a2e; }
    .topbar-search { width: 320px; }
    .topbar-search input {
        border-radius: 20px; border: 1.5px solid #eee; font-size: 13px;
        padding: 7px 14px 7px 36px; background: #f8f9fa;
    }
    .topbar-search input:focus { border-color: #e8342a; background: #fff; box-shadow: none; }
    .topbar-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #999; font-size: 13px; }
    .user-pill {
        display: flex; align-items: center; gap: 10px;
        background: #f8f9fa; border-radius: 50px;
        padding: 6px 14px 6px 8px; border: 1px solid #eee;
    }
    .user-avatar {
        width: 32px; height: 32px; background: linear-gradient(135deg, #e8342a, #ff6b6b);
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: white; font-size: 13px; font-weight: 600;
    }
    .user-name { font-size: 13px; font-weight: 500; color: #333; }
    .role-badge {
        font-size: 10px; font-weight: 700; padding: 3px 8px;
        border-radius: 20px; background: #fff3e0; color: #e65100;
        text-transform: uppercase; letter-spacing: 0.05em;
    }

    /* Main */
    .main-content { margin-left: 250px; padding: 28px; }
    .sidebar-toggle-btn { display: none; background: none; border: none; font-size: 22px; color: #241C15; padding: 4px 8px; }
    .sidebar-backdrop { display: none; }
        .sidebar::-webkit-scrollbar { width:6px; }
        .sidebar::-webkit-scrollbar-thumb { background:#e0e0e0; border-radius:3px; }


    /* Below this width the fixed sidebar would eat most of the screen, so
       it slides off-canvas and opens via a hamburger button instead. */
    @media (max-width: 991px) {
        .sidebar { transform: translateX(-100%); transition: transform .25s ease; z-index: 1050; }
        .sidebar.show { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.25); }
        .main-content, .topbar { margin-left: 0 !important; }
        .sidebar-toggle-btn { display: inline-flex; align-items: center; }
        .sidebar-backdrop.show {
            display: block; position: fixed; inset: 0; background: rgba(20,15,10,0.45); z-index: 1040;
        }
    }
    @media (max-width: 575px) {
        .main-content { padding: 16px; }
        .topbar { padding: 0 14px; flex-wrap: wrap; height: auto; min-height: 60px; row-gap: 8px; padding-top: 10px; padding-bottom: 10px; }
        .stat-card { padding: 16px; }
        .stat-card .stat-number { font-size: 22px; }
    }

    /* Stat cards */
    .stat-card {
        border-radius: 16px; padding: 24px; color: white;
        border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon { font-size: 36px; opacity: 0.7; }
    .stat-number { font-size: 32px; font-weight: 700; line-height: 1; margin: 8px 0 4px; }
    .stat-label { font-size: 13px; opacity: 0.9; font-weight: 500; }

    /* Cards */
    .card {
        border: none; border-radius: 16px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.07);
    }
    .card-header {
        background: #fff; border-bottom: 1px solid #f5f5f5;
        border-radius: 16px 16px 0 0 !important;
        padding: 18px 24px; font-weight: 600; font-size: 15px; color: #1a1a2e;
    }

    /* Table */
    .table th {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: #999; border-bottom: 1px solid #f0f0f0;
        padding: 12px 16px; background: #fafafa;
    }
    .table td { font-size: 14px; vertical-align: middle; color: #444; padding: 14px 16px; border-bottom: 1px solid #f8f8f8; }
    .table tbody tr:hover { background: #fffafa; }
    .table tbody tr:last-child td { border-bottom: none; }

    /* Badges */
    .badge-approved   { background: #e8f5e9; color: #2e7d32; font-weight: 600; }
    .badge-pending    { background: #fff8e1; color: #f57f17; font-weight: 600; }
    .badge-open       { background: #e3f2fd; color: #1565c0; font-weight: 600; }
    .badge-closed     { background: #fce4ec; color: #880e4f; font-weight: 600; }
    .badge-veg        { background: #e8f5e9; color: #2e7d32; font-weight: 600; }
    .badge-nonveg     { background: #fce4ec; color: #880e4f; font-weight: 600; }
    .badge-available  { background: #e3f2fd; color: #1565c0; font-weight: 600; }
    .badge-unavailable{ background: #f5f5f5; color: #999; font-weight: 600; }

    /* Alert */
    .alert { border: none; border-radius: 12px; font-size: 14px; }
    .alert-success { background: #e8f5e9; color: #2e7d32; }

    body { background: #f4f6fb; color: #252a35; font-family: 'Segoe UI', system-ui, sans-serif; }
    .sidebar { width: 264px; background: #191b25; border-right: 0; box-shadow: 8px 0 28px rgba(20, 22, 32, .12); }
    .sidebar-brand { border-bottom-color: rgba(255,255,255,.09); }
    .brand-text { color: #fff; }
    .brand-text small { color: #a4a7b3; }
    .nav-label { color: #858997; }
    .sidebar .nav-link { color: #c4c6cf; border-left-color: transparent; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
        color: #fff; background: rgba(232, 52, 42, .17); border-left-color: #f06b60;
    }
    .topbar { margin-left: 264px; height: 72px; border-bottom-color: #eceef3; box-shadow: 0 3px 16px rgba(23, 28, 40, .045); }
    .topbar .page-title { color: #202431; font-weight: 700; letter-spacing: -.02em; }
    .main-content { margin-left: 264px; padding: 32px; }
    .card { border: 1px solid #eceef3; border-radius: 18px; box-shadow: 0 8px 24px rgba(32, 36, 49, .055); }
    .card-header { border-bottom-color: #f0f1f5; color: #252a35; }
    .table th { background: #f8f9fc; color: #858b99; }
    .table td { color: #454b59; }
    .table tbody tr:hover { background: #fff8f7; }
    .form-control, .form-select { border-color: #e5e7ee; border-radius: 10px; }
    .form-control:focus, .form-select:focus { border-color: #ee8b81; box-shadow: 0 0 0 3px rgba(232, 52, 42, .1); }
    .btn { font-weight: 600; transition: transform .18s ease, box-shadow .18s ease; }
    .btn:hover { transform: translateY(-1px); }
    .alert { border-radius: 14px; }
    @media (max-width: 575px) { .main-content { padding: 18px; } }
</style>
@stack('styles')
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
    <div class="brand-icon">🍔</div>
    <div>
        <div class="brand-text">
            ZomatoClone
            <small>Owner Panel</small>
        </div>
    </div>
</div>
    
    <div class="nav-label">Main</div>
    <a href="{{ route('owner.dashboard') }}"
        class="nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <div class="nav-label">My Restaurant</div>
    <a href="{{ route('owner.restaurants.index') }}"
        class="nav-link {{ request()->routeIs('owner.restaurants.*') ? 'active' : '' }}">
        <i class="bi bi-shop"></i> My Restaurants
    </a>
    <a href="{{ route('owner.orders.index') }}"
        class="nav-link {{ request()->routeIs('owner.orders.*') ? 'active' : '' }}">
        <i class="bi bi-bag-check"></i> Orders
    </a>
    <a href="{{ route('owner.reviews.index') }}"
        class="nav-link {{ request()->routeIs('owner.reviews.*') ? 'active' : '' }}">
        <i class="bi bi-star"></i> Reviews
    </a>
    <a href="{{ route('owner.coupons.index') }}"
        class="nav-link {{ request()->routeIs('owner.coupons.*') ? 'active' : '' }}">
        <i class="bi bi-ticket-perforated"></i> Coupons
    </a>
    <a href="{{ route('owner.disputes.index') }}"
        class="nav-link {{ request()->routeIs('owner.disputes.*') ? 'active' : '' }}">
        <i class="bi bi-exclamation-octagon"></i> Disputes
    </a>
    <a href="{{ route('owner.analytics') }}"
        class="nav-link {{ request()->routeIs('owner.analytics') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line"></i> Analytics
    </a>
    <a href="{{ route('owner.settlements.index') }}"
        class="nav-link {{ request()->routeIs('owner.settlements.*') ? 'active' : '' }}">
        <i class="bi bi-cash-coin"></i> Payouts
    </a>
    <div class="nav-label">Account</div>
    <a href="{{ route('owner.profile.edit') }}"
        class="nav-link {{ request()->routeIs('owner.profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="nav-link border-0 w-100 text-start bg-transparent">
            <i class="bi bi-box-arrow-left"></i> Logout
        </button>
    </form>
</div>

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <form action="{{ route('owner.search') }}" method="GET" class="topbar-search position-relative d-none d-md-block">
            <i class="bi bi-search"></i>
            <input type="text" name="q" class="form-control form-control-sm" autocomplete="off"
                placeholder="Search orders, menu items, restaurants..." value="{{ request()->routeIs('owner.search') ? request('q') : '' }}">
        </form>
        <a href="{{ route('owner.search') }}" class="btn btn-light btn-sm d-md-none" title="Search">
            <i class="bi bi-search"></i>
        </a>
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                @php $ownerUnread = auth()->user()->unreadNotifications()->count(); @endphp
                @if($ownerUnread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px">
                        {{ $ownerUnread }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="width:320px;max-height:400px;overflow-y:auto">
                <li class="d-flex justify-content-between align-items-center px-2 pb-2">
                    <span class="fw-700" style="font-size:14px">Notifications</span>
                    @if($ownerUnread > 0)
                    <form action="{{ route('owner.notifications.readAll') }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-link btn-sm p-0" style="font-size:12px">Mark all read</button>
                    </form>
                    @endif
                </li>
                @forelse(auth()->user()->notifications()->latest()->take(6)->get() as $n)
                <li>
                    <a href="{{ route('owner.orders.index') }}"
                        class="dropdown-item rounded-3 py-2 {{ $n->read_at ? '' : 'bg-light' }}" style="white-space:normal;font-size:12.5px">
                        <div class="fw-600">New Order — {{ $n->data['customer'] ?? 'Customer' }}</div>
                        <div class="text-muted">{{ $n->data['message'] ?? '' }}</div>
                        <div class="text-muted" style="font-size:10px">{{ $n->created_at->diffForHumans() }}</div>
                    </a>
                </li>
                @empty
                <li class="text-center text-muted py-3" style="font-size:13px">No notifications yet</li>
                @endforelse
                <li><hr class="dropdown-divider"></li>
                <li><a href="{{ route('owner.notifications.index') }}" class="dropdown-item text-center text-danger fw-600" style="font-size:13px">View All</a></li>
            </ul>
        </div>
        <a href="{{ route('owner.profile.edit') }}" class="user-pill text-decoration-none">
            @if(auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                    class="user-avatar" style="object-fit:cover">
            @else
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="role-badge">Owner</span>
        </a>
    </div>
</div>

<div class="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" data-auto-dismiss>
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" data-auto-dismiss>
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-dismiss flash messages after a few seconds so they don't sit
    // around forever, while still leaving the manual close button usable.
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(el)?.close();
        }, 5000);
    });

    // Off-canvas sidebar toggle for mobile/tablet widths
    (function () {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (!sidebar || !backdrop || !toggleBtn) return;

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
        backdrop.addEventListener('click', closeSidebar);
        // Closing on nav-link tap keeps mobile navigation feeling snappy
        sidebar.querySelectorAll('a.nav-link').forEach(a => a.addEventListener('click', closeSidebar));
    })();
</script>
@stack('scripts')
</body>
</html>