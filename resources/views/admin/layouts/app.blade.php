<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — Zomato Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { width:250px; height:100vh; overflow-y:auto; scrollbar-width:thin; background:#fff; border-right:1px solid #e3e6f0; position:fixed; top:0; left:0; z-index:100; box-shadow:2px 0 5px rgba(0,0,0,0.05); }
        .sidebar-brand { padding:20px 24px; border-bottom:1px solid #e3e6f0; }
        .sidebar-brand span.logo { font-size:20px; font-weight:700; color:#e8342a; }
        .sidebar-brand small { font-size:11px; color:#999; display:block; }
        .nav-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#aaa; padding:20px 24px 6px; }
        .sidebar .nav-link { padding:10px 24px; color:#555; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; transition:all 0.2s; border-left:3px solid transparent; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background:#fff4f4; color:#e8342a; border-left:3px solid #e8342a; }
        .sidebar .nav-link i { font-size:16px; width:20px; }
        .topbar { margin-left:250px; height:60px; background:#fff; border-bottom:1px solid #e3e6f0; display:flex; align-items:center; justify-content:space-between; padding:0 24px; position:sticky; top:0; z-index:99; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .topbar .page-title { font-size:16px; font-weight:600; color:#333; }
        .main-content { margin-left:250px; padding:24px; }
        .card { border:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
        .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:12px 12px 0 0 !important; padding:16px 20px; font-weight:600; font-size:15px; }
        .stat-card { border-radius:12px; padding:20px; color:white; border:none; }
        .stat-card .stat-icon { font-size:32px; opacity:0.8; }
        .stat-card .stat-number { font-size:28px; font-weight:700; }
        .stat-card .stat-label { font-size:13px; opacity:0.85; }
        .table th { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#888; border-bottom:2px solid #f0f0f0; }
        .table td { font-size:14px; vertical-align:middle; color:#444; }
        .badge-approved { background:#e8f5e9; color:#2e7d32; }
        .badge-pending  { background:#fff3e0; color:#e65100; }
        .badge-open     { background:#e3f2fd; color:#1565c0; }
        .badge-closed   { background:#fce4ec; color:#880e4f; }
        .sidebar-toggle-btn { display:none; background:none; border:none; font-size:22px; color:#333; padding:4px 8px; }
        .sidebar-backdrop { display:none; }
        .sidebar::-webkit-scrollbar { width:6px; }
        .sidebar::-webkit-scrollbar-thumb { background:#e0e0e0; border-radius:3px; }


        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s ease; z-index:1050; }
            .sidebar.show { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.25); }
            .main-content, .topbar { margin-left: 0 !important; }
            .sidebar-toggle-btn { display:inline-flex; align-items:center; }
            .sidebar-backdrop.show {
                display:block; position:fixed; inset:0; background:rgba(20,15,10,0.45); z-index:1040;
            }
        }
        @media (max-width: 575px) {
            .main-content { padding:16px; }
            .topbar { padding:0 14px; flex-wrap: wrap; height: auto; min-height: 60px; row-gap: 8px; padding-top: 10px; padding-bottom: 10px; }
            .stat-card { padding:16px; }
            .stat-card .stat-number { font-size:22px; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="logo">🍔 ZomatoClone</span>
        <small>Admin Panel</small>
    </div>
    <div class="nav-label">Main</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <div class="nav-label">Management</div>
    <a href="{{ route('admin.restaurants.index') }}" class="nav-link {{ request()->routeIs('admin.restaurants.*') ? 'active' : '' }}">
        <i class="bi bi-shop"></i> Restaurants
    </a>
    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Users</a>
    <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><i class="bi bi-ticket-perforated"></i> Coupons</a>
    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="bi bi-bag-check"></i> Orders</a>
    <a href="{{ route('admin.disputes.index') }}" class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}"><i class="bi bi-exclamation-octagon"></i> Disputes</a>
    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifications
        @php $adminSidebarUnread = auth()->user()->unreadNotifications()->count(); @endphp
        @if($adminSidebarUnread > 0)
            <span class="badge bg-danger rounded-pill ms-1">{{ $adminSidebarUnread }}</span>
        @endif
    </a>
    <a href="{{ route('admin.analytics') }}" class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> Analytics</a>
    <a href="#" class="nav-link"><i class="bi bi-grid"></i> Categories</a>
    <div class="nav-label">Account</div>
    <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="nav-link border-0 w-100 text-start bg-transparent">
            <i class="bi bi-box-arrow-left"></i> Logout
        </button>
    </form>
</div>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                @php $adminUnread = auth()->user()->unreadNotifications()->count(); @endphp
                @if($adminUnread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px">
                        {{ $adminUnread }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="width:320px;max-height:400px;overflow-y:auto">
                <li class="d-flex justify-content-between align-items-center px-2 pb-2">
                    <span class="fw-700" style="font-size:14px">Notifications</span>
                    @if($adminUnread > 0)
                    <form action="{{ route('admin.notifications.readAll') }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-link btn-sm p-0" style="font-size:12px">Mark all read</button>
                    </form>
                    @endif
                </li>
                @forelse(auth()->user()->notifications()->latest()->take(6)->get() as $n)
                <li>
                    <a href="{{ route('admin.orders.index') }}"
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
                <li><a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-center text-danger fw-600" style="font-size:13px">View All</a></li>
            </ul>
        </div>
        <a href="{{ route('admin.profile.edit') }}" class="text-muted d-none d-sm-inline text-decoration-none" style="font-size:13px">
            @if(auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                    style="width:22px;height:22px;border-radius:50%;object-fit:cover;margin-right:4px;vertical-align:-5px">
            @else
                <i class="bi bi-person-circle"></i>
            @endif
            {{ auth()->user()->name }}
        </a>
        <span class="badge bg-danger">Admin</span>
    </div>
</div>

<!-- Main Content -->
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
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(el)?.close();
        }, 5000);
    });

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
        sidebar.querySelectorAll('a.nav-link').forEach(a => a.addEventListener('click', closeSidebar));
    })();
</script>
</body>
</html>