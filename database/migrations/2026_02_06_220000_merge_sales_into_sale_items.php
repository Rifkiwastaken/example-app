<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Gabungkan data tabel sales ke sale_items.
     *
     * - Tambah kolom header penjualan ke tabel sale_items
     * - Salin nilai dari tabel sales berdasarkan sale_id
     * - Hapus tabel sales (struktur lama tidak lagi dipakai)
     *
     * Catatan: struktur ini membuat setiap baris di sale_items
     * menyimpan sekaligus data header + item penjualan.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sale_items') || !Schema::hasTable('sales')) {
            return;
        }

        // Tambah kolom-kolom dari tabel sales ke sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'receipt_number')) {
                $table->string('receipt_number', 50)->nullable()->after('sale_id');
            }
            if (!Schema::hasColumn('sale_items', 'sale_date')) {
                $table->date('sale_date')->nullable()->after('receipt_number');
            }
            if (!Schema::hasColumn('sale_items', 'buyer_name')) {
                $table->string('buyer_name', 50)->nullable()->after('sale_date');
            }
            if (!Schema::hasColumn('sale_items', 'buyer_contact')) {
                $table->string('buyer_contact', 50)->nullable()->after('buyer_name');
            }
            if (!Schema::hasColumn('sale_items', 'planting_location_id')) {
                $table->string('planting_location_id', 36)->nullable()->after('buyer_contact');
            }
            if (!Schema::hasColumn('sale_items', 'buyer_nik')) {
                $table->string('buyer_nik', 50)->nullable()->after('planting_location_id');
            }
            if (!Schema::hasColumn('sale_items', 'buyer_category')) {
                $table->enum('buyer_category', ['petani_perorangan', 'kelompok_tani', 'instansi_pemerintah', 'swasta', 'lainnya'])
                    ->nullable()
                    ->after('buyer_nik');
            }
            if (!Schema::hasColumn('sale_items', 'buyer_category_custom')) {
                $table->string('buyer_category_custom', 50)->nullable()->after('buyer_category');
            }
            if (!Schema::hasColumn('sale_items', 'destination_province')) {
                $table->string('destination_province', 50)->nullable()->after('buyer_category_custom');
            }
            if (!Schema::hasColumn('sale_items', 'destination_city')) {
                $table->string('destination_city', 50)->nullable()->after('destination_province');
            }
            if (!Schema::hasColumn('sale_items', 'destination_district')) {
                $table->string('destination_district', 50)->nullable()->after('destination_city');
            }
            if (!Schema::hasColumn('sale_items', 'destination_village')) {
                $table->string('destination_village', 50)->nullable()->after('destination_district');
            }
            if (!Schema::hasColumn('sale_items', 'planned_location_name')) {
                $table->string('planned_location_name', 50)->nullable()->after('destination_village');
            }
            if (!Schema::hasColumn('sale_items', 'estimated_planting_area')) {
                $table->decimal('estimated_planting_area', 10, 2)->nullable()->after('planned_location_name');
            }
            if (!Schema::hasColumn('sale_items', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('sale_items', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'transfer_bank'])->default('cash')->after('total_amount');
            }
            if (!Schema::hasColumn('sale_items', 'payment_status')) {
                $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas')->after('payment_method');
            }
            if (!Schema::hasColumn('sale_items', 'notes')) {
                $table->text('notes')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('sale_items', 'payment_proof')) {
                $table->string('payment_proof', 255)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('sale_items', 'user_id')) {
                $table->string('user_id', 36)->nullable()->after('payment_proof');
            }
        });

        // Pindahkan data dari sales ke sale_items berdasarkan sale_id
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                UPDATE sale_items si
                JOIN sales s ON si.sale_id = s.sale_id
                SET
                    si.receipt_number = s.receipt_number,
                    si.sale_date = s.sale_date,
                    si.buyer_name = s.buyer_name,
                    si.buyer_contact = s.buyer_contact,
                    si.planting_location_id = s.planting_location_id,
                    si.total_amount = s.total_amount,
                    si.payment_method = s.payment_method,
                    si.payment_status = s.payment_status,
                    si.notes = s.notes,
                    si.user_id = s.user_id,
                    si.buyer_nik = IFNULL(si.buyer_nik, s.buyer_nik),
                    si.buyer_category = IFNULL(si.buyer_category, s.buyer_category),
                    si.buyer_category_custom = IFNULL(si.buyer_category_custom, s.buyer_category_custom),
                    si.destination_province = IFNULL(si.destination_province, s.destination_province),
                    si.destination_city = IFNULL(si.destination_city, s.destination_city),
                    si.destination_district = IFNULL(si.destination_district, s.destination_district),
                    si.destination_village = IFNULL(si.destination_village, s.destination_village),
                    si.planned_location_name = IFNULL(si.planned_location_name, s.planned_location_name),
                    si.estimated_planting_area = IFNULL(si.estimated_planting_area, s.estimated_planting_area)
            ");
        } elseif ($driver === 'pgsql') {
            DB::statement("
                UPDATE sale_items si
                SET
                    receipt_number = s.receipt_number,
                    sale_date = s.sale_date,
                    buyer_name = s.buyer_name,
                    buyer_contact = s.buyer_contact,
                    planting_location_id = s.planting_location_id,
                    total_amount = s.total_amount,
                    payment_method = s.payment_method,
                    payment_status = s.payment_status,
                    notes = s.notes,
                    user_id = s.user_id,
                    buyer_nik = COALESCE(si.buyer_nik, s.buyer_nik),
                    buyer_category = COALESCE(si.buyer_category, s.buyer_category),
                    buyer_category_custom = COALESCE(si.buyer_category_custom, s.buyer_category_custom),
                    destination_province = COALESCE(si.destination_province, s.destination_province),
                    destination_city = COALESCE(si.destination_city, s.destination_city),
                    destination_district = COALESCE(si.destination_district, s.destination_district),
                    destination_village = COALESCE(si.destination_village, s.destination_village),
                    planned_location_name = COALESCE(si.planned_location_name, s.planned_location_name),
                    estimated_planting_area = COALESCE(si.estimated_planting_area, s.estimated_planting_area)
                FROM sales s
                WHERE si.sale_id = s.sale_id
            ");
        } else {
            // Fallback untuk driver lain: iterasi manual
            $items = DB::table('sale_items')->select('sale_item_id', 'sale_id')->get();
            foreach ($items as $item) {
                $sale = DB::table('sales')->where('sale_id', $item->sale_id)->first();
                if (!$sale) {
                    continue;
                }
                DB::table('sale_items')
                    ->where('sale_item_id', $item->sale_item_id)
                    ->update([
                        'receipt_number' => $sale->receipt_number,
                        'sale_date' => $sale->sale_date,
                        'buyer_name' => $sale->buyer_name,
                        'buyer_contact' => $sale->buyer_contact,
                        'planting_location_id' => $sale->planting_location_id,
                        'total_amount' => $sale->total_amount,
                        'payment_method' => $sale->payment_method,
                        'payment_status' => $sale->payment_status,
                        'notes' => $sale->notes,
                        'user_id' => $sale->user_id,
                        'buyer_nik' => $sale->buyer_nik,
                        'buyer_category' => $sale->buyer_category,
                        'buyer_category_custom' => $sale->buyer_category_custom,
                        'destination_province' => $sale->destination_province,
                        'destination_city' => $sale->destination_city,
                        'destination_district' => $sale->destination_district,
                        'destination_village' => $sale->destination_village,
                        'planned_location_name' => $sale->planned_location_name,
                        'estimated_planting_area' => $sale->estimated_planting_area,
                    ]);
            }
        }

        // Nonaktifkan FK sementara lalu hapus tabel sales
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        Schema::dropIfExists('sales');

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Down hanya mengembalikan struktur dasar tabel sales (tanpa data penuh).
     */
    public function down(): void
    {
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->string('sale_id', 36)->primary();
                $table->string('receipt_number', 50)->unique();
                $table->date('sale_date');
                $table->string('buyer_name', 50);
                $table->string('buyer_contact', 50)->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->enum('payment_method', ['cash', 'transfer_bank'])->default('cash');
                $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas');
                $table->text('notes')->nullable();
                $table->string('user_id', 36)->nullable();
                $table->timestamps();
            });
        }

        // Catatan: data tidak dipindahkan kembali ke sales pada rollback.
    }
};

