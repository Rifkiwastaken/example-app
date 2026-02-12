# Script untuk setup development environment untuk testing migrasi
# SIBESTI - Custom ID Migration Testing
# PowerShell Version

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "SETUP DEVELOPMENT ENVIRONMENT" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Konfigurasi
$PROD_DB = "sibesti"
$DEV_DB = "sibesti_dev_migration_test"
$BACKUP_DIR = "database/backups"
$TIMESTAMP = Get-Date -Format "yyyyMMdd_HHmmss"

Write-Host "Step 1: Membuat backup database production..." -ForegroundColor Yellow
Write-Host "Database: $PROD_DB"
Write-Host "Backup akan disimpan di: $BACKUP_DIR"
Write-Host ""

# Buat direktori backup jika belum ada
if (!(Test-Path $BACKUP_DIR)) {
    New-Item -ItemType Directory -Path $BACKUP_DIR | Out-Null
}

# Input credentials
$MYSQL_USER = Read-Host "MySQL Username"
$MYSQL_PASS_SECURE = Read-Host "MySQL Password" -AsSecureString
$MYSQL_PASS = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($MYSQL_PASS_SECURE))

# Backup database production
$BACKUP_FILE = "$BACKUP_DIR/sibesti_before_migration_$TIMESTAMP.sql"
Write-Host "Membuat backup: $BACKUP_FILE"

# Gunakan mysqldump
$mysqldumpCmd = "mysqldump -u $MYSQL_USER -p$MYSQL_PASS $PROD_DB"
Invoke-Expression $mysqldumpCmd | Out-File -FilePath $BACKUP_FILE -Encoding UTF8

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Backup berhasil dibuat!" -ForegroundColor Green
    Write-Host "File: $BACKUP_FILE"
    $fileSize = (Get-Item $BACKUP_FILE).Length / 1MB
    Write-Host "Size: $([math]::Round($fileSize, 2)) MB"
} else {
    Write-Host "✗ Backup gagal!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2: Membuat database development..." -ForegroundColor Yellow

# Drop database development jika sudah ada
$dropCmd = "mysql -u $MYSQL_USER -p$MYSQL_PASS -e `"DROP DATABASE IF EXISTS $DEV_DB;`""
Invoke-Expression $dropCmd | Out-Null

# Buat database development baru
$createCmd = "mysql -u $MYSQL_USER -p$MYSQL_PASS -e `"CREATE DATABASE $DEV_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`""
Invoke-Expression $createCmd | Out-Null

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Database development berhasil dibuat!" -ForegroundColor Green
} else {
    Write-Host "✗ Gagal membuat database development!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 3: Clone data dari production ke development..." -ForegroundColor Yellow

# Import backup ke database development
$importCmd = "mysql -u $MYSQL_USER -p$MYSQL_PASS $DEV_DB"
Get-Content $BACKUP_FILE | & mysql -u $MYSQL_USER -p$MYSQL_PASS $DEV_DB

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Data berhasil di-clone ke development!" -ForegroundColor Green
} else {
    Write-Host "✗ Gagal clone data!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 4: Update .env untuk development testing..." -ForegroundColor Yellow

# Backup .env original
Copy-Item .env ".env.backup_$TIMESTAMP"
Write-Host "✓ .env original di-backup ke .env.backup_$TIMESTAMP" -ForegroundColor Green

# Baca APP_KEY dari .env original
$APP_KEY = (Get-Content .env | Select-String "APP_KEY=").ToString().Split("=")[1]

# Buat .env.dev untuk testing
$envDevContent = @"
APP_NAME=SIBESTI-DEV-MIGRATION-TEST
APP_ENV=local
APP_KEY=$APP_KEY
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DEV_DB
DB_USERNAME=$MYSQL_USER
DB_PASSWORD=$MYSQL_PASS

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="`${APP_NAME}"
"@

$envDevContent | Out-File -FilePath ".env.dev" -Encoding UTF8
Write-Host "✓ .env.dev berhasil dibuat!" -ForegroundColor Green

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "SETUP SELESAI!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Database Development: $DEV_DB"
Write-Host "Backup Production: $BACKUP_FILE"
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Gunakan .env.dev untuk testing:"
Write-Host "   Copy-Item .env.dev .env"
Write-Host ""
Write-Host "2. Jalankan migrasi fase 1-3 di development"
Write-Host ""
Write-Host "3. Test aplikasi dengan database development"
Write-Host ""
Write-Host "4. Jika berhasil, restore .env original:"
Write-Host "   Copy-Item .env.backup_$TIMESTAMP .env"
Write-Host ""
Write-Host "5. Jalankan migrasi di production"
Write-Host ""
Write-Host "PENTING: Jangan lupa restore .env setelah testing!" -ForegroundColor Red
Write-Host ""
