@php
    $year = $calendar['year'] ?? now()->year;
    $month = $calendar['month'] ?? now()->month;
    $selectable = $selectable ?? false;
    $inputId = $inputId ?? 'tgl_kunjungan';
    $weekdays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
@endphp
<div class="geo-calendar" data-year="{{ $year }}" data-month="{{ $month }}" data-selectable="{{ $selectable ? '1' : '0' }}" data-input="{{ $inputId }}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm geo-prev">&laquo;</button>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm geo-month">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm geo-year">
                @foreach(range(now()->year, now()->year + 2) as $y)
                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm geo-next">&raquo;</button>
    </div>
    <div class="geo-grid-head">
        @foreach($weekdays as $label)
            <div class="geo-cell head">{{ $label }}</div>
        @endforeach
    </div>
    <div class="geo-grid-body"></div>
    <div class="mt-2 small">
        <span class="geo-legend booked"></span> Sudah dibooking / weekend / libur
        <span class="geo-legend available ms-3"></span> Tersedia
    </div>
</div>
@once
@push('styles')
<style>
.geo-grid-head, .geo-grid-body { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
.geo-cell { min-height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; background: #fff; border: 1px solid #e5e7eb; }
.geo-cell.head { background: transparent; border: 0; font-weight: 600; min-height: 28px; }
.geo-cell.muted { visibility: hidden; }
.geo-cell.unavailable { background: #dc3545; color: #fff; cursor: not-allowed; }
.geo-cell.available { background: #ecfdf5; cursor: pointer; }
.geo-cell.selected { outline: 2px solid #059669; }
.geo-legend { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: middle; }
.geo-legend.booked { background: #dc3545; }
.geo-legend.available { background: #ecfdf5; border: 1px solid #059669; }
.geo-tooltip { position: absolute; z-index: 20; background: #111827; color: #fff; padding: 8px 10px; border-radius: 8px; font-size: 12px; pointer-events: none; max-width: 240px; }
</style>
@endpush
@push('scripts')
<script>
window.initGeoCalendar = function (root, endpoint) {
    const body = root.querySelector('.geo-grid-body');
    const monthSelect = root.querySelector('.geo-month');
    const yearSelect = root.querySelector('.geo-year');
    const selectable = root.dataset.selectable === '1';
    const input = document.getElementById(root.dataset.input);
    let tooltip = document.querySelector('.geo-tooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.className = 'geo-tooltip d-none';
        document.body.appendChild(tooltip);
    }
    function load(year, month) {
        fetch(endpoint + '?year=' + year + '&month=' + month)
            .then(r => r.json())
            .then(render);
    }
    function render(data) {
        monthSelect.value = data.month;
        yearSelect.value = data.year;
        const blanks = data.start_weekday;
        let html = '';
        for (let i = 0; i < blanks; i++) html += '<div class="geo-cell muted"></div>';
        data.days.forEach(function (day) {
            const cls = day.unavailable ? 'unavailable' : 'available';
            const info = (day.bookings || []).map(b => b.nama_lembaga + ' (' + b.status_kedatangan + ')').join('<br>');
            html += '<div class="geo-cell ' + cls + '" data-date="' + day.date + '" data-info="' + encodeURIComponent(info || (day.holiday ? 'Libur nasional' : (day.weekend ? 'Akhir pekan' : 'Tersedia'))) + '">' + day.day + '</div>';
        });
        body.innerHTML = html;
        const list = document.getElementById('geo-month-list');
        if (list) {
            list.innerHTML = (data.visits || []).length
                ? data.visits.map(v => '<li class="list-group-item d-flex justify-content-between"><span>' + v.nama_lembaga + '<br><small>' + v.tgl_kunjungan + '</small></span><span class="badge bg-secondary align-self-center">' + v.status_kedatangan + '</span></li>').join('')
                : '<li class="list-group-item text-muted">Belum ada kunjungan di bulan ini.</li>';
        }
        body.querySelectorAll('.geo-cell[data-date]').forEach(function (cell) {
            cell.addEventListener('mouseenter', function (e) {
                tooltip.innerHTML = decodeURIComponent(cell.dataset.info || '');
                tooltip.classList.remove('d-none');
                tooltip.style.left = (e.pageX + 12) + 'px';
                tooltip.style.top = (e.pageY + 12) + 'px';
            });
            cell.addEventListener('mouseleave', function () { tooltip.classList.add('d-none'); });
            cell.addEventListener('click', function () {
                if (!selectable || cell.classList.contains('unavailable')) return;
                body.querySelectorAll('.geo-cell').forEach(c => c.classList.remove('selected'));
                cell.classList.add('selected');
                if (input) input.value = cell.dataset.date;
            });
        });
    }
    root.querySelector('.geo-prev').addEventListener('click', function () {
        let m = parseInt(monthSelect.value, 10) - 1, y = parseInt(yearSelect.value, 10);
        if (m < 1) { m = 12; y--; }
        load(y, m);
    });
    root.querySelector('.geo-next').addEventListener('click', function () {
        let m = parseInt(monthSelect.value, 10) + 1, y = parseInt(yearSelect.value, 10);
        if (m > 12) { m = 1; y++; }
        load(y, m);
    });
    monthSelect.addEventListener('change', function () { load(yearSelect.value, monthSelect.value); });
    yearSelect.addEventListener('change', function () { load(yearSelect.value, monthSelect.value); });
    render(@json($calendar));
};
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.geo-calendar').forEach(function (root) {
        if (root.dataset.ready === '1') return;
        root.dataset.ready = '1';
        window.initGeoCalendar(root, @json(route('public.geowisata.calendar')));
    });
});
</script>
@endpush
@endonce
