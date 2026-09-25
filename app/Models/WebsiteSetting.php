<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $table = 'website_settings';

    protected $fillable = [
        'office_name',
        'tagline',
        'address',
        'phone',
        'whatsapp',
        'email',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'retribusi_note',
    ];

    public static function current(): self
    {
        $row = static::query()->first();
        if ($row) {
            return $row;
        }

        return static::create([
            'office_name' => 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura (BBI TPH) Provinsi Sumatera Barat',
            'tagline' => 'Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat',
            'address' => 'Jl. Pertanian, Lubuk Minturun, Kec. Koto Tangah, Kota Padang, Sumatera Barat 25586',
            'phone' => '(0751) 123456',
            'whatsapp' => '+62 812-3456-7890',
            'email' => 'info@bbitph.sumbar.go.id',
            'hero_title' => 'SIBESTI: Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat.',
            'hero_image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920',
            'retribusi_note' => 'Tarif retribusi mengikuti Peraturan Gubernur Sumatera Barat tentang Retribusi Daerah penjualan benih bersertifikat yang berlaku.',
        ]);
    }
}
