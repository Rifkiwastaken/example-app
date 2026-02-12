<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIBESTI')</title>
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
            transition: width 0.3s ease, transform 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sidebar.collapsed {
            width: 0;
            transform: translateX(-100%);
        }
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #495057;
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }
        .sidebar-toggle-btn:hover {
            background-color: #495057;
        }
        .sidebar-toggle-btn-floating {
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 1001;
            background-color: #343a40;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: none;
            align-items: center;
            justify-content: center;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .sidebar-toggle-btn-floating:hover {
            background-color: #495057;
        }
        .sidebar-toggle-btn-floating.show {
            display: flex;
        }
        .sidebar.collapsed .sidebar-toggle-btn {
            display: none;
        }
        @media (max-width: 768px) {
            .sidebar-toggle-btn-floating {
                display: none !important;
            }
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
            transition: margin-left 0.3s ease;
        }
        .main-content.sidebar-collapsed {
            margin-left: 0;
        }
        .sidebar.collapsed .sidebar-nav,
        .sidebar.collapsed .sidebar-brand {
            opacity: 0;
            visibility: hidden;
        }
        .sidebar .sidebar-nav,
        .sidebar .sidebar-brand {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.2s, visibility 0.2s;
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
                <i class="fas fa-seedling"></i> SIBESTI
            </a>
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Sembunyikan Menu">
                <i class="fas fa-chevron-left"></i>
            </button>
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
                <li class="nav-item has-submenu {{ request()->routeIs('certifications.*') ? 'expanded' : '' }}">
                    <a class="nav-link {{ request()->routeIs('certifications.*') ? 'active' : '' }}" href="#" onclick="toggleSubmenu(event, this)">
                        <i class="fas fa-certificate nav-icon"></i>
                        Sertifikasi
                        <i class="fas fa-chevron-up chevron ms-auto"></i>
                    </a>
                    <div class="submenu {{ request()->routeIs('certifications.*') ? 'd-block' : 'd-none' }}">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('certifications.index') || request()->routeIs('certifications.by-plant') || request()->routeIs('certifications.show') ? 'active' : '' }}" href="{{ route('certifications.index') }}">
                                    Data Riwayat Sertifikasi
                                </a>
                            </li>
                        </ul>
                    </div>
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
                                <a class="nav-link {{ request()->routeIs('seed-stock.*') ? 'active' : '' }}" href="{{ route('seed-stock.index') }}">
                                    Stok Benih
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('warehouse-locations.*') ? 'active' : '' }}" href="{{ route('warehouse-locations.index') }}">
                                    Lokasi Gudang
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
                        Pencatatan Penjualan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                        <i class="fas fa-money-bill-wave nav-icon"></i>
                        Pengeluaran
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                        <i class="fas fa-chart-line nav-icon"></i>
                        Laporan
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
    
    <!-- Floating Toggle Button (shown when sidebar is collapsed) -->
    <button class="sidebar-toggle-btn sidebar-toggle-btn-floating" id="sidebarToggleBtnFloating" title="Tampilkan Menu">
        <i class="fas fa-chevron-right"></i>
    </button>

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
                    <span class="fw-bold text-success">SIBESTI</span>
                    <span class="text-muted ms-2">- Sistem Informasi Benih Bersertifikat</span>
                </div>
            </div>
            
            <div class="header-icons">
                @php
                    $taskNotifications = collect();
                    $noteNotifications = collect();
                    $lowStockNotifications = collect();
                    $expiredBinStockNotifications = collect();
                    $expiringSeedNotifications = collect();
                    $notificationCount = 0;
                    
                    if (auth()->check()) {
                        $user = auth()->user();
                        
                        // Get task notifications (for admin, kepala_satuan_tugas, penangkar)
                        if ($user->isAdmin() || in_array($user->role, ['kepala_satuan_tugas', 'penangkar'])) {
                            $taskQuery = \App\Models\Task::with(['assignedUser', 'plantingLocation'])
                                ->whereIn('new_status', ['dalam_progress', 'dilakukan'])
                                ->whereNotNull('due_date')
                                ->where('due_date', '>=', \Carbon\Carbon::today())
                                ->where('due_date', '<=', \Carbon\Carbon::today()->addDays(3));
                            
                            if (!$user->isAdmin()) {
                                if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                                    $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                                    $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                                    $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                                    
                                    if (count($assignedLocationIds) > 0) {
                                        $taskQuery->whereIn('planting_location_id', $assignedLocationIds);
                                    } else {
                                        $taskQuery->whereRaw('1 = 0');
                                    }
                                }
                            }
                            
                            $taskNotifications = $taskQuery->get();
                            
                            // Get note notifications
                            $noteQuery = \App\Models\PlantingLocationNote::with(['plantingLocation', 'user'])
                                ->whereNotNull('assigned_to')
                                ->whereJsonContains('assigned_to', $user->user_id)
                                ->where(function($q) use ($user) {
                                    $q->whereNull('read_by')
                                      ->orWhereJsonDoesntContain('read_by', $user->user_id);
                                });
                            
                            if (!$user->isAdmin()) {
                                if ($user->role === 'kepala_satuan_tugas' || $user->role === 'penangkar') {
                                    $managedIds = $user->managedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                                    $workedIds = $user->workedPlantingLocations()->pluck('planting_locations.planting_location_id')->toArray();
                                    $assignedLocationIds = array_unique(array_merge($managedIds, $workedIds));
                                    
                                    if (count($assignedLocationIds) > 0) {
                                        $noteQuery->whereIn('planting_location_id', $assignedLocationIds);
                                    } else {
                                        $noteQuery->whereRaw('1 = 0');
                                    }
                                }
                            }
                            
                            $noteNotifications = $noteQuery->get();
                        }
                        
                        // Get low stock notifications (for admin and petugas gudang)
                        if ($user->isAdmin() || $user->role === 'petugas_gudang') {
                            $inventoryTypes = \App\Models\InventoryType::whereNotNull('low_stock_threshold')
                                ->where('low_stock_threshold', '>', 0)
                                ->with(['plant.type'])
                                ->get();
                            
                            foreach ($inventoryTypes as $type) {
                                $totalStock = $type->seeds()->sum('total_seed_quantity') ?? 0;
                                $threshold = $type->low_stock_threshold ?? 0;
                                $thresholdUnit = $type->low_stock_unit ?? 'kg';
                                $stockUnit = $type->unit ?? 'kg';
                                
                                // Convert to kg for comparison
                                $totalStockInKg = $totalStock;
                                $thresholdInKg = $threshold;
                                
                                if ($stockUnit === 'ton') {
                                    $totalStockInKg = $totalStock * 1000;
                                } elseif ($stockUnit === 'gram') {
                                    $totalStockInKg = $totalStock / 1000;
                                }
                                
                                if ($thresholdUnit === 'ton') {
                                    $thresholdInKg = $threshold * 1000;
                                } elseif ($thresholdUnit === 'gram') {
                                    $thresholdInKg = $threshold / 1000;
                                }
                                
                                if ($totalStockInKg < $thresholdInKg) {
                                    $lowStockNotifications->push([
                                        'id' => $type->inventory_type_id,
                                        'name' => $type->plant->name ?? $type->name,
                                        'variety' => $type->plant->variety ?? null,
                                        'current_stock' => $totalStock,
                                        'stock_unit' => $stockUnit,
                                        'threshold' => $threshold,
                                        'notification_type' => 'low_stock',
                                    ]);
                                }
                            }
                        }
                        
                        // Get expired bin stock notifications (for admin and petugas gudang)
                        if ($user->isAdmin() || $user->role === 'petugas_gudang') {
                            $today = \Carbon\Carbon::today();
                            $expiredLots = \App\Models\InventoryLot::with(['inventoryType', 'warehouse', 'bin'])
                                ->whereNotNull('bin_id')
                                ->whereNotNull('expiry_date')
                                ->where('expiry_date', '<', $today)
                                ->where('current_stock', '>', 0)
                                ->orderBy('expiry_date', 'asc')
                                ->get()
                                ->groupBy(function($lot) {
                                    return $lot->warehouse_id . '-' . $lot->bin_id;
                                });
                            
                            foreach ($expiredLots as $lots) {
                                $firstLot = $lots->first();
                                $expiredBinStockNotifications->push([
                                    'warehouse_id' => $firstLot->warehouse_id,
                                    'warehouse_name' => $firstLot->warehouse->name ?? 'Gudang Tidak Diketahui',
                                    'bin_id' => $firstLot->bin_id,
                                    'bin_name' => $firstLot->bin->name ?? 'Bin Tidak Diketahui',
                                    'expired_count' => $lots->count(),
                                    'notification_type' => 'expired_bin_stock',
                                ]);
                            }
                        }
                        
                        // Get expiring seed notifications (for responsible person)
                        $threeMonthsFromNow = \Carbon\Carbon::today()->addMonths(3);
                        $today = \Carbon\Carbon::today();
                        $inventoryTypeIds = \App\Models\InventoryType::where('responsible_person_id', $user->user_id)
                            ->pluck('inventory_type_id')
                            ->toArray();
                        
                        if (!empty($inventoryTypeIds)) {
                            $expiringSeeds = \App\Models\InventoryTypeSeed::with(['inventoryType', 'plant.type', 'plantingLocation'])
                                ->whereIn('inventory_type_id', $inventoryTypeIds)
                                ->whereNotNull('expiry_date')
                                ->where('expiry_date', '<=', $threeMonthsFromNow)
                                ->orderBy('expiry_date', 'asc')
                                ->get();
                            
                            foreach ($expiringSeeds as $seed) {
                                $isExpired = $seed->expiry_date->isPast();
                                $daysUntil = $isExpired ? $seed->expiry_date->diffInDays($today) : $today->diffInDays($seed->expiry_date);
                                
                                $expiringSeedNotifications->push([
                                    'id' => $seed->inventory_type_seed_id,
                                    'inventory_type_id' => $seed->inventory_type_id,
                                    'name' => $seed->plant->name ?? $seed->inventoryType->name ?? 'Benih',
                                    'variety' => $seed->plant->variety ?? null,
                                    'expiry_date' => $seed->expiry_date->format('d M Y'),
                                    'is_expired' => $isExpired,
                                    'days_until' => $daysUntil,
                                    'notification_type' => 'expiring_seed',
                                ]);
                            }
                        }
                        
                        $notificationCount = $taskNotifications->count() 
                            + $noteNotifications->count() 
                            + $lowStockNotifications->count() 
                            + $expiredBinStockNotifications->count() 
                            + $expiringSeedNotifications->count();
                    }
                @endphp
                <div class="dropdown">
                    <a href="#" class="header-icon icon-notification dropdown-toggle position-relative" data-bs-toggle="dropdown" aria-expanded="false" role="button" style="cursor: pointer;">
                    <i class="fas fa-bell"></i>
                        @if($notificationCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px; max-width: 400px; max-height: 500px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifikasi</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        @if($notificationCount > 0)
                            @php
                                $shownCount = 0;
                                $maxShow = 5;
                            @endphp
                            
                            {{-- Low Stock Notifications --}}
                            @if($lowStockNotifications->count() > 0 && $shownCount < $maxShow)
                                <li><h6 class="dropdown-header small"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Stok Rendah</h6></li>
                                @foreach($lowStockNotifications->take($maxShow - $shownCount) as $item)
                                    @php $shownCount++; @endphp
                                    <li>
                                        <a class="dropdown-item text-warning" href="{{ route('seed-stock.show', $item['id']) }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-boxes me-1"></i>{{ Str::limit($item['name'], 25) }}</strong>
                                                    @if($item['variety'])
                                                        <br><small class="text-muted">{{ Str::limit($item['variety'], 30) }}</small>
                                                    @endif
                                                    <br><small class="text-muted">
                                                        Stok: {{ number_format($item['current_stock'], 2) }} {{ $item['stock_unit'] }} | 
                                                        Threshold: {{ number_format($item['threshold'], 2) }} {{ $item['stock_unit'] }}
                                                    </small>
                                                </div>
                                                <div class="text-end ms-2">
                                                    <span class="badge bg-warning">Rendah</span>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                @if($shownCount < $maxShow && ($taskNotifications->count() > 0 || $noteNotifications->count() > 0 || $expiredBinStockNotifications->count() > 0 || $expiringSeedNotifications->count() > 0))
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @endif
                            
                            {{-- Expired Bin Stock Notifications --}}
                            @if($expiredBinStockNotifications->count() > 0 && $shownCount < $maxShow)
                                <li><h6 class="dropdown-header small"><i class="fas fa-exclamation-circle text-danger me-1"></i>Benih Kadaluarsa di Bin</h6></li>
                                @foreach($expiredBinStockNotifications->take($maxShow - $shownCount) as $item)
                                    @php $shownCount++; @endphp
                                    <li>
                                        <a class="dropdown-item text-danger" href="{{ route('warehouse-locations.show', $item['warehouse_id']) }}?bin_id={{ $item['bin_id'] }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-warehouse me-1"></i>{{ Str::limit($item['warehouse_name'], 20) }}</strong>
                                                    <br><small class="text-muted">
                                                        Bin: {{ $item['bin_name'] }} | 
                                                        {{ $item['expired_count'] }} lot kadaluarsa
                                                    </small>
                                                </div>
                                                <div class="text-end ms-2">
                                                    <span class="badge bg-danger">Kadaluarsa</span>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                @if($shownCount < $maxShow && ($taskNotifications->count() > 0 || $noteNotifications->count() > 0 || $expiringSeedNotifications->count() > 0))
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @endif
                            
                            {{-- Expiring Seed Notifications --}}
                            @if($expiringSeedNotifications->count() > 0 && $shownCount < $maxShow)
                                <li><h6 class="dropdown-header small"><i class="fas fa-clock text-warning me-1"></i>Benih Mendekati Kadaluarsa</h6></li>
                                @foreach($expiringSeedNotifications->take($maxShow - $shownCount) as $item)
                                    @php $shownCount++; @endphp
                                    <li>
                                        <a class="dropdown-item {{ $item['is_expired'] ? 'text-danger' : 'text-warning' }}" href="{{ route('seed-stock.show', $item['inventory_type_id']) }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-seedling me-1"></i>{{ Str::limit($item['name'], 25) }}</strong>
                                                    @if($item['variety'])
                                                        <br><small class="text-muted">{{ Str::limit($item['variety'], 30) }}</small>
                                                    @endif
                                                    <br><small class="text-muted">
                                                        Kadaluarsa: {{ $item['expiry_date'] }}
                                                    </small>
                                                </div>
                                                <div class="text-end ms-2">
                                                    @if($item['is_expired'])
                                                        <span class="badge bg-danger">{{ $item['days_until'] }} hari lalu</span>
                                                    @else
                                                        <span class="badge bg-warning">{{ $item['days_until'] }} hari</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                @if($shownCount < $maxShow && ($taskNotifications->count() > 0 || $noteNotifications->count() > 0))
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @endif
                            
                            {{-- Task Notifications --}}
                            @if($taskNotifications->count() > 0 && $shownCount < $maxShow)
                                <li><h6 class="dropdown-header small"><i class="fas fa-tasks me-1"></i>Tugas</h6></li>
                                @foreach($taskNotifications->take($maxShow - $shownCount) as $task)
                                    @php 
                                        $shownCount++;
                                        $daysUntil = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($task->due_date), false);
                                        $isUrgent = $daysUntil <= 1;
                                    @endphp
                                    <li>
                                        <a class="dropdown-item {{ $isUrgent ? 'text-danger' : '' }}" href="{{ route('planting-locations.show', $task->planting_location_id) }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-tasks me-1"></i>{{ Str::limit($task->title, 30) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $task->plantingLocation->name ?? 'Umum' }}
                                                    </small>
                                                </div>
                                                <div class="text-end ms-2">
                                                    @if($daysUntil === 0)
                                                        <span class="badge bg-danger">Hari ini</span>
                                                    @elseif($daysUntil === 1)
                                                        <span class="badge bg-warning">Besok</span>
                                                    @else
                                                        <span class="badge bg-info">{{ $daysUntil }} hari</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                                @if($shownCount < $maxShow && $noteNotifications->count() > 0)
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @endif
                            
                            {{-- Note Notifications --}}
                            @if($noteNotifications->count() > 0 && $shownCount < $maxShow)
                                <li><h6 class="dropdown-header small"><i class="fas fa-sticky-note me-1"></i>Catatan</h6></li>
                                @foreach($noteNotifications->take($maxShow - $shownCount) as $note)
                                    @php $shownCount++; @endphp
                                    <li>
                                        <a class="dropdown-item text-warning" href="{{ route('planting-locations.show', $note->planting_location_id) }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($note->title ?: 'Catatan', 30) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $note->plantingLocation->name ?? 'Umum' }}
                                                    </small>
                                                </div>
                                                <div class="text-end ms-2">
                                                    <span class="badge bg-warning">Baru</span>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                            
                            @if($notificationCount > $shownCount)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-muted" href="#">
                                    <small>+{{ $notificationCount - $shownCount }} notifikasi lainnya</small>
                                </a></li>
                            @endif
                        @else
                            <li><a class="dropdown-item text-center text-muted" href="#">
                                <small>Tidak ada notifikasi</small>
                            </a></li>
                        @endif
                    </ul>
                </div>
                <div class="dropdown">
                    <a href="#" class="header-icon icon-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button" style="cursor: pointer; overflow: hidden;">
                        @if(Auth::check() && Auth::user()->photo_path)
                            <img src="{{ Storage::url(Auth::user()->photo_path) }}" 
                                 class="rounded-circle" 
                                 style="width: 100%; height: 100%; object-fit: cover;" 
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <i class="fas fa-user"></i>
                        @endif
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
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarToggleBtnFloating = document.getElementById('sidebarToggleBtnFloating');
        
        // Load saved sidebar state from localStorage
        const savedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (savedSidebarState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
        }
        
        // Toggle sidebar function
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            
            // Show/hide floating button
            if (sidebar.classList.contains('collapsed')) {
                sidebarToggleBtnFloating.classList.add('show');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                sidebarToggleBtnFloating.classList.remove('show');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }
        
        // Initialize floating button visibility based on saved state
        if (savedSidebarState === 'true' && sidebarToggleBtnFloating) {
            sidebarToggleBtnFloating.classList.add('show');
        }
        
        // Toggle sidebar from header button
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        // Toggle sidebar from floating button
        if (sidebarToggleBtnFloating) {
            sidebarToggleBtnFloating.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        // Sidebar toggle for mobile
        const mobileSidebarToggle = document.getElementById('sidebarToggle');
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const mobileToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                mobileToggle &&
                !sidebar.contains(event.target) && 
                !mobileToggle.contains(event.target)) {
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

        // Global delete confirmation function
        function confirmDelete(deleteUrl, itemName, itemType) {
            const modalElement = document.getElementById('globalDeleteConfirmModal');
            if (!modalElement) {
                // Fallback to browser confirm if modal doesn't exist
                return confirm('Apakah Anda yakin ingin menghapus ' + itemType + ' "' + itemName + '"?');
            }
            
            document.getElementById('globalDeleteItemName').textContent = itemType + ' "' + itemName + '"';
            document.getElementById('globalDeleteForm').action = deleteUrl;
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            return false;
        }
    </script>
    
    <!-- Global Delete Confirmation Modal -->
    <div class="modal fade" id="globalDeleteConfirmModal" tabindex="-1" aria-labelledby="globalDeleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalDeleteConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus <strong id="globalDeleteItemName"></strong>?</p>
                    <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="globalDeleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
