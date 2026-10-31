<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE employees_new MODIFY COLUMN work_mode ENUM('wfo', 'wfh', 'hybrid') NOT NULL DEFAULT 'wfo'");
        DB::statement("ALTER TABLE attendances MODIFY COLUMN work_mode ENUM('wfo', 'wfh', 'hybrid') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE employees_new MODIFY COLUMN work_mode ENUM('wfo', 'wfh') NOT NULL DEFAULT 'wfo'");
        DB::statement("ALTER TABLE attendances MODIFY COLUMN work_mode ENUM('wfo', 'wfh') NULL DEFAULT NULL");
    }
};
