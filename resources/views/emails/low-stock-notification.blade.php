<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Stok Rendah - SIBESTI</title>
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
        .item-box {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
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
        <h1>⚠️ Peringatan Stok Rendah</h1>
        <p>SIBESTI - Sistem Informasi Benih Bersertifikat</p>
    </div>
    
    <div class="content">
        <p>Halo <strong>{{ $userName }}</strong>,</p>
        
        <div class="warning-box">
            <p><strong>Peringatan!</strong> Terdapat <strong>{{ $totalItems }}</strong> tipe benih yang stoknya sudah di bawah ambang batas peringatan stok rendah.</p>
        </div>
        
        <h3>Daftar Benih dengan Stok Rendah:</h3>
        
        @foreach($lowStockItems as $item)
        <div class="item-box">
            <h4 style="margin-top: 0; color: #d97706;">{{ $item['plant_name'] }}</h4>
            @if($item['variety'])
                <p><strong>Varietas:</strong> {{ $item['variety'] }}</p>
            @endif
            <p><strong>Stok Saat Ini:</strong> {{ number_format($item['current_stock'], 2) }} {{ $item['stock_unit'] }}</p>
            <p><strong>Ambang Batas:</strong> {{ number_format($item['threshold'], 2) }} {{ $item['threshold_unit'] }}</p>
            <p style="color: #dc2626;"><strong>Kekurangan:</strong> {{ number_format($item['threshold'] - $item['current_stock'], 2) }} {{ $item['stock_unit'] }}</p>
        </div>
        @endforeach
        
        <p style="color: #6b7280; font-size: 14px;">
            Email ini dikirim secara otomatis dari sistem SIBESTI. Mohon segera lakukan pengecekan dan pengisian ulang stok benih.
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} SIBESTI - Sistem Informasi Benih Bersertifikat</p>
        <p>UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura</p>
    </div>
</body>
</html>



