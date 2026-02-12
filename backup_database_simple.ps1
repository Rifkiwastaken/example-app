# Script Backup Database SIBESTI - Versi Sederhana
# Menggunakan artisan untuk membaca konfigurasi

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Backup Database SIBESTI" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Cek apakah artisan ada
if (-not (Test-Path "artisan")) {
    Write-Host "ERROR: File artisan tidak ditemukan!" -ForegroundColor Red
    Write-Host "Pastikan Anda menjalankan script ini dari root direktori Laravel." -ForegroundColor Red
    exit 1
}

# Baca konfigurasi menggunakan artisan tinker
Write-Host "Membaca konfigurasi database..." -ForegroundColor Yellow

$dbConfig = php artisan tinker --execute="echo json_encode(['host' => config('database.connections.mysql.host'), 'port' => config('database.connections.mysql.port'), 'database' => config('database.connections.mysql.database'), 'username' => config('database.connections.mysql.username'), 'password' => config('database.connections.mysql.password')]);" 2>&1

# Parse JSON output (simplified - mungkin perlu penyesuaian)
# Alternatif: gunakan pendekatan langsung dengan mysqldump dan minta user input

Write-Host ""
Write-Host "Masukkan informasi database:" -ForegroundColor Yellow
$dbHost = Read-Host "Host (default: localhost)"
if ([string]::IsNullOrEmpty($dbHost)) { $dbHost = "localhost" }

$dbPort = Read-Host "Port (default: 3306)"
if ([string]::IsNullOrEmpty($dbPort)) { $dbPort = "3306" }

$dbName = Read-Host "Nama Database"
if ([string]::IsNullOrEmpty($dbName)) {
    Write-Host "ERROR: Nama database harus diisi!" -ForegroundColor Red
    exit 1
}

$dbUser = Read-Host "Username"
if ([string]::IsNullOrEmpty($dbUser)) {
    Write-Host "ERROR: Username harus diisi!" -ForegroundColor Red
    exit 1
}

$securePass = Read-Host "Password" -AsSecureString
$dbPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))

# Buat direktori backup jika belum ada
$backupDir = "database_backups"
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

# Buat nama file backup dengan timestamp
$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = Join-Path $backupDir "sibesti_backup_$timestamp.sql"

Write-Host ""
Write-Host "Database: $dbName" -ForegroundColor Yellow
Write-Host "Host: ${dbHost}:${dbPort}" -ForegroundColor Yellow
Write-Host "User: $dbUser" -ForegroundColor Yellow
Write-Host "File backup: $backupFile" -ForegroundColor Yellow
Write-Host ""
Write-Host "Memulai backup..." -ForegroundColor Green

# Jalankan mysqldump
$mysqldumpPath = "mysqldump"
$mysqldumpArgs = @(
    "-h", $dbHost,
    "-P", $dbPort,
    "-u", $dbUser,
    "-p$dbPass",
    "--single-transaction",
    "--routines",
    "--triggers",
    "--add-drop-table",
    $dbName
)

try {
    & $mysqldumpPath $mysqldumpArgs | Out-File -FilePath $backupFile -Encoding UTF8
    
    if ($LASTEXITCODE -eq 0 -and (Test-Path $backupFile)) {
        $fileSize = (Get-Item $backupFile).Length
        $fileSizeMB = [math]::Round($fileSize / 1MB, 2)
        
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "Backup berhasil dibuat!" -ForegroundColor Green
        Write-Host "File: $backupFile" -ForegroundColor Green
        Write-Host "Ukuran: $fileSizeMB MB" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        
        # Kompres file
        Write-Host ""
        Write-Host "Mengompres file backup..." -ForegroundColor Yellow
        $zipFile = "$backupFile.zip"
        Compress-Archive -Path $backupFile -DestinationPath $zipFile -Force
        
        if (Test-Path $zipFile) {
            $zipSize = (Get-Item $zipFile).Length
            $zipSizeMB = [math]::Round($zipSize / 1MB, 2)
            Remove-Item $backupFile
            Write-Host "File telah dikompres: $zipFile" -ForegroundColor Green
            Write-Host "Ukuran terkompres: $zipSizeMB MB" -ForegroundColor Green
        }
    } else {
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Red
        Write-Host "ERROR: Backup gagal!" -ForegroundColor Red
        Write-Host "Pastikan mysqldump terinstall dan dapat diakses." -ForegroundColor Red
        Write-Host "========================================" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Selesai!" -ForegroundColor Green
