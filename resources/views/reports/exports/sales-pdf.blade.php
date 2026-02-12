<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN REKAPITULASI PENJUALAN & DISTRIBUSI</h2>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Struk</th>
                <th>Tanggal Penjualan</th>
                <th>Pembeli</th>
                <th>Komoditas</th>
                <th>Jumlah Item</th>
                <th>Total Penjualan</th>
                <th>Metode Pembayaran</th>
                <th>Status Pembayaran</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $totalAmount = 0;
            @endphp
            @foreach($sales as $sale)
                @php
                    $uniquePlants = $sale->items->map(function($item) {
                        return $item->inventoryType->plant->name ?? ($item->inventoryType->name ?? 'N/A');
                    })->unique()->values()->implode(', ');
                    $totalAmount += $sale->total_amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $sale->receipt_number ?? '-' }}</td>
                    <td>{{ $sale->sale_date ? $sale->sale_date->format('d-m-Y') : '-' }}</td>
                    <td>{{ $sale->buyer_name ?? '-' }}</td>
                    <td>{{ $uniquePlants }}</td>
                    <td class="text-right">{{ number_format($sale->total_items, 2) }}</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    <td>{{ $sale->payment_method_label ?? '-' }}</td>
                    <td>{{ $sale->payment_status_label ?? '-' }}</td>
                    <td>{{ $sale->user->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total:</th>
                <th class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>



















