<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // LocationSeeder dihapus: tabel locations sudah di-drop oleh migrasi
            // TaskTemplateSeeder::class,
            // PlantTypeSeeder::class,
        ]);
    }
}
