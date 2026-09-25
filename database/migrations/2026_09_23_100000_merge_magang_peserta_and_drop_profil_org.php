<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_magang') && ! Schema::hasColumn('pendaftaran_magang', 'jabatan')) {
            Schema::table('pendaftaran_magang', function (Blueprint $table) {
                $table->string('jabatan', 100)->nullable()->after('institusi_asal');
                $table->json('peserta')->nullable()->after('no_whatsapp');
            });
            if (Schema::hasColumn('pendaftaran_magang', 'jurusan')) {
                DB::table('pendaftaran_magang')->whereNull('jabatan')->update([
                    'jabatan' => DB::raw('jurusan'),
                ]);
            }
        }

        if (Schema::hasTable('pendaftaran_magang_peserta') && Schema::hasTable('pendaftaran_magang')) {
            $rows = DB::table('pendaftaran_magang_peserta')->orderBy('id')->get()->groupBy('pendaftaran_magang_id');
            foreach ($rows as $id => $peserta) {
                DB::table('pendaftaran_magang')->where('id', $id)->update([
                    'peserta' => json_encode($peserta->map(fn ($row) => [
                        'nama_lengkap' => $row->nama_lengkap,
                        'identitas' => $row->identitas,
                        'jabatan' => $row->jurusan,
                    ])->values()->all()),
                ]);
            }
            Schema::dropIfExists('pendaftaran_magang_peserta');
        }

        if (Schema::hasTable('pendaftaran_magang') && Schema::hasColumn('pendaftaran_magang', 'jurusan')) {
            Schema::table('pendaftaran_magang', function (Blueprint $table) {
                $table->dropColumn('jurusan');
            });
        }

        Schema::dropIfExists('profil_kelembagaan');
    }

    public function down(): void
    {
        // Data merge is one-way.
    }
};
