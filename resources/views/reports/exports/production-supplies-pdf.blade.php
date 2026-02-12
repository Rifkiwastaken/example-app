<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggunaan Sarana Produksi</title>
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
        <h2>LAPORAN PENGGUNAAN SARANA PRODUKSI</h2>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Pengeluaran</th>
                <th>Nama Pengeluaran</th>
                <th>Jenis Pengeluaran</th>
                <th>Komoditas</th>
                <th>Lokasi Lahan</th>
                <th>Penanggung Jawab</th>
                <th>Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowNumber = 1;
                $total = 0;
            @endphp
            @foreach($expenses as $expense)
                @php
                    $plant = null;
                    if ($expense->planting && $expense->planting->plant) {
                        $plant = $expense->planting->plant;
                    } elseif ($expense->treatment && $expense->treatment->planting && $expense->treatment->planting->plant) {
                        $plant = $expense->treatment->planting->plant;
                    } elseif ($expense->nutrient && $expense->nutrient->planting && $expense->nutrient->planting->plant) {
                        $plant = $expense->nutrient->planting->plant;
                    }
                    $total += $expense->amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td>{{ $expense->expense_date ? $expense->expense_date->format('d-m-Y') : '-' }}</td>
                    <td>{{ $expense->expense_name ?? '-' }}</td>
                    <td>{{ $expenseTypes[$expense->expense_type] ?? '-' }}</td>
                    <td>{{ $plant ? $plant->name : '-' }}</td>
                    <td>{{ $expense->plantingLocation->name ?? '-' }}</td>
                    <td>{{ $expense->responsiblePerson->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-right">Total:</th>
                <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>




















