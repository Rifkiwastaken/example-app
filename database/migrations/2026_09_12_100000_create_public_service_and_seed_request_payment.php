<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_geowisata')) {
            Schema::create('booking_geowisata', function (Blueprint $table) {
                $table->id();
                $table->string('kode_booking', 20)->unique();
                $table->string('nama_lembaga', 255);
                $table->unsignedInteger('jumlah_peserta');
                $table->date('tgl_kunjungan');
                $table->string('nama_pj', 150);
                $table->string('no_whatsapp_pj', 20);
                $table->enum('status_kedatangan', ['Booked', 'Hadir', 'Batal'])->default('Booked');
                $table->string('status_pengajuan', 30)->default('menunggu_review');
                $table->text('alasan_ditolak')->nullable();
                $table->string('processed_by', 36)->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index('tgl_kunjungan');
            });
        }

        if (! Schema::hasTable('pendaftaran_magang')) {
            Schema::create('pendaftaran_magang', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_registrasi', 50)->unique();
                $table->string('nama_lengkap', 150);
                $table->string('institusi_asal', 150);
                $table->string('jurusan', 100);
                $table->string('no_whatsapp', 20);
                $table->date('tgl_mulai');
                $table->date('tgl_selesai');
                $table->enum('status_magang', ['Menunggu Review', 'Diterima', 'Ditolak'])->default('Menunggu Review');
                $table->text('alasan_ditolak')->nullable();
                $table->string('processed_by', 36)->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('pendaftaran_magang_peserta')) {
            Schema::create('pendaftaran_magang_peserta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pendaftaran_magang_id');
                $table->string('nama_lengkap', 150);
                $table->string('identitas', 50)->nullable();
                $table->string('jurusan', 100)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('pendaftaran_magang_id')
                    ->references('id')
                    ->on('pendaftaran_magang')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('seed_requests')) {
            Schema::table('seed_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('seed_requests', 'payment_method')) {
                    $table->string('payment_method', 30)->nullable()->after('notes');
                }
                if (! Schema::hasColumn('seed_requests', 'payment_proof')) {
                    $table->string('payment_proof')->nullable()->after('payment_method');
                }
                if (! Schema::hasColumn('seed_requests', 'total_amount')) {
                    $table->decimal('total_amount', 15, 2)->nullable()->after('payment_proof');
                }
            });
        }

        if (Schema::hasTable('seed_request_items')) {
            Schema::table('seed_request_items', function (Blueprint $table) {
                if (! Schema::hasColumn('seed_request_items', 'unit_price')) {
                    $table->decimal('unit_price', 15, 2)->nullable()->after('unit');
                }
                if (! Schema::hasColumn('seed_request_items', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->nullable()->after('unit_price');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_magang_peserta');
        Schema::dropIfExists('pendaftaran_magang');
        Schema::dropIfExists('booking_geowisata');

        if (Schema::hasTable('seed_requests')) {
            Schema::table('seed_requests', function (Blueprint $table) {
                foreach (['payment_method', 'payment_proof', 'total_amount'] as $column) {
                    if (Schema::hasColumn('seed_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('seed_request_items')) {
            Schema::table('seed_request_items', function (Blueprint $table) {
                foreach (['unit_price', 'subtotal'] as $column) {
                    if (Schema::hasColumn('seed_request_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
