<?php

/**
 * Create Admin Account for SIBESTI
 * Run: php create_admin_account.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "Creating Admin Account for SIBESTI\n";
echo "===========================================\n\n";

try {
    // Check if admin already exists
    $existingAdmin = User::where('email', 'admin@sibesti.com')->first();
    
    if ($existingAdmin) {
        echo "⚠ Admin account already exists!\n";
        echo "Email: admin@sibesti.com\n";
        echo "User ID: {$existingAdmin->user_id}\n";
        echo "Name: {$existingAdmin->name}\n\n";
        echo "Do you want to reset the password? (This will delete and recreate)\n";
        exit(0);
    }
    
    // Generate custom IDs
    $adminId = 'USR-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    $penangkarId = 'USR-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    $petugasId = 'USR-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    
    // Create admin user using DB::insert
    DB::table('users')->insert([
        'user_id' => $adminId,
        'name' => 'Administrator',
        'email' => 'admin@sibesti.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'address' => 'Kantor SIBESTI',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Admin account created successfully!\n\n";
    echo "===========================================\n";
    echo "LOGIN CREDENTIALS\n";
    echo "===========================================\n";
    echo "User ID    : {$adminId}\n";
    echo "Email      : admin@sibesti.com\n";
    echo "Password   : admin123\n";
    echo "Role       : admin\n";
    echo "Name       : Administrator\n";
    echo "===========================================\n\n";
    
    echo "⚠ IMPORTANT: Please change the password after first login!\n\n";
    
    // Create additional test users
    echo "Creating additional test users...\n\n";
    
    DB::table('users')->insert([
        'user_id' => $penangkarId,
        'name' => 'Penangkar Test',
        'email' => 'penangkar@sibesti.com',
        'password' => Hash::make('penangkar123'),
        'role' => 'penangkar',
        'address' => 'Lokasi Penangkar',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Penangkar account created!\n";
    echo "   Email: penangkar@sibesti.com\n";
    echo "   Password: penangkar123\n";
    echo "   User ID: {$penangkarId}\n\n";
    
    DB::table('users')->insert([
        'user_id' => $petugasId,
        'name' => 'Staff Test',
        'email' => 'staff@sibesti.com',
        'password' => Hash::make('staff123'),
        'role' => 'staff',
        'address' => 'Kantor Staff',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Staff account created!\n";
    echo "   Email: staff@sibesti.com\n";
    echo "   Password: staff123\n";
    echo "   User ID: {$petugasId}\n\n";
    
    echo "===========================================\n";
    echo "SUMMARY\n";
    echo "===========================================\n";
    echo "Total users created: 3\n";
    echo "- Admin (admin@sibesti.com)\n";
    echo "- Penangkar (penangkar@sibesti.com)\n";
    echo "- Staff (staff@sibesti.com)\n";
    echo "===========================================\n\n";
    
    echo "✅ All accounts created successfully!\n";
    echo "You can now login to the application.\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
