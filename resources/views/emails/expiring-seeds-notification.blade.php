<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Benih Mendekati Kadaluarsa - SIBESTI</title>
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
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .danger-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .seed-box {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .seed-box.expired {
            border-left-color: #ef4444;
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
        <h1>⏰ Peringatan Benih Mendekati/Melahwati Kadaluarsa</h1>
        <p>SIBESTI - Sistem Informasi Benih Bersertifikat</p>
    </div>
    
    <div class="content">
        <p>Halo <strong>Admin</strong>,</p>
        
        @if($expiredCount > 0)
        <div class="danger-box">
            <p><strong>Peringatan!</strong> Terdapat <strong>{{ $expiredCount }}</strong> benih yang sudah melewati masa kadaluarsa dan perlu segera dilakukan sertifikasi ulang.</p>
        </div>
        @endif
        
        @if($nearExpiryCount > 0)
        <div class="warning-box">
            <p><strong>Perhatian!</strong> Terdapat <strong>{{ $nearExpiryCount }}</strong> benih yang akan mendekati masa kadaluarsa dalam 14 hari ke depan (H-14).</p>
        </div>
        @endif
        
        <h3>Daftar Benih yang Perlu Sertifikasi Ulang:</h3>
        
        @foreach($expiringSeeds as $seed)
        <div class="seed-box {{ $seed['is_expired'] ? 'expired' : '' }}">
            <h4 style="margin-top: 0; color: {{ $seed['is_expired'] ? '#dc2626' : '#d97706' }};">
                {{ $seed['name'] }}
                @if($seed['is_expired'])
                    <span style="color: #dc2626;">(KADALUARSA)</span>
                @else
                    <span style="color: #d97706;">(Mendekati Kadaluarsa)</span>
                @endif
            </h4>
            @if($seed['variety'])
                <p><strong>Varietas:</strong> {{ $seed['variety'] }}</p>
            @endif
            <p><strong>Batch/Lot Number:</strong> {{ $seed['batch_no'] }}</p>
            <p><strong>Lokasi:</strong> {{ $seed['location'] }}</p>
            <p><strong>Tanggal Kadaluarsa:</strong> {{ $seed['expiry_date'] }}</p>
            @if($seed['is_expired'])
                <p style="color: #dc2626;"><strong>Status:</strong> Sudah kadaluarsa sejak {{ $seed['days_until'] }} hari lalu</p>
            @else
                <p style="color: #d97706;"><strong>Status:</strong> Akan kadaluarsa dalam {{ $seed['days_until'] }} hari</p>
            @endif
            <p><strong>Stok:</strong> {{ number_format($seed['stock_quantity'], 2) }} {{ $seed['stock_unit'] }}</p>
        </div>
        @endforeach
        
        <div style="background: #eff6ff; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <p><strong>📋 Tindakan yang Diperlukan:</strong></p>
            <ul>
                <li>Lakukan pengecekan terhadap benih-benih di atas</li>
                <li>Untuk benih yang sudah kadaluarsa, segera lakukan proses sertifikasi ulang</li>
                <li>Untuk benih yang mendekati kadaluarsa (H-14), persiapkan dokumen untuk sertifikasi ulang</li>
            </ul>
        </div>
        
        <p style="color: #6b7280; font-size: 14px;">
            Email ini dikirim secara otomatis dari sistem SIBESTI. Mohon segera lakukan tindakan yang diperlukan.
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} SIBESTI - Sistem Informasi Benih Bersertifikat</p>
        <p>UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura</p>
    </div>
</body>
</html>

