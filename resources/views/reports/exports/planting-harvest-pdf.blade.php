<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Realisasi Tanam & Panen</title>
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
        <h2>LAPORAN REALISASI TANAM & PANEN</h2>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">KOMODITI</th>
                <th rowspan="2">KELAS BENIH</th>
                <th rowspan="2">VARIETAS</th>
                <th rowspan="2">LUAS (ha)</th>
                <th rowspan="2">LOKASI KEGIATAN</th>
                <th colspan="2">WAKTU</th>
                <th colspan="2">PRODUKSI (kg)</th>
            </tr>
            <tr>
                <th>TANAM</th>
                <th>PANEN</th>
                <th>CALON BENIH (kg)</th>
                <th>BENIH BERSERTIFIKAT</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowNumber = 1;
            @endphp
            @foreach($plantings as $planting)
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td>{{ $planting->plant->type->name ?? 'Lainnya' }}</td>
                    <td>{{ $planting->seed_class ?? '-' }}</td>
                    <td>{{ $planting->plant->name ?? '-' }}</td>
                    <td class="text-right">{{ $planting->area_ha > 0 ? number_format($planting->area_ha, 2, ',', '.') : '-' }}</td>
                    <td>{{ $planting->location->name ?? '-' }}</td>
                    <td class="text-center">{{ $planting->planted_at ? $planting->planted_at->format('d-m-Y') : '-' }}</td>
                    <td class="text-center">{{ $planting->harvest && $planting->harvest->harvested_at ? $planting->harvest->harvested_at->format('d-m-Y') : '-' }}</td>
                    <td class="text-right">{{ $planting->candidate_seed_kg > 0 ? number_format($planting->candidate_seed_kg, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $planting->certified_seed_kg > 0 ? number_format($planting->certified_seed_kg, 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>




















