<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "Creating admin user...\n";

try {
    // Check if admin already exists
    $existingAdmin = DB::table('users')->where('email', 'admin@sibit.com')->first();
    
    if ($existingAdmin) {
        echo "Admin user already exists!\n";
        echo "Email: admin@sibit.com\n";
        echo "User ID: " . $existingAdmin->user_id . "\n";
        exit(0);
    }
    
    // Insert user with auto-increment ID (database belum dimigrate ke custom ID)
    $userId = DB::table('users')->insertGetId([
        'name' => 'Admin SIBIT',
        'email' => 'admin@sibit.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'role' => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Admin user created successfully!\n\n";
    echo "Login credentials:\n";
    echo "Email: admin@sibit.com\n";
    echo "Password: password123\n";
    echo "User ID: $userId (BigInt - belum custom ID)\n";
    echo "\nNote: Database belum dimigrate ke custom ID.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
