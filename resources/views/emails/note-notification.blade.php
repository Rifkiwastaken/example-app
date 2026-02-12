<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Baru - SIBESTI</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #f59e0b;
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
        <h1>📝 Catatan Baru</h1>
        <p>SIBESTI - Sistem Informasi Benih Bersertifikat</p>
    </div>
    
    <div class="content">
        <p>Halo <strong>{{ $userName }}</strong>,</p>
        
        <p>Ada catatan baru yang perlu Anda perhatikan:</p>
        
        <div class="info-box">
            <h2 style="margin-top: 0; color: #d97706;">{{ $noteTitle }}</h2>
            @if($noteContent)
                <p>{{ $noteContent }}</p>
            @endif
            
            <p><strong>📍 Lokasi:</strong> {{ $locationName }}</p>
            <p><strong>🕐 Dibuat:</strong> {{ $createdAt }}</p>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $detailUrl }}" class="button">Lihat Detail Catatan</a>
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



