<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EPMS') - Event Planning Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --epms-primary: #1e3a5f; --epms-accent: #e67e22; }
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: var(--epms-primary); }
        .sidebar .nav-link { color: rgba(255,255,255,.85); }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .brand { font-weight: 700; letter-spacing: .5px; }
        .card-stat { border-left: 4px solid var(--epms-accent); }
    </style>
    @stack('styles')
</head>
<body>
@auth
@php($roleLabel = config('epms.auth_roles.'.(auth()->user()->role?->slug), auth()->user()->role?->name))
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar p-3">
            <div class="text-white mb-4">
                <div class="brand fs-4">EPMS</div>
                <small class="opacity-75">{{ $roleLabel }}</small>
            </div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link rounded {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}"><i class="bi bi-calendar-event me-2"></i>Events</a></li>
                @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
                <li><a class="nav-link rounded {{ request()->routeIs('venues.*') ? 'active' : '' }}" href="{{ route('venues.index') }}"><i class="bi bi-building me-2"></i>Venues</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('vendors.*') ? 'active' : '' }}" href="{{ route('vendors.index') }}"><i class="bi bi-shop me-2"></i>Vendors</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}"><i class="bi bi-wallet2 me-2"></i>Budgets</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}"><i class="bi bi-check2-square me-2"></i>Tasks</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}"><i class="bi bi-credit-card me-2"></i>Payments</a></li>
                @elseif(auth()->user()->isVendor())
                <li><a class="nav-link rounded {{ request()->routeIs('vendors.*') ? 'active' : '' }}" href="{{ route('vendors.index') }}"><i class="bi bi-shop me-2"></i>Vendor Profile</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}"><i class="bi bi-credit-card me-2"></i>Payments</a></li>
                @endif
                @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
                <li><a class="nav-link rounded {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</a></li>
                @endif
                @if(auth()->user()->isAdministrator())
                <li><a class="nav-link rounded {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people me-2"></i>Users</a></li>
                <li><a class="nav-link rounded {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                @endif
                <li><a class="nav-link rounded {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a></li>
            </ul>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button class="btn btn-outline-light btn-sm w-100">Logout</button>
            </form>
        </nav>
        <main class="col-md-9 col-lg-10 p-4">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
