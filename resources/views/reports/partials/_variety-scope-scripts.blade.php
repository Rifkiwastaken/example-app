@push('scripts')
<script>
(function () {
    const catalogUrl = @json(route('reports.catalog'));
    const form = document.getElementById('filterForm');
    if (!form) return;
    const rowsWrap = form.querySelector('.js-variety-rows');
    const scope = form.querySelector('.js-variety-scope');
    const mode = form.querySelector('.js-view-mode');
    const initial = @json(old('filters', request('filters', [])));

    function toggleScope() {
        if (!scope || !mode) return;
        scope.classList.toggle('d-none', mode.value !== 'specific');
    }
    mode?.addEventListener('change', toggleScope);
    toggleScope();

    async function loadCatalog(params) {
        const url = catalogUrl + (params ? '?' + new URLSearchParams(params).toString() : '');
        const res = await fetch(url);
        return res.json();
    }

    function fillSelect(select, items, valueKey, labelKey, selected, placeholder) {
        const current = selected ?? select.dataset.selected ?? '';
        select.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = placeholder;
        select.appendChild(empty);
        items.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = item[valueKey];
            opt.textContent = item[labelKey];
            if (String(item[valueKey]) === String(current)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    async function hydrateRow(row, preset = {}) {
        const category = row.querySelector('.js-filter-category');
        const commodity = row.querySelector('.js-filter-commodity');
        const variety = row.querySelector('.js-filter-variety');
        const source = row.querySelector('.js-filter-source');
        if (preset.category) category.value = preset.category;
        const commodities = await loadCatalog(category.value ? { category: category.value } : {});
        fillSelect(commodity, commodities.commodities || [], 'seed_commodity_id', 'name', preset.commodity_id, 'Pilih nama tanaman');
        const plants = await loadCatalog({
            category: category.value,
            commodity_id: commodity.value || preset.commodity_id || ''
        });
        fillSelect(variety, (plants.plants || []).map((p) => ({
            seed_varieties_id: p.seed_varieties_id,
            label: p.variety || p.name
        })), 'seed_varieties_id', 'label', preset.variety_id, 'Pilih varietas');
        const sources = await loadCatalog({ variety_id: variety.value || preset.variety_id || '' });
        fillSelect(source, (sources.sources || []).map((s) => ({
            seed_source_id: s.seed_source_id,
            label: s.origin_lot_number || s.seed_source_id
        })), 'seed_source_id', 'label', preset.seed_source_id, 'Semua');
    }

    form.querySelectorAll('.js-variety-row').forEach((row, index) => {
        hydrateRow(row, initial[index] || {});
        bindRow(row);
    });

    function bindRow(row) {
        row.querySelector('.js-filter-category')?.addEventListener('change', () => hydrateRow(row, { category: row.querySelector('.js-filter-category').value }));
        row.querySelector('.js-filter-commodity')?.addEventListener('change', () => hydrateRow(row, {
            category: row.querySelector('.js-filter-category').value,
            commodity_id: row.querySelector('.js-filter-commodity').value
        }));
        row.querySelector('.js-filter-variety')?.addEventListener('change', () => hydrateRow(row, {
            category: row.querySelector('.js-filter-category').value,
            commodity_id: row.querySelector('.js-filter-commodity').value,
            variety_id: row.querySelector('.js-filter-variety').value
        }));
        row.querySelector('.js-remove-variety')?.addEventListener('click', () => row.remove());
    }

    form.querySelector('.js-add-variety')?.addEventListener('click', function () {
        const first = rowsWrap.querySelector('.js-variety-row');
        const clone = first.cloneNode(true);
        const index = rowsWrap.querySelectorAll('.js-variety-row').length;
        clone.querySelectorAll('select').forEach((el) => {
            el.name = el.name.replace(/filters\[\d+]/, 'filters[' + index + ']');
            el.value = '';
        });
        if (!clone.querySelector('.js-remove-variety')) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-danger js-remove-variety';
            btn.textContent = 'Hapus';
            clone.appendChild(btn);
        }
        rowsWrap.appendChild(clone);
        bindRow(clone);
        hydrateRow(clone, {});
    });
})();
</script>
@endpush
