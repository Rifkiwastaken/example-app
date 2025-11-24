<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantType;

class PlantTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plantTypes = [
            // Tanaman Pangan
            ['name' => 'Padi (Oryza sativa)', 'category' => 'Tanaman Pangan'],
            ['name' => 'Jagung (Zea mays)', 'category' => 'Tanaman Pangan'],
            ['name' => 'Kacang tanah (Arachis hypogaea)', 'category' => 'Tanaman Pangan'],
            ['name' => 'Kedelai (Glycine max)', 'category' => 'Tanaman Pangan'],
            ['name' => 'Kacang hijau (Vigna radiata)', 'category' => 'Tanaman Pangan'],
            ['name' => 'Kacang merah (Phaseolus vulgaris)', 'category' => 'Tanaman Pangan'],
            
            // Tanaman Sayuran
            ['name' => 'Bayam (Amaranthus sp.)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Kangkung (Ipomoea aquatica)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Sawi (Brassica juncea)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Tomat (Solanum lycopersicum)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Cabai (Capsicum annuum)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Wortel (Daucus carota)', 'category' => 'Tanaman Sayuran'],
            ['name' => 'Kol / Kubis (Brassica oleracea)', 'category' => 'Tanaman Sayuran'],
            
            // Tanaman Buah-buahan
            ['name' => 'Mangga (Mangifera indica)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Pisang (Musa paradisiaca)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Pepaya (Carica papaya)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Jeruk (Citrus sp.)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Semangka (Citrullus lanatus)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Nanas (Ananas comosus)', 'category' => 'Tanaman Buah-buahan'],
            ['name' => 'Jambu biji (Psidium guajava)', 'category' => 'Tanaman Buah-buahan'],
            
            // Tanaman Hias
            ['name' => 'Anggrek (Orchidaceae)', 'category' => 'Tanaman Hias'],
            ['name' => 'Mawar (Rosa sp.)', 'category' => 'Tanaman Hias'],
            ['name' => 'Melati (Jasminum sambac)', 'category' => 'Tanaman Hias'],
            ['name' => 'Kamboja (Plumeria sp.)', 'category' => 'Tanaman Hias'],
            ['name' => 'Lidah mertua (Sansevieria trifasciata)', 'category' => 'Tanaman Hias'],
            ['name' => 'Sri Rejeki (Aglaonema sp.)', 'category' => 'Tanaman Hias'],
            ['name' => 'Kaktus (Cactaceae)', 'category' => 'Tanaman Hias'],
        ];

        foreach ($plantTypes as $plantType) {
            PlantType::create($plantType);
        }
    }
}