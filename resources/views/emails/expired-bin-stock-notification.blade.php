<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Benih Kadaluarsa - SIBESTI</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .danger-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .bin-box {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ef4444;
            border-radius: 4px;
        }
        .lot-item {
            padding: 8px;
            margin: 5px 0;
            background: #f9fafb;
            border-radius: 3px;
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
        <h1>🚨 Peringatan Benih Kadaluarsa</h1>
        <p>SIBESTI - Sistem Informasi Benih Bersertifikat</p>
    </div>
    
    <div class="content">
        <p>Halo <strong>{{ $userName }}</strong>,</p>
        
        <div class="danger-box">
            <p><strong>Peringatan!</strong> Terdapat benih yang sudah melewati masa kadaluarsa di <strong>{{ $totalBins }}</strong> bin gudang.</p>
        </div>
        
        <h3>Daftar Bin dengan Benih Kadaluarsa:</h3>
        
        @foreach($expiredBins as $bin)
        <div class="bin-box">
            <h4 style="margin-top: 0; color: #dc2626;">📍 {{ $bin['warehouse_name'] }}</h4>
            <p><strong>Bin:</strong> {{ $bin['bin_name'] }} ({{ $bin['bin_internal_id'] }})</p>
            <p><strong>Jumlah Lot Kadaluarsa:</strong> {{ $bin['expired_count'] }}</p>
            <p><strong>Total Stok Kadaluarsa:</strong> {{ number_format($bin['total_expired_stock'], 2) }} kg</p>
            
            <h5 style="margin-top: 15px;">Detail Lot:</h5>
            @foreach($bin['lots'] as $lot)
            <div class="lot-item">
                <strong>{{ $lot['inventory_type_name'] }}</strong> - {{ $lot['production_id'] }}<br>
                Stok: {{ number_format($lot['current_stock'], 2) }} {{ $lot['stock_unit'] }} | 
                Kadaluarsa: {{ $lot['expiry_date'] }} ({{ $lot['days_expired'] }} hari lalu)
            </div>
            @endforeach
        </div>
        @endforeach
        
        <p style="color: #6b7280; font-size: 14px;">
            Email ini dikirim secara otomatis dari sistem SIBESTI. Mohon segera lakukan pengecekan dan tindakan yang diperlukan.
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} SIBESTI - Sistem Informasi Benih Bersertifikat</p>
        <p>UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura</p>
    </div>
</body>
</html>



