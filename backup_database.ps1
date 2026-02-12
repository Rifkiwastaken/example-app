# Script Backup Database SIBESTI untuk PowerShell
# Menggunakan mysqldump

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Backup Database SIBESTI" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Baca konfigurasi dari .env
$envContent = Get-Content .env -ErrorAction SilentlyContinue
if (-not $envContent) {
    Write-Host "ERROR: File .env tidak ditemukan!" -ForegroundColor Red
    exit 1
}

$dbName = ($envContent | Select-String "DB_DATABASE").ToString().Split('=')[1].Trim().Trim('"')
$dbUser = ($envContent | Select-String "DB_USERNAME").ToString().Split('=')[1].Trim().Trim('"')
$dbPass = ($envContent | Select-String "DB_PASSWORD").ToString().Split('=')[1].Trim().Trim('"')
$dbHost = ($envContent | Select-String "DB_HOST").ToString().Split('=')[1].Trim().Trim('"')
$dbPort = ($envContent | Select-String "DB_PORT").ToString().Split('=')[1].Trim().Trim('"')

if ([string]::IsNullOrEmpty($dbPort)) {
    $dbPort = "3306"
}

# Buat direktori backup jika belum ada
$backupDir = "database_backups"
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

# Buat nama file backup dengan timestamp
$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = Join-Path $backupDir "sibesti_backup_$timestamp.sql"

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
