<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1 - Level 0: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tables: users, plant_types, warehouses, task_templates, landing_page_settings
     */
    public function up(): void
    {
        // USERS TABLE
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_id', 36)->nullable()->unique('users_user_id_unq')->after('id');
            });
        }

        // PLANT_TYPES TABLE
        if (Schema::hasTable('plant_types') && !Schema::hasColumn('plant_types', 'plant_type_id')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->string('plant_type_id', 36)->nullable()->unique('plant_types_plant_type_id_unq')->after('id');
            });
        }

        // WAREHOUSES TABLE
        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'warehouse_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->string('warehouse_id', 36)->nullable()->unique('warehouses_warehouse_id_unq')->after('id');
                $table->string('new_responsible_person_id', 36)->nullable()->after('responsible_person_id');
            });
        }

        // TASK_TEMPLATES TABLE
        if (Schema::hasTable('task_templates') && !Schema::hasColumn('task_templates', 'task_template_id')) {
            Schema::table('task_templates', function (Blueprint $table) {
                $table->string('task_template_id', 36)->nullable()->unique('task_templates_task_template_id_unq')->after('id');
            });
        }

        // LANDING_PAGE_SETTINGS TABLE
        if (Schema::hasTable('landing_page_settings') && !Schema::hasColumn('landing_page_settings', 'landing_page_setting_id')) {
            Schema::table('landing_page_settings', function (Blueprint $table) {
                $table->string('landing_page_setting_id', 36)->nullable()->unique('landing_page_setting_landing_page_setting_unq')->after('id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['user_id']);
            });
        }

        if (Schema::hasTable('plant_types')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->dropColumn(['plant_type_id']);
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropColumn(['warehouse_id', 'new_responsible_person_id']);
            });
        }

        if (Schema::hasTable('task_templates')) {
            Schema::table('task_templates', function (Blueprint $table) {
                $table->dropColumn(['task_template_id']);
            });
        }

        if (Schema::hasTable('landing_page_settings')) {
            Schema::table('landing_page_settings', function (Blueprint $table) {
                $table->dropColumn(['landing_page_setting_id']);
            });
        }

    }
};