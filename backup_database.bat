@echo off
REM Script Backup Database SIBESTI untuk Windows
REM Menggunakan mysqldump

echo ========================================
echo Backup Database SIBESTI
echo ========================================
echo.

REM Baca konfigurasi dari .env (sederhana)
for /f "tokens=2 delims==" %%a in ('findstr "DB_DATABASE" .env') do set DB_NAME=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_USERNAME" .env') do set DB_USER=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_PASSWORD" .env') do set DB_PASS=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_HOST" .env') do set DB_HOST=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_PORT" .env') do set DB_PORT=%%a

REM Hapus tanda petik jika ada
set DB_NAME=%DB_NAME:"=%
set DB_USER=%DB_USER:"=%
set DB_PASS=%DB_PASS:"=%
set DB_HOST=%DB_HOST:"=%
set DB_PORT=%DB_PORT:"=%

if "%DB_PORT%"=="" set DB_PORT=3306

REM Buat direktori backup jika belum ada
if not exist "database_backups" mkdir database_backups

REM Buat nama file backup dengan timestamp
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do set mydate=%%c-%%a-%%b
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do set mytime=%%a%%b
set mytime=%mytime: =0%
set timestamp=%mydate%_%mytime%
set backup_file=database_backups\sibesti_backup_%timestamp%.sql

echo Database: %DB_NAME%
echo Host: %DB_HOST%:%DB_PORT%
echo User: %DB_USER%
echo File backup: %backup_file%
echo.
echo Memulai backup...

REM Jalankan mysqldump
mysqldump -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -p%DB_PASS% --single-transaction --routines --triggers %DB_NAME% > %backup_file%

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Backup berhasil dibuat!
    echo File: %backup_file%
    echo ========================================
    
    REM Kompres file menggunakan PowerShell
    powershell -Command "Compress-Archive -Path '%backup_file%' -DestinationPath '%backup_file%.zip' -Force"
    if %errorlevel% equ 0 (
        del %backup_file%
        echo File telah dikompres: %backup_file%.zip
    )
) else (
    echo.
    echo ========================================
    echo ERROR: Backup gagal!
    echo ========================================
    pause
    exit /b 1
)

echo.
pause
