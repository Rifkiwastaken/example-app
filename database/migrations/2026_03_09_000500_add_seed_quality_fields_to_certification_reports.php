<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('certification_reports')) {
            return;
        }

        Schema::table('certification_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('certification_reports', 'daya_berkecambah')) {
                $table->decimal('daya_berkecambah', 5, 2)->nullable()->after('estimated_yield');
            }
            if (!Schema::hasColumn('certification_reports', 'cvl')) {
                $table->decimal('cvl', 5, 2)->nullable()->after('daya_berkecambah');
            }
            if (!Schema::hasColumn('certification_reports', 'kadar_air')) {
                $table->decimal('kadar_air', 5, 2)->nullable()->after('cvl');
            }
            if (!Schema::hasColumn('certification_reports', 'benih_murni')) {
                $table->decimal('benih_murni', 5, 2)->nullable()->after('kadar_air');
            }
            if (!Schema::hasColumn('certification_reports', 'kotoran_benih')) {
                $table->decimal('kotoran_benih', 5, 2)->nullable()->after('benih_murni');
            }
            if (!Schema::hasColumn('certification_reports', 'biji_gulma')) {
                $table->decimal('biji_gulma', 5, 2)->nullable()->after('kotoran_benih');
            }
            if (!Schema::hasColumn('certification_reports', 'test_completed_at')) {
                $table->date('test_completed_at')->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('certification_reports', 'package_content_per_pack')) {
                $table->decimal('package_content_per_pack', 12, 2)->nullable()->after('certified_seed_quantity');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('certification_reports')) {
            return;
        }

        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'daya_berkecambah')) {
                $table->dropColumn('daya_berkecambah');
            }
            if (Schema::hasColumn('certification_reports', 'cvl')) {
                $table->dropColumn('cvl');
            }
            if (Schema::hasColumn('certification_reports', 'kadar_air')) {
                $table->dropColumn('kadar_air');
            }
            if (Schema::hasColumn('certification_reports', 'benih_murni')) {
                $table->dropColumn('benih_murni');
            }
            if (Schema::hasColumn('certification_reports', 'kotoran_benih')) {
                $table->dropColumn('kotoran_benih');
            }
            if (Schema::hasColumn('certification_reports', 'biji_gulma')) {
                $table->dropColumn('biji_gulma');
            }
            if (Schema::hasColumn('certification_reports', 'test_completed_at')) {
                $table->dropColumn('test_completed_at');
            }
            if (Schema::hasColumn('certification_reports', 'package_content_per_pack')) {
                $table->dropColumn('package_content_per_pack');
            }
        });
    }
};

