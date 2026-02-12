<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Status Sertifikasi</title>
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
        <h2>REKAP STATUS SERTIFIKASI</h2>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Komoditas/Tanaman</th>
                <th>Varietas</th>
                <th>Lokasi Lahan</th>
                <th>Kelas Benih Diminta</th>
                <th>Status Sertifikasi</th>
                <th>Tanggal Laporan Terakhir</th>
                <th>Kesimpulan Terakhir</th>
                <th>Jumlah Laporan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach($certifications as $certification)
                @php
                    $latestReport = $certification->reports->first();
                    $plant = $certification->plant ?? ($certification->harvest->plant ?? null);
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $plant ? $plant->name : '-' }}</td>
                    <td>{{ $plant && $plant->variety ? $plant->variety : '-' }}</td>
                    <td>
                        {{ $certification->plantingLocation ? $certification->plantingLocation->name : 
                            ($certification->harvest && $certification->harvest->location ? $certification->harvest->location->name : '-') }}
                    </td>
                    <td>{{ $certification->seed_class_requested ?? '-' }}</td>
                    <td>{{ $certification->status_label ?? '-' }}</td>
                    <td>{{ $latestReport && $latestReport->report_date ? $latestReport->report_date->format('d-m-Y') : '-' }}</td>
                    <td>{{ $latestReport && $latestReport->conclusion ? $latestReport->conclusion : '-' }}</td>
                    <td class="text-center">{{ $certification->reports->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



















