<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class BackupAndMigrateFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-keep-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate fresh but keep admin SIBIT data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migrate fresh while preserving admin SIBIT data...');
        
        // Step 1: Backup admin SIBIT data
        $this->info('Step 1: Backing up admin SIBIT data...');
        $adminData = User::where('email', 'admin@sibit.com')->first();
        
        if (!$adminData) {
            $this->warn('Admin SIBIT not found in database. Will create new one after migrate.');
            $adminData = null;
        } else {
            $this->info('Admin SIBIT found: ' . $adminData->name);
            // Store all attributes
            $adminBackup = [
                'name' => $adminData->name,
                'email' => $adminData->email,
                'password' => $adminData->password, // Keep hashed password
                'email_verified_at' => $adminData->email_verified_at,
                'role' => $adminData->role,
                'location_id' => $adminData->location_id,
                'location_placement' => $adminData->location_placement,
                'photo_path' => $adminData->photo_path,
                'full_name' => $adminData->full_name,
                'status' => $adminData->status,
                'contact_type' => $adminData->contact_type,
                'organization' => $adminData->organization,
                'position' => $adminData->position,
                'nip' => $adminData->nip,
                'primary_phone' => $adminData->primary_phone,
                'primary_phone_is_whatsapp' => $adminData->primary_phone_is_whatsapp,
                'secondary_phone' => $adminData->secondary_phone,
                'address' => $adminData->address,
                'province' => $adminData->province,
                'city' => $adminData->city,
                'district' => $adminData->district,
                'village' => $adminData->village,
                'notes' => $adminData->notes,
                'remember_token' => $adminData->remember_token,
                'created_at' => $adminData->created_at,
                'updated_at' => $adminData->updated_at,
            ];
        }
        
        // Step 2: Run migrate fresh
        $this->info('Step 2: Running migrate fresh...');
        $this->call('migrate:fresh', [
            '--force' => true,
        ]);
        
        // Step 3: Restore admin SIBIT data
        if ($adminData) {
            $this->info('Step 3: Restoring admin SIBIT data...');
            try {
                User::create($adminBackup);
                $this->info('✓ Admin SIBIT data restored successfully!');
            } catch (\Exception $e) {
                $this->error('Failed to restore admin SIBIT data: ' . $e->getMessage());
                $this->warn('You may need to run UserSeeder manually: php artisan db:seed --class=UserSeeder');
                return 1;
            }
        } else {
            $this->info('Step 3: Creating new admin SIBIT from seeder...');
            $this->call('db:seed', [
                '--class' => 'UserSeeder',
                '--force' => true,
            ]);
        }
        
        $this->info('');
        $this->info('✓ Migrate fresh completed successfully!');
        $this->info('Admin SIBIT data has been preserved.');
        
        return 0;
    }
}
