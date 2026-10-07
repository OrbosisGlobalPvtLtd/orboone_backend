<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_duration_type')) {
            DB::statement("ALTER TABLE employees_new MODIFY COLUMN probation_duration_type ENUM('months', 'days') NULL DEFAULT 'months'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_duration_type')) {
            DB::table('employees_new')
                ->whereNull('probation_duration_type')
                ->update(['probation_duration_type' => 'months']);

            DB::statement("ALTER TABLE employees_new MODIFY COLUMN probation_duration_type ENUM('months', 'days') NOT NULL DEFAULT 'months'");
        }
    }
};
