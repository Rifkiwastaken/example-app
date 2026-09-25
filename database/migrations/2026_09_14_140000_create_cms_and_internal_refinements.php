<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_contents')) {
            Schema::create('website_contents', function (Blueprint $table) {
                $table->string('content_id', 36)->primary();
                $table->string('jenis', 50)->index();
                $table->string('kategori', 80)->nullable()->index();
                $table->string('judul', 255);
                $table->string('slug', 180)->unique();
                $table->text('excerpt')->nullable();
                $table->longText('body')->nullable();
                $table->string('cover_path', 255)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->string('author', 150)->nullable();
                $table->string('kip_label', 50)->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('profil_kelembagaan')) {
            Schema::create('profil_kelembagaan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('jabatan', 150);
                $table->string('nama_pejabat', 150);
                $table->string('nip', 30)->nullable();
                $table->string('foto_path', 255)->nullable();
                $table->text('tupoksi')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('website_settings')) {
            Schema::create('website_settings', function (Blueprint $table) {
                $table->id();
                $table->string('office_name', 200)->nullable();
                $table->string('tagline', 255)->nullable();
                $table->text('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('whatsapp', 50)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('facebook_url', 255)->nullable();
                $table->string('instagram_url', 255)->nullable();
                $table->string('youtube_url', 255)->nullable();
                $table->string('hero_title', 255)->nullable();
                $table->text('hero_subtitle')->nullable();
                $table->string('hero_image', 255)->nullable();
                $table->text('retribusi_note')->nullable();
                $table->timestamps();
            });

            DB::table('website_settings')->insert([
                'office_name' => 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura (BBI TPH) Provinsi Sumatera Barat',
                'tagline' => 'Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat',
                'address' => 'Jl. Pertanian, Lubuk Minturun, Kec. Koto Tangah, Kota Padang, Sumatera Barat 25586',
                'phone' => '(0751) 123456',
                'whatsapp' => '+62 812-3456-7890',
                'email' => 'info@bbitph.sumbar.go.id',
                'hero_title' => 'SIBESTI: Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat.',
                'hero_subtitle' => 'Layanan resmi UPTD BBI TPH untuk stok benih bersertifikat, permintaan benih, geowisata, dan magang.',
                'hero_image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920',
                'retribusi_note' => 'Tarif retribusi mengikuti Peraturan Gubernur Sumatera Barat tentang Retribusi Daerah penjualan benih bersertifikat yang berlaku.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('planting_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('planting_locations', 'province')) {
                $table->string('province', 100)->nullable()->after('location_summary');
            }
            if (! Schema::hasColumn('planting_locations', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
            if (! Schema::hasColumn('planting_locations', 'district')) {
                $table->string('district', 100)->nullable()->after('city');
            }
            if (! Schema::hasColumn('planting_locations', 'village')) {
                $table->string('village', 100)->nullable()->after('district');
            }
        });

        Schema::table('planting_fields', function (Blueprint $table) {
            if (! Schema::hasColumn('planting_fields', 'panjang_m')) {
                $table->decimal('panjang_m', 10, 2)->nullable()->after('luas_ha');
            }
            if (! Schema::hasColumn('planting_fields', 'lebar_m')) {
                $table->decimal('lebar_m', 10, 2)->nullable()->after('panjang_m');
            }
        });

        Schema::table('plant_seed_source', function (Blueprint $table) {
            if (! Schema::hasColumn('plant_seed_source', 'quantity_awal')) {
                $table->decimal('quantity_awal', 12, 2)->nullable()->after('quantity_kg');
            }
        });

        if (Schema::hasColumn('plant_seed_source', 'quantity_awal')) {
            DB::table('plant_seed_source')->whereNull('quantity_awal')->update([
                'quantity_awal' => DB::raw('quantity_kg'),
            ]);
        }

        if (Schema::hasColumn('plant_seed_source', 'seed_class')) {
            DB::statement("ALTER TABLE `plant_seed_source` MODIFY COLUMN `seed_class` VARCHAR(10) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_contents');
        Schema::dropIfExists('profil_kelembagaan');
        Schema::dropIfExists('website_settings');
    }
};
