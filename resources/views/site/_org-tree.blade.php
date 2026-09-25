@foreach($nodes as $node)
    <div class="mb-3">
        <button type="button" class="org-card btn w-100 text-start bg-white border-0 shadow-sm p-3"
                data-jabatan="{{ $node->jabatan }}" data-nama="{{ $node->nama_pejabat }}"
                data-nip="{{ $node->nip }}" data-tupoksi="{{ e($node->tupoksi) }}">
            <div class="d-flex align-items-center gap-3">
                @if($node->fotoUrl())
                    <img src="{{ $node->fotoUrl() }}" alt="{{ $node->nama_pejabat }}" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                @else
                    <div class="rounded bg-success-subtle d-flex align-items-center justify-content-center" style="width:56px;height:56px;color:#065f46;"><i class="fas fa-user"></i></div>
                @endif
                <div>
                    <div class="fw-bold">{{ $node->nama_pejabat }}</div>
                    <div class="small text-success">{{ $node->jabatan }}</div>
                    <div class="small text-muted">NIP {{ $node->nip ?: '-' }}</div>
                </div>
            </div>
        </button>
        @if($node->children->count())
            <div class="ps-4 pt-3 border-start ms-4">
                @include('site._org-tree', ['nodes' => $node->children])
            </div>
        @endif
    </div>
@endforeach
