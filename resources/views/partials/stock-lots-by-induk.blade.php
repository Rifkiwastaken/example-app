@php
    $lots = $lots ?? collect();
    $unit = $unit ?? '';
    $showAdd = $showAdd ?? false;
    $certificateRoute = $certificateRoute ?? 'public.seed-certificate';
@endphp
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>No induk</th>
                <th>Tanggal lulus</th>
                <th>Total stok saat ini{{ $unit ? ' ('.$unit.')' : '' }}</th>
                <th>Isi kemasan</th>
                <th>Total produk{{ $showAdd ? ' (kemasan)' : '' }}</th>
                <th>Tanggal masa kadaluarsa</th>
                <th>Sertifikat</th>
                @if($showAdd)<th></th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($lots as $lot)
                <tr>
                    <td>{{ $lot['nomor_induk'] ?? '-' }}</td>
                    <td>{{ $lot['tanggal_lulus'] ?? '-' }}</td>
                    <td>{{ number_format((float) ($lot['total_stok'] ?? 0), 2) }} {{ $lot['unit'] ?? $unit }}</td>
                    <td>{{ number_format((float) ($lot['isi_kemasan'] ?? 0), 2) }} {{ $lot['unit'] ?? $unit }}</td>
                    <td>{{ (int) ($lot['total_produk'] ?? 0) }}</td>
                    <td>{{ $lot['tanggal_kadaluarsa'] ?? '-' }}</td>
                    <td>
                        @if(!empty($lot['stock_id']))
                            <a class="btn btn-sm btn-outline-success" href="{{ route($certificateRoute, $lot['stock_id']) }}" target="_blank">Lihat sertifikat</a>
                        @else
                            -
                        @endif
                    </td>
                    @if($showAdd)
                        <td>
                            <button type="button" class="btn btn-sm btn-success btn-add-lot"
                                data-stock="{{ $lot['stock_id'] }}"
                                data-induk="{{ $lot['nomor_induk'] }}"
                                data-size="{{ $lot['isi_kemasan'] }}"
                                data-unit="{{ $lot['unit'] ?? $unit }}"
                                data-price="{{ $lot['unit_price'] ?? 0 }}"
                                data-max="{{ $lot['total_produk'] ?? 0 }}">Tambahkan produk</button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $showAdd ? 8 : 7 }}" class="text-center text-muted py-3">Belum ada stok aktif berdasarkan nomor induk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
