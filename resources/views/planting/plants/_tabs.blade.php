    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'detail' ? 'active' : '' }}" href="{{ route('plants.show', $plant) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'seed-sources' ? 'active' : '' }}" href="{{ route('plants.seed-sources.index', $plant) }}">
            <i class="fas fa-warehouse me-1"></i>Benih Sumber
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'current-plantings' ? 'active' : '' }}" href="{{ route('plants.current-plantings', $plant) }}">
            <i class="fas fa-seedling me-1"></i>Produksi Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'label-certificates' ? 'active' : '' }}" href="{{ route('plants.label-certificates', $plant) }}">
            <i class="fas fa-certificate me-1"></i>Sertifikat Label
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'label-stock' ? 'active' : '' }}" href="{{ route('plants.label-stock-history', $plant) }}">
            <i class="fas fa-tags me-1"></i>Riwayat Label & Stok
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'sales-history' ? 'active' : '' }}" href="{{ route('plants.sales-history', $plant) }}">
            <i class="fas fa-receipt me-1"></i>Riwayat Penjualan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'notes' ? 'active' : '' }}" href="{{ route('plants.notes.index', $plant) }}">
            <i class="fas fa-paperclip me-1"></i>Lampiran
        </a>
    </li>
