<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaskTemplate;

class TaskTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Template Penanaman Rutin',
                'description' => 'Template untuk kegiatan penanaman rutin harian',
                'association' => 'penanaman',
                'tasks_list' => [
                    [
                        'title' => 'Persiapan Lahan',
                        'description' => 'Membersihkan dan menyiapkan lahan untuk penanaman',
                    ],
                    [
                        'title' => 'Penanaman Bibit',
                        'description' => 'Menanam bibit sesuai jadwal dan standar',
                    ],
                    [
                        'title' => 'Pemeliharaan Harian',
                        'description' => 'Menyiram dan memeriksa kondisi tanaman',
                    ],
                ],
            ],
            [
                'name' => 'Template Sertifikasi Produk',
                'description' => 'Template untuk proses sertifikasi produk',
                'association' => 'sertifikasi',
                'tasks_list' => [
                    [
                        'title' => 'Pengambilan Sample',
                        'description' => 'Mengambil sample produk untuk testing',
                    ],
                    [
                        'title' => 'Testing Laboratorium',
                        'description' => 'Melakukan testing kualitas di laboratorium',
                    ],
                    [
                        'title' => 'Penerbitan Sertifikat',
                        'description' => 'Menerbitkan sertifikat hasil testing',
                    ],
                ],
            ],
            [
                'name' => 'Template Manajemen Gudang',
                'description' => 'Template untuk kegiatan manajemen gudang',
                'association' => 'gudang',
                'tasks_list' => [
                    [
                        'title' => 'Penerimaan Barang',
                        'description' => 'Menerima dan mencatat barang masuk',
                    ],
                    [
                        'title' => 'Penyimpanan Barang',
                        'description' => 'Menyimpan barang sesuai kategori dan standar',
                    ],
                    [
                        'title' => 'Pencatatan Stok',
                        'description' => 'Mencatat dan update stok barang',
                    ],
                ],
            ],
            [
                'name' => 'Template Penjualan BBI',
                'description' => 'Template untuk kegiatan penjualan bibit',
                'association' => 'penjualan',
                'tasks_list' => [
                    [
                        'title' => 'Persiapan Bibit',
                        'description' => 'Menyiapkan bibit untuk dijual',
                    ],
                    [
                        'title' => 'Penjualan ke Pelanggan',
                        'description' => 'Melayani pelanggan dan melakukan penjualan',
                    ],
                    [
                        'title' => 'Pencatatan Penjualan',
                        'description' => 'Mencatat transaksi penjualan',
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            TaskTemplate::create($template);
        }
    }
}


















