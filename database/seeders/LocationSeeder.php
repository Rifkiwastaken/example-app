<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Lahan Tanam Utara',
                'city' => 'Padang',
                'district' => 'Koto Tangah',
                'type' => 'lokasi_lahan',
                'description' => 'Lahan tanam utama di area utara kota Padang',
                'google_maps_link' => 'https://maps.google.com/?q=-0.947083,100.416644',
            ],
            [
                'name' => 'Lahan Tanam Selatan',
                'city' => 'Padang',
                'district' => 'Lubuk Begalung',
                'type' => 'lokasi_lahan',
                'description' => 'Lahan tanam di area selatan kota Padang',
                'google_maps_link' => 'https://maps.google.com/?q=-0.967083,100.436644',
            ],
            [
                'name' => 'Kantor Sertifikasi Pusat',
                'city' => 'Padang',
                'district' => 'Padang Timur',
                'type' => 'lokasi_sertifikasi',
                'description' => 'Kantor utama untuk proses sertifikasi produk',
                'google_maps_link' => 'https://maps.google.com/?q=-0.947083,100.416644',
            ],
            [
                'name' => 'Gudang Penyimpanan Utama',
                'city' => 'Padang',
                'district' => 'Padang Barat',
                'type' => 'lokasi_gudang',
                'description' => 'Gudang utama untuk penyimpanan produk dan bahan baku',
                'google_maps_link' => 'https://maps.google.com/?q=-0.937083,100.406644',
            ],
            [
                'name' => 'Kantor Utama SIBIT',
                'city' => 'Padang',
                'district' => 'Padang Utara',
                'type' => 'lokasi_kantor_utama',
                'description' => 'Kantor pusat SIBIT untuk administrasi dan manajemen',
                'google_maps_link' => 'https://maps.google.com/?q=-0.927083,100.426644',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}















