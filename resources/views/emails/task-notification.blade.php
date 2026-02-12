<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Baru - SIBESTI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .info-box {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #10b981;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Tugas Baru</h1>
        <p>SIBESTI - Sistem Informasi Benih Bersertifikat</p>
    </div>
    
    <div class="content">
        <p>Halo <strong>{{ $userName }}</strong>,</p>
        
        <p>Anda memiliki tugas baru yang perlu ditangani:</p>
        
        <div class="info-box">
            <h2 style="margin-top: 0; color: #059669;">{{ $taskTitle }}</h2>
            @if($taskDescription)
                <p>{{ $taskDescription }}</p>
            @endif
            
            <p><strong>📍 Lokasi:</strong> {{ $locationName }}</p>
            <p><strong>📅 Tenggat Waktu:</strong> {{ $dueDate }}@if($dueTime) pukul {{ $dueTime }}@endif</p>
            <p><strong>⚡ Prioritas:</strong> {{ ucfirst($priority) }}</p>
            <p><strong>📊 Status:</strong> {{ ucfirst(str_replace('_', ' ', $status)) }}</p>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $detailUrl }}" class="button">Lihat Detail Tugas</a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px;">
            Email ini dikirim secara otomatis dari sistem SIBESTI. Mohon jangan membalas email ini.
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} SIBESTI - Sistem Informasi Benih Bersertifikat</p>
        <p>UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura</p>
    </div>
</body>
</html>



