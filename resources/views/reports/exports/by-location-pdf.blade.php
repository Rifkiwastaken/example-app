<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Per Lokasi Lahan - {{ $plantingLocation->name }}</title>
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
        <h2>LAPORAN PER LOKASI LAHAN</h2>
        <h3>{{ $plantingLocation->name }}</h3>
        <p>Tanggal: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Data</th>
                <th>Tanggal</th>
                <th>Judul/Nama</th>
                <th>Deskripsi/Detail</th>
                <th>Penanggung Jawab</th>
                <th>Status</th>
                <th>Biaya/Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allData = collect();
                $rowNumber = 1;
            @endphp
            
            @foreach($plantings as $planting)
                @php
                    $harvest = $planting->harvest;
                    $allData->push([
                        'type' => 'Penanaman',
                        'date' => $planting->planted_at,
                        'title' => $planting->plant->name ?? '-',
                        'description' => 'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : ''),
                        'responsible' => '-',
                        'status' => $harvest && $harvest->quantity > 0 ? 'Berhasil' : ($harvest ? 'Gagal' : 'Belum Panen'),
                        'amount' => null,
                    ]);
                @endphp
            @endforeach
            
            @foreach($treatments as $treatment)
                @php
                    $allData->push([
                        'type' => 'Perawatan',
                        'date' => $treatment->treatment_date,
                        'title' => $treatment->treatment_name ?? '-',
                        'description' => 'Tipe: ' . ($treatment->treatment_type ?? '-') . ' | Metode: ' . ($treatment->application_method ?? '-'),
                        'responsible' => $treatment->responsiblePerson->name ?? '-',
                        'status' => '-',
                        'amount' => $treatment->total_cost ?? 0,
                    ]);
                @endphp
            @endforeach
            
            @foreach($nutrients as $nutrient)
                @php
                    $allData->push([
                        'type' => 'Nutrisi',
                        'date' => $nutrient->application_date,
                        'title' => $nutrient->product_applied ?? '-',
                        'description' => 'Metode: ' . ($nutrient->application_method ?? '-') . ' | Jumlah: ' . ($nutrient->amount_applied ?? '-') . ' ' . ($nutrient->unit ?? ''),
                        'responsible' => $nutrient->responsiblePerson->name ?? '-',
                        'status' => '-',
                        'amount' => $nutrient->total_cost ?? 0,
                    ]);
                @endphp
            @endforeach
            
            @foreach($tasks as $task)
                @php
                    $allData->push([
                        'type' => 'Tugas',
                        'date' => $task->due_date,
                        'title' => $task->title ?? '-',
                        'description' => Str::limit($task->description ?? '-', 100),
                        'responsible' => $task->assignedUser->name ?? '-',
                        'status' => $task->new_status === 'selesai' ? 'Selesai' : ($task->new_status === 'dalam_progress' ? 'Dalam Progress' : 'Belum Selesai'),
                        'amount' => null,
                    ]);
                @endphp
            @endforeach
            
            @foreach($notes as $note)
                @php
                    $allData->push([
                        'type' => 'Catatan',
                        'date' => $note->note_date,
                        'title' => $note->title ?? 'Catatan',
                        'description' => Str::limit($note->description ?? '-', 100),
                        'responsible' => $note->user->name ?? '-',
                        'status' => '-',
                        'amount' => null,
                    ]);
                @endphp
            @endforeach
            
            @foreach($attachments as $attachment)
                @php
                    $allData->push([
                        'type' => 'Lampiran',
                        'date' => $attachment->attachment_date,
                        'title' => $attachment->title ?? '-',
                        'description' => Str::limit($attachment->description ?? '-', 100),
                        'responsible' => $attachment->creator->name ?? '-',
                        'status' => '-',
                        'amount' => null,
                    ]);
                @endphp
            @endforeach
            
            @php
                $allData = $allData->sortByDesc('date');
            @endphp
            
            @foreach($allData as $item)
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d-m-Y') : '-' }}</td>
                    <td>{{ $item['title'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['responsible'] }}</td>
                    <td>{{ $item['status'] }}</td>
                    <td class="text-right">
                        @if($item['amount'] !== null && $item['amount'] > 0)
                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-right">Total Pengeluaran:</th>
                <th class="text-right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</th>
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




















