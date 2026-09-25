<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'progress' ? 'active' : '' }}" href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'progress']) }}">Laporan Produksi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'daily' ? 'active' : '' }}" href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'daily']) }}">Log Harian</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'certification' ? 'active' : '' }}" href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'certification']) }}">Laporan Sertifikasi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'notes' ? 'active' : '' }}" href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'notes']) }}">Penugasan</a>
    </li>
</ul>
