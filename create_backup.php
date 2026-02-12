<?php
/**
 * Script untuk backup database menggunakan Laravel DB connection
 * Alternatif jika mysqldump tidak tersedia
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "==========================================\n";
echo "BACKUP DATABASE SIBESTI\n";
echo "==========================================\n\n";

$dbName = Config::get('database.connections.mysql.database');
$backupDir = 'database/backups';
$timestamp = date('Ymd_His');
$backupFile = "{$backupDir}/sibesti_backup_before_migration_{$timestamp}.sql";

echo "Database: {$dbName}\n";
echo "Backup file: {$backupFile}\n\n";

// Buat direktori jika belum ada
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

try {
    echo "Mengambil daftar tabel...\n";
    $tables = DB::select('SHOW TABLES');
    $tableKey = "Tables_in_{$dbName}";
    
    $sql = "-- Backup Database: {$dbName}\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        $tableName = $table->$tableKey;
        echo "Backing up table: {$tableName}...\n";
        
        // Get CREATE TABLE statement
        $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
        $sql .= "-- Table: {$tableName}\n";
        $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
        
        // Get table data
        $rows = DB::table($tableName)->get();
        
        if ($rows->count() > 0) {
            $sql .= "-- Data for table: {$tableName}\n";
            
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                
                $columns = implode('`, `', array_keys((array)$row));
                $valuesStr = implode(', ', $values);
                $sql .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES ({$valuesStr});\n";
            }
            
            $sql .= "\n";
        }
    }
    
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Simpan ke file
    file_put_contents($backupFile, $sql);
    
    $fileSize = filesize($backupFile);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    
    echo "\n==========================================\n";
    echo "✓ BACKUP BERHASIL!\n";
    echo "==========================================\n";
    echo "File: {$backupFile}\n";
    echo "Size: {$fileSizeMB} MB\n";
    echo "Tables: " . count($tables) . "\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n==========================================\n";
    echo "✗ ERROR: Backup gagal!\n";
    echo "==========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
