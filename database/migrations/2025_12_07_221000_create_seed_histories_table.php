<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('seed_histories')) {
            Schema::create('seed_histories', function (Blueprint $table) {
                $table->string('seed_history_id', 36)->primary();
                $table->string('inventory_type_seed_id', 36)->onDelete('cascade');
                $table->string('action')->comment('create, update, delete, reduce_stock');
                $table->text('description')->nullable()->comment('Deskripsi aksi');
                $table->json('old_data')->nullable()->comment('Data sebelum perubahan');
                $table->json('new_data')->nullable()->comment('Data setelah perubahan');
                $table->string('user_id', 36)->onDelete('cascade')->comment('User yang melakukan aksi');
                $table->timestamps();

                $table->index(['inventory_type_seed_id', 'action']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_histories');
    }
};

