    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'detail' ? 'active' : '' }}" href="{{ route('planting-locations.show', $plantingLocation) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'fields' ? 'active' : '' }}" href="{{ route('planting-locations.fields.index', $plantingLocation) }}">
            <i class="fas fa-map me-1"></i>Lahan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'plantings' ? 'active' : '' }}" href="{{ route('planting-locations.plantings.index', $plantingLocation) }}">
            <i class="fas fa-seedling me-1"></i>Produksi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'history' ? 'active' : '' }}" href="{{ route('planting-locations.planting-history', $plantingLocation) }}">
            <i class="fas fa-history me-1"></i>Riwayat Produksi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'label-certificates' ? 'active' : '' }}" href="{{ route('planting-locations.label-certificates', $plantingLocation) }}">
            <i class="fas fa-certificate me-1"></i>Riwayat Sertifikat Label
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'attachments' ? 'active' : '' }}" href="{{ route('planting-locations.attachments.index', $plantingLocation) }}">
            <i class="fas fa-paperclip me-1"></i>Lampiran
        </a>
    </li>
