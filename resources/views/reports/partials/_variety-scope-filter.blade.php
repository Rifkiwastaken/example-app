@php
    $filters = old('filters', request('filters', [['category' => '', 'commodity_id' => '', 'variety_id' => '', 'seed_source_id' => '']]));
    if (! is_array($filters) || empty($filters)) {
        $filters = [['category' => '', 'commodity_id' => '', 'variety_id' => '', 'seed_source_id' => '']];
    }
    $viewMode = request('view_mode', 'all');
    $categories = ($commodities ?? collect())->pluck('category')->filter()->unique()->sort()->values();
@endphp
<div class="col-md-4 mb-3">
    <label class="form-label">Tampilan laporan</label>
    <select name="view_mode" class="form-select js-view-mode">
        <option value="all" {{ $viewMode === 'all' ? 'selected' : '' }}>Semua varietas</option>
        <option value="specific" {{ $viewMode === 'specific' ? 'selected' : '' }}>Varietas tertentu</option>
    </select>
</div>
<div class="col-12 js-variety-scope {{ $viewMode === 'specific' ? '' : 'd-none' }}">
    <div class="js-variety-rows">
        @foreach($filters as $index => $row)
            <div class="border rounded p-3 mb-3 js-variety-row">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Kategori</label>
                        <select name="filters[{{ $index }}][category]" class="form-select js-filter-category">
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ ($row['category'] ?? '') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Nama tanaman</label>
                        <select name="filters[{{ $index }}][commodity_id]" class="form-select js-filter-commodity">
                            <option value="">Pilih nama tanaman</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Varietas</label>
                        <select name="filters[{{ $index }}][variety_id]" class="form-select js-filter-variety">
                            <option value="">Pilih varietas</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Benih sumber</label>
                        <select name="filters[{{ $index }}][seed_source_id]" class="form-select js-filter-source">
                            <option value="">Semua</option>
                        </select>
                    </div>
                </div>
                @if($index > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-variety">Hapus</button>
                @endif
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm mb-3 js-add-variety">Tambah varietas</button>
</div>
