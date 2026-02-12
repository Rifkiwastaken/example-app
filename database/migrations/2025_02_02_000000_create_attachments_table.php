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
        Schema::create('attachments', function (Blueprint $table) {
            $table->string('attachment_id', 36)->primary();
            $table->string('planting_location_id', 36)->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('attachment_date');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_size', 36)->nullable();
            $table->string('mime_type')->nullable();
            $table->string('created_by', 36)->onDelete('cascade');
            $table->timestamp('edited_at')->nullable();
            $table->string('edited_by', 36)->nullable()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

