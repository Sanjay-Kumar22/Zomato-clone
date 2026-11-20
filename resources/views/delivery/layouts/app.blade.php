<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Delivery Panel') — Zomato Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
    * { box-sizing: border-box; }
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }

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
        width: 40px; height: 40px; background: linear-gradient(135deg, #1565c0, #64b5f6);
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
    .sidebar .nav-link:hover { background: #f0f7ff; color: #1565c0; border-left-color: #1565c0; }
    .sidebar .nav-link.active { background: #f0f7ff; color: #1565c0; border-left-color: #1565c0; font-weight: 600; }
    .sidebar .nav-link i { font-size: 17px; width: 22px; }

    .topbar {
        margin-left: 250px; height: 65px; background: #fff;
        border-bottom: 1px solid #f0f0f0; display: flex;
        align-items: center; justify-content: space-between;
        padding: 0 28px; position: sticky; top: 0; z-index: 99;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .topbar .page-title { font-size: 18px; font-weight: 600; color: #1a1a2e; }
    .user-pill {
        display: flex; align-items: center; gap: 10px;
        background: #f8f9fa; border-radius: 50px;
        padding: 6px 14px 6px 8px; border: 1px solid #eee;
    }
    .user-avatar {
        width: 32px; height: 32px; background: linear-gradient(135deg, #1565c0, #64b5f6);
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: white; font-size: 13px; font-weight: 600;
    }
    .user-name { font-size: 13px; font-weight: 500; color: #333; }
    .role-badge {
        font-size: 10px; font-weight: 700; padding: 3px 8px;
        border-radius: 20px; background: #e3f2fd; color: #1565c0;
        text-transform: uppercase; letter-spacing: 0.05em;
    }

    .main-content { margin-left: 250px; padding: 28px; }
    .sidebar-toggle-btn { display:none; background:none; border:none; font-size:22px; color:#241C15; padding:4px 8px; }
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
        .stat-number { font-size:22px; }
    }

    .stat-card {
        border-radius: 16px; padding: 24px; color: white;
        border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon { font-size: 36px; opacity: 0.7; }
    .stat-number { font-size: 32px; font-weight: 700; line-height: 1; margin: 8px 0 4px; }
    .stat-label { font-size: 13px; opacity: 0.9; font-weight: 500; }

    .card {
        border: none; border-radius: 16px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.07);
    }
    .card-header {
        background: #fff; border-bottom: 1px solid #f5f5f5;
        border-radius: 16px 16px 0 0 !important;
        padding: 18px 24px; font-weight: 600; font-size: 15px; color: #1a1a2e;
    }

    .table th {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: #999; border-bottom: 1px solid #f0f0f0;
        padding: 12px 16px; background: #fafafa;
    }
    .table td { font-size: 14px; vertical-align: middle; color: #444; padding: 14px 16px; border-bottom: 1px solid #f8f8f8; }
    .table tbody tr:hover { background: #fafdff; }
    .table tbody tr:last-child td { border-bottom: none; }

    .alert { border: none; border-radius: 12px; font-size: 14px; }
    .alert-success { background: #e8f5e9; color: #2e7d32; }
    .alert-danger  { background: #fce4ec; color: #880e4f; }

    body { background: #f4f6fb; color: #252a35; font-family: 'Segoe UI', system-ui, sans-serif; }
    .sidebar { width: 264px; background: #191b25; border-right: 0; box-shadow: 8px 0 28px rgba(20, 22, 32, .12); }
    .sidebar-brand { border-bottom-color: rgba(255,255,255,.09); }
    .brand-text { color: #fff; }
    .brand-text small { color: #a4a7b3; }
    .nav-label { color: #858997; }
    .sidebar .nav-link { color: #c4c6cf; border-left-color: transparent; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
        color: #fff; background: rgba(42, 116, 218, .2); border-left-color: #64a7ff;
    }
    .topbar { margin-left: 264px; height: 72px; border-bottom-color: #eceef3; box-shadow: 0 3px 16px rgba(23, 28, 40, .045); }
    .topbar .page-title { color: #202431; font-weight: 700; letter-spacing: -.02em; }
    .main-content { margin-left: 264px; padding: 32px; }
    .card { border: 1px solid #eceef3; border-radius: 18px; box-shadow: 0 8px 24px rgba(32, 36, 49, .055); }
    .card-header { border-bottom-color: #f0f1f5; color: #252a35; }
    .table th { background: #f8f9fc; color: #858b99; }
    .table td { color: #454b59; }
    .table tbody tr:hover { background: #f7faff; }
    .form-control, .form-select { border-color: #e5e7ee; border-radius: 10px; }
    .form-control:focus, .form-select:focus { border-color: #79aef3; box-shadow: 0 0 0 3px rgba(21, 101, 192, .1); }
    .btn { font-weight: 600; transition: transform .18s ease, box-shadow .18s ease; }
    .btn:hover { transform: translateY(-1px); }
    .alert { border-radius: 14px; }
    @media (max-width: 575px) { .main-content { padding: 18px; } }
</style>
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🛵</div>
        <div>
            <div class="brand-text">
                ZomatoClone
                <small>Delivery Panel</small>
            </div>
        </div>
    </div>

    <div class="nav-label">Main</div>
    <a href="{{ route('delivery.dashboard') }}"
        class="nav-link {{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('delivery.orders.available') }}"
        class="nav-link {{ request()->routeIs('delivery.orders.available') ? 'active' : '' }}">
        <i class="bi bi-box-seam"></i> Available for Pickup
    </a>
    <a href="{{ route('delivery.orders.history') }}"
        class="nav-link {{ request()->routeIs('delivery.orders.history') ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i> Delivery History
    </a>
    <a href="{{ route('delivery.notifications.index') }}"
        class="nav-link {{ request()->routeIs('delivery.notifications.*') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifications
        @php $deliveryUnread = auth()->user()->unreadNotifications()->count(); @endphp
        @if($deliveryUnread > 0)
            <span class="badge bg-danger rounded-pill ms-1">{{ $deliveryUnread }}</span>
        @endif
    </a>

    <div class="nav-label">Account</div>
    <a href="{{ route('delivery.profile.edit') }}"
        class="nav-link {{ request()->routeIs('delivery.profile.*') ? 'active' : '' }}">
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
        <form action="{{ route('delivery.toggleOnline') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm d-flex align-items-center gap-2"
                style="border-radius:50px;padding:6px 14px;font-size:12.5px;font-weight:600;
                       border:1px solid {{ auth()->user()->is_online ? '#c8e6c9' : '#eee' }};
                       background:{{ auth()->user()->is_online ? '#e8f5e9' : '#f8f9fa' }};
                       color:{{ auth()->user()->is_online ? '#2e7d32' : '#888' }};">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ auth()->user()->is_online ? '#2e7d32' : '#bbb' }};display:inline-block;"></span>
                {{ auth()->user()->is_online ? "You're Online" : "You're Offline" }}
            </button>
        </form>
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                @if($deliveryUnread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px">
                        {{ $deliveryUnread }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="width:320px;max-height:400px;overflow-y:auto">
                <li class="d-flex justify-content-between align-items-center px-2 pb-2">
                    <span class="fw-700" style="font-size:14px">Notifications</span>
                    @if($deliveryUnread > 0)
                    <form action="{{ route('delivery.notifications.readAll') }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-link btn-sm p-0" style="font-size:12px">Mark all read</button>
                    </form>
                    @endif
                </li>
                @forelse(auth()->user()->notifications()->latest()->take(6)->get() as $n)
                <li>
                    <a href="{{ route('delivery.orders.available') }}"
                        class="dropdown-item rounded-3 py-2 {{ $n->read_at ? '' : 'bg-light' }}" style="white-space:normal;font-size:12.5px">
                        <div class="fw-600">{{ $n->data['restaurant'] ?? 'Order Ready' }}</div>
                        <div class="text-muted">{{ $n->data['message'] ?? '' }}</div>
                        <div class="text-muted" style="font-size:10px">{{ $n->created_at->diffForHumans() }}</div>
                    </a>
                </li>
                @empty
                <li class="text-center text-muted py-3" style="font-size:13px">No notifications yet</li>
                @endforelse
                <li><hr class="dropdown-divider"></li>
                <li><a href="{{ route('delivery.notifications.index') }}" class="dropdown-item text-center text-primary fw-600" style="font-size:13px">View All</a></li>
            </ul>
        </div>
        <a href="{{ route('delivery.profile.edit') }}" class="user-pill text-decoration-none">
            @if(auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                    class="user-avatar" style="object-fit:cover">
            @else
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="role-badge">Delivery Agent</span>
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
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(el)?.close();
        }, 5000);
    });
</script>
</body>
</html>
