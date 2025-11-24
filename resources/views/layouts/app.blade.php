<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIBIT')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #495057;
            flex-shrink: 0;
        }
        .sidebar-brand {
            color: #28a745;
            font-weight: bold;
            font-size: 1.5rem;
            text-decoration: none;
        }
        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: #2c3136;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 3px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #5a6268;
        }
        .nav-item {
            margin: 0.25rem 0;
        }
        .nav-link {
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        .nav-link.active {
            background-color: #28a745;
        }
        .nav-link.active-submenu {
            background-color: #e9ecef;
            color: #495057;
        }
        .nav-icon {
            margin-right: 0.75rem;
            width: 20px;
        }
        .submenu {
            background-color: #f8f9fa;
            border-left: 3px solid #28a745;
            margin-left: 1rem;
            margin-right: 1rem;
            border-radius: 0 0 5px 5px;
            padding: 0.5rem 0;
        }
        .submenu .nav-link {
            padding: 0.5rem 1rem;
            color: #495057;
            font-size: 0.9rem;
        }
        .submenu .nav-link:hover {
            background-color: #e9ecef;
            color: #495057;
        }
        .submenu .nav-link.active {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 500;
        }
        .nav-item.has-submenu .nav-link {
            position: relative;
        }
        .nav-item.has-submenu .nav-link .chevron {
            transition: transform 0.3s;
        }
        .nav-item.has-submenu.expanded .nav-link .chevron {
            transform: rotate(90deg);
        }
        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        .main-content.expanded {
            margin-left: 0;
        }
        .top-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-icons {
            display: flex;
            gap: 1rem;
        }
        .header-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .header-icon:hover {
            transform: scale(1.1);
            color: white;
        }
        .icon-notification { background-color: #28a745; }
        .icon-user { background-color: #6c757d; }
        .icon-user:hover { background-color: #5a6268; }
        .dropdown-toggle::after {
            display: none;
        }
        .dropdown-menu {
            margin-top: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .content-area {
            padding: 2rem;
        }
        .weather-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .task-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .priority-highest {
            background-color: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .priority-high {
            background-color: #fd7e14;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .priority-medium {
            background-color: #ffc107;
            color: black;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .priority-low {
            background-color: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        /* Enhanced Nav Tabs Styling */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            padding: 0.5rem 0.5rem 0 0.5rem;
        }
        .nav-tabs .nav-item {
            margin-bottom: -2px;
        }
        .nav-tabs .nav-link {
            color: #495057;
            background-color: transparent;
            border: 1px solid transparent;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .nav-tabs .nav-link:hover {
            color: #212529;
            background-color: #e9ecef;
            border-color: #dee2e6 #dee2e6 transparent;
        }
        .nav-tabs .nav-link.active {
            color: #212529;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            border-bottom-color: transparent;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active:hover {
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .tab-content {
            background-color: #fff;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-seedling"></i> SIBIT
            </a>
        </div>
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-home nav-icon"></i>
                        Dashboard
                    </a>
                </li>
                @if(auth()->user()->hasAccessTo('penanaman') || auth()->user()->isAdmin())
                <li class="nav-item has-submenu {{ request()->routeIs('plants.*') || request()->routeIs('planting-locations.*') ? 'expanded' : '' }}">
                    <a class="nav-link {{ request()->routeIs('plants.*') || request()->routeIs('planting-locations.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu(event, this)">
                        <i class="fas fa-seedling nav-icon"></i>
                        Penanaman
                        <i class="fas fa-chevron-up chevron ms-auto"></i>
                    </a>
                    <div class="submenu {{ request()->routeIs('plants.*') || request()->routeIs('planting-locations.*') ? 'd-block' : 'd-none' }}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}" href="{{ route('plants.index') }}">
                                    Tanaman Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('planting-locations.*') ? 'active' : '' }}" href="{{ route('planting-locations.index') }}">
                                    Lokasi Penanaman
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                @if(auth()->user()->hasAccessTo('sertifikasi') || auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('certifications.*') ? 'active' : '' }}" href="{{ route('certifications.index') }}">
                        <i class="fas fa-certificate nav-icon"></i>
                        Sertifikasi
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasAccessTo('gudang') || auth()->user()->isAdmin())
                <li class="nav-item has-submenu {{ request()->routeIs('warehouse-locations.*') || request()->routeIs('seed-stock.*') ? 'expanded' : '' }}">
                    <a class="nav-link {{ request()->routeIs('warehouse-locations.*') || request()->routeIs('seed-stock.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu(event, this)">
                        <i class="fas fa-warehouse nav-icon"></i>
                        Gudang
                        <i class="fas fa-chevron-up chevron ms-auto"></i>
                    </a>
                    <div class="submenu {{ request()->routeIs('warehouse-locations.*') || request()->routeIs('seed-stock.*') ? 'd-block' : 'd-none' }}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('warehouse-locations.*') ? 'active' : '' }}" href="{{ route('warehouse-locations.index') }}">
                                    Lokasi Gudang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('seed-stock.*') ? 'active' : '' }}" href="{{ route('seed-stock.index') }}">
                                    Stok Bibit
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                @if(auth()->user()->hasAccessTo('penjualan') || auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                        <i class="fas fa-shopping-cart nav-icon"></i>
                        Penjualan
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('planning.*') ? 'active' : '' }}" href="{{ route('planning.index') }}">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        Perencanaan
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}">
                        <i class="fas fa-map-marker-alt nav-icon"></i>
                        Lokasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                        <i class="fas fa-chart-line nav-icon"></i>
                        Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}" href="{{ route('contacts.index') }}">
                        <i class="fas fa-address-book nav-icon"></i>
                        Kontak
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="fas fa-user nav-icon"></i>
                        Akun
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Header -->
        <div class="top-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center">
                    <i class="fas fa-seedling text-success me-2"></i>
                    <span class="fw-bold text-success">SIBIT</span>
                </div>
            </div>
            
            <div class="header-icons">
                <a href="#" class="header-icon icon-notification">
                    <i class="fas fa-bell"></i>
                </a>
                <div class="dropdown">
                    <a href="#" class="header-icon icon-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button" style="cursor: pointer;">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ Auth::check() ? Auth::user()->name : 'User' }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger" style="cursor: pointer;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Toggle submenu
        function toggleSubmenu(event, element) {
            event.preventDefault();
            const navItem = element.closest('.nav-item');
            const submenu = navItem.querySelector('.submenu');
            const chevron = element.querySelector('.chevron');
            
            // Toggle expanded class
            navItem.classList.toggle('expanded');
            
            // Toggle submenu visibility
            if (submenu.classList.contains('d-none')) {
                submenu.classList.remove('d-none');
                submenu.classList.add('d-block');
            } else {
                submenu.classList.remove('d-block');
                submenu.classList.add('d-none');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
