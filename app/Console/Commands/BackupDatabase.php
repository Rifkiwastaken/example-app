<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--compress : Kompres file backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat backup database SIBESTI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('Backup Database SIBESTI');
        $this->info('========================================');
        $this->newLine();

        // Baca konfigurasi database
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $this->info("Database: {$database}");
        $this->info("Host: {$host}:{$port}");
        $this->info("User: {$username}");
        $this->newLine();

        // Buat direktori backup jika belum ada
        $backupDir = database_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Buat nama file backup dengan timestamp
        $timestamp = date('Y-m-d_His');
        $backupFile = "{$backupDir}/sibesti_backup_{$timestamp}.sql";

        $this->info("Memulai backup...");
        $this->info("File backup: {$backupFile}");
        $this->newLine();

        // Cari mysqldump di lokasi umum
        $mysqldumpPath = 'mysqldump';
        $commonPaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
        ];

        // Cek apakah mysqldump tersedia di PATH
        exec('where mysqldump 2>nul', $mysqldumpCheck, $checkReturn);
        if ($checkReturn === 0 && !empty($mysqldumpCheck)) {
            $mysqldumpPath = trim($mysqldumpCheck[0]);
        } else {
            // Coba cari di lokasi umum
            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    $mysqldumpPath = $path;
                    break;
                }
            }
            
            if ($mysqldumpPath === 'mysqldump' && !file_exists($mysqldumpPath)) {
                $this->warn("mysqldump tidak ditemukan di PATH atau lokasi umum.");
                $this->warn("Menggunakan metode backup alternatif menggunakan Laravel DB...");
                return $this->backupUsingLaravel($config, $backupFile);
            }
        }

        // Perintah mysqldump
        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table %s > %s 2>&1',
            $mysqldumpPath,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        // Jalankan backup
        exec($command, $output, $returnVar);

        // Debug: tampilkan output jika ada error
        if ($returnVar !== 0 && !empty($output)) {
            $this->warn("Output mysqldump:");
            foreach ($output as $line) {
                $this->line($line);
            }
        }

        // Jika mysqldump gagal, coba metode alternatif
        if ($returnVar !== 0 || !file_exists($backupFile)) {
            $this->warn("mysqldump gagal, mencoba metode alternatif...");
            return $this->backupUsingLaravel($config, $backupFile);
        }

        if (file_exists($backupFile)) {
            $fileSize = filesize($backupFile);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            $this->info("✓ Backup berhasil dibuat!");
            $this->info("File: {$backupFile}");
            $this->info("Ukuran: {$fileSizeMB} MB");
            $this->newLine();

            // Kompres jika diminta
            if ($this->option('compress')) {
                $this->info("Mengompres file backup...");
                $compressedFile = $backupFile . '.gz';
                
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
                    
                    $compressedSize = filesize($compressedFile);
                    $compressedSizeMB = round($compressedSize / 1024 / 1024, 2);
                    
                    $this->info("✓ File telah dikompres!");
                    $this->info("File: {$compressedFile}");
                    $this->info("Ukuran terkompres: {$compressedSizeMB} MB");
                } else {
                    $this->warn("Gagal mengompres file, file SQL tetap tersedia.");
                }
            }

            $this->newLine();
            $this->info("Selesai!");
        } else {
            $this->error("✗ Backup gagal!");
            if (!empty($output)) {
                $this->error("Error: " . implode("\n", $output));
            }
            return 1;
        }

        return 0;
    }

    /**
     * Backup menggunakan Laravel DB (alternatif jika mysqldump tidak tersedia)
     */
    private function backupUsingLaravel($config, $backupFile)
    {
        $this->warn("Menggunakan metode backup alternatif...");
        $this->warn("Catatan: Metode ini mungkin lebih lambat dan tidak mencakup stored procedures/triggers.");
        
        try {
            $fp = fopen($backupFile, 'w');
            
            // Header SQL
            fwrite($fp, "-- Backup Database SIBESTI\n");
            fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($fp, "-- Database: {$config['database']}\n\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");
            
            // Dapatkan semua tabel
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $config['database'];
            
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $this->line("Backing up table: {$tableName}");
                
                // CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createTableSql = $createTable[0]->{'Create Table'};
                fwrite($fp, "-- Table structure for `{$tableName}`\n");
                fwrite($fp, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($fp, $createTableSql . ";\n\n");
                
                // Data
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    fwrite($fp, "-- Data for table `{$tableName}`\n");
                    fwrite($fp, "LOCK TABLES `{$tableName}` WRITE;\n");
                    
                    foreach ($rows->chunk(100) as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $rowArray = (array) $row;
                            $escapedValues = array_map(function($value) {
                                if ($value === null) return 'NULL';
                                return "'" . addslashes($value) . "'";
                            }, array_values($rowArray));
                            $values[] = '(' . implode(',', $escapedValues) . ')';
                        }
                        
                        if (!empty($values)) {
                            $columns = '`' . implode('`,`', array_keys((array) $rows->first())) . '`';
                            fwrite($fp, "INSERT INTO `{$tableName}` ({$columns}) VALUES\n");
                            fwrite($fp, implode(",\n", $values) . ";\n");
                        }
                    }
                    
                    fwrite($fp, "UNLOCK TABLES;\n\n");
                }
            }
            
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($fp);
            
            $fileSize = filesize($backupFile);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);
            
            $this->info("✓ Backup berhasil dibuat!");
            $this->info("File: {$backupFile}");
            $this->info("Ukuran: {$fileSizeMB} MB");
            
            if ($this->option('compress')) {
                $this->compressBackup($backupFile);
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Backup gagal: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Kompres file backup
     */
    private function compressBackup($backupFile)
    {
        $this->info("Mengompres file backup...");
        $compressedFile = $backupFile . '.gz';
        
        $fp_in = fopen($backupFile, 'rb');
        $fp_out = gzopen($compressedFile, 'wb9');
        
        if ($fp_in && $fp_out) {
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 8192));
            }
            fclose($fp_in);
            gzclose($fp_out);
            
            unlink($backupFile);
            
            $compressedSize = filesize($compressedFile);
            $compressedSizeMB = round($compressedSize / 1024 / 1024, 2);
            
            $this->info("✓ File telah dikompres!");
            $this->info("File: {$compressedFile}");
            $this->info("Ukuran terkompres: {$compressedSizeMB} MB");
        }
    }
}
