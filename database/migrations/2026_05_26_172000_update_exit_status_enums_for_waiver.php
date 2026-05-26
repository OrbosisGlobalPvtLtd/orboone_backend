<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_exit_processes')) {
            return;
        }

        // Allow waiver/finalized states to persist at DB level.
        DB::statement("
            ALTER TABLE employee_exit_processes
            MODIFY COLUMN fnf_status ENUM('pending','processing','approved','paid','completed','waived')
            NOT NULL DEFAULT 'pending'
        ");

        // Keep legacy values and add waived to avoid truncation when mirrored from asset_status.
        DB::statement("
            ALTER TABLE employee_exit_processes
            MODIFY COLUMN asset_handover_status ENUM('not_required','pending','completed','waived')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Intentionally left empty to avoid destructive enum rollback.
    }
};

