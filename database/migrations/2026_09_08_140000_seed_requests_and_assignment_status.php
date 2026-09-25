<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('attachments', 'module')) {
                $table->string('module', 20)->default('plant')->after('stock_id');
                $table->index('module');
            }
        });

        Schema::table('planting_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('planting_assignments', 'status')) {
                $table->string('status', 20)->default('ditugaskan')->after('intended_for');
            }
            if (! Schema::hasColumn('planting_assignments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (! Schema::hasColumn('planting_assignments', 'completed_by')) {
                $table->string('completed_by', 36)->nullable();
            }
            if (! Schema::hasColumn('planting_assignments', 'completion_note')) {
                $table->text('completion_note')->nullable();
            }
        });

        Schema::table('stock_packaging', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_packaging', 'hold_for_request')) {
                $table->boolean('hold_for_request')->default(false)->after('hold_for_recert');
            }
            if (! Schema::hasColumn('stock_packaging', 'hold_until')) {
                $table->timestamp('hold_until')->nullable()->after('hold_for_request');
            }
            if (! Schema::hasColumn('stock_packaging', 'seed_request_id')) {
                $table->string('seed_request_id', 36)->nullable()->after('hold_until');
                $table->index('seed_request_id');
            }
        });

        if (! Schema::hasTable('seed_requests')) {
            Schema::create('seed_requests', function (Blueprint $table) {
                $table->string('seed_request_id', 36)->primary();
                $table->string('request_number', 50)->unique();
                $table->date('request_date');
                $table->string('buyer_name');
                $table->string('buyer_contact')->nullable();
                $table->string('buyer_nik')->nullable();
                $table->string('buyer_category', 50)->nullable();
                $table->string('buyer_category_custom')->nullable();
                $table->string('organization')->nullable();
                $table->string('destination_province')->nullable();
                $table->string('destination_city')->nullable();
                $table->string('destination_district')->nullable();
                $table->string('destination_village')->nullable();
                $table->string('planned_location_name')->nullable();
                $table->string('planned_gps', 100)->nullable();
                $table->decimal('estimated_planting_area', 8, 2)->nullable();
                $table->string('status', 30)->default('menunggu_verifikasi');
                $table->text('rejection_reason')->nullable();
                $table->string('created_by', 36)->nullable();
                $table->string('processed_by', 36)->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('ready_at')->nullable();
                $table->timestamp('taken_at')->nullable();
                $table->string('receipt_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seed_request_items')) {
            Schema::create('seed_request_items', function (Blueprint $table) {
                $table->string('seed_request_item_id', 36)->primary();
                $table->string('seed_request_id', 36);
                $table->string('seed_varieties_id', 36);
                $table->decimal('quantity', 8, 2);
                $table->string('unit', 20)->nullable();
                $table->timestamps();
                $table->foreign('seed_request_id')->references('seed_request_id')->on('seed_requests')->cascadeOnDelete();
                $table->foreign('seed_varieties_id')->references('seed_varieties_id')->on('plant_varieties')->cascadeOnDelete();
            });
        }

        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'seed_request_id')) {
                $table->string('seed_request_id', 36)->nullable()->after('stock_packaging_id');
                $table->index('seed_request_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'seed_request_id')) {
                $table->dropColumn('seed_request_id');
            }
        });
        Schema::dropIfExists('seed_request_items');
        Schema::dropIfExists('seed_requests');
    }
};
