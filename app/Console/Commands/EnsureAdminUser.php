<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class EnsureAdminUser extends Command
{
    protected $signature = 'sibesti:ensure-admin 
                            {--password= : Password baru (default: password123)}';
    protected $description = 'Buat atau reset akun admin (admin@sibit.com) agar dapat login ke SIBESTI';

    public function handle(): int
    {
        $email = 'admin@sibit.com';
        $password = $this->option('password') ?: 'password123';

        // Jangan pakai Hash::make() di sini — model User punya cast 'hashed' untuk password
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin SIBIT',
                'password' => $password,
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );
        $user->rememberRevealablePassword($password);
        $user->save();

        $this->info('Akun admin siap dipakai.');
        $this->table(
            ['Email', 'Password (default)', 'Role'],
            [[$email, $password, $user->role]]
        );
        $this->newLine();
        $this->info('Gunakan kredensial di atas untuk login di halaman /login');

        return self::SUCCESS;
    }
}
