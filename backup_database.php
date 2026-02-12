<?php

/**
 * Script Backup Database SIBESTI
 * Menjalankan backup database menggunakan mysqldump
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Baca konfigurasi database
$connection = config('database.default');
$config = config("database.connections.{$connection}");

$host = $config['host'];
$port = $config['port'] ?? 3306;
$database = $config['database'];
$username = $config['username'];
$password = $config['password'];

// Buat nama file backup dengan timestamp
$timestamp = date('Y-m-d_His');
$backupDir = __DIR__ . '/database_backups';
$backupFile = "{$backupDir}/sibesti_backup_{$timestamp}.sql";

// Buat direktori backup jika belum ada
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Perintah mysqldump
$command = sprintf(
    'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
    escapeshellarg($host),
    escapeshellarg($port),
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($database),
    escapeshellarg($backupFile)
);

echo "Memulai backup database SIBESTI...\n";
echo "Database: {$database}\n";
echo "Host: {$host}:{$port}\n";
echo "File backup: {$backupFile}\n\n";

// Jalankan perintah backup
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    // Kompres file backup
    $compressedFile = $backupFile . '.gz';
    echo "Mengompres file backup...\n";
    
    $fp_in = fopen($backupFile, 'rb');
    $fp_out = gzopen($compressedFile, 'wb9');
    
    if ($fp_in && $fp_out) {
        while (!feof($fp_in)) {
            gzwrite($fp_out, fread($fp_in, 8192));
        }
        fclose($fp_in);
        gzclose($fp_out);
        
        // Hapus file SQL yang tidak terkompres
        unlink($backupFile);
        
        $fileSize = filesize($compressedFile);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);
        
        echo "\n✓ Backup berhasil dibuat!\n";
        echo "File: {$compressedFile}\n";
        echo "Ukuran: {$fileSizeMB} MB\n";
        echo "Waktu: " . date('Y-m-d H:i:s') . "\n";
    } else {
        echo "\n✓ Backup berhasil dibuat (tanpa kompresi)!\n";
        echo "File: {$backupFile}\n";
    }
} else {
    echo "\n✗ Error: Backup gagal!\n";
    echo "Return code: {$returnVar}\n";
    if (!empty($output)) {
        echo "Output: " . implode("\n", $output) . "\n";
    }
    exit(1);
}
