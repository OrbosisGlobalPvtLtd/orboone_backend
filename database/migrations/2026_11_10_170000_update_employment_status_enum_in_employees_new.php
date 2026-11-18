<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees_new')) {
            DB::statement("ALTER TABLE employees_new MODIFY COLUMN employment_status ENUM('active', 'inactive', 'notice_period', 'exited', 'terminated', 'absconded', 'suspended') NOT NULL DEFAULT 'active'");

            // Backfill completed exits
            if (Schema::hasTable('employee_exit_processes')) {
                $completedExits = DB::table('employee_exit_processes')
                    ->where(function ($q) {
                        $q->where('status', 'exit_completed')
                            ->orWhere('final_status', 'completed');
                    })
                    ->get();

                foreach ($completedExits as $exit) {
                    $status = match ((string) $exit->exit_type) {
                        'termination' => 'terminated',
                        'absconding' => 'absconded',
                        default => 'exited',
                    };

                    DB::table('employees_new')
                        ->where('id', $exit->employee_id)
                        ->update([
                            'employment_status' => $status,
                            'is_active' => 0,
                        ]);
                }

                // Backfill in-progress exits to notice_period
                $pendingExits = DB::table('employee_exit_processes')
                    ->whereNotIn('status', ['exit_completed', 'cancelled', 'rejected', 'rolled_back'])
                    ->where('final_status', '!=', 'completed')
                    ->pluck('employee_id');

                DB::table('employees_new')
                    ->whereIn('id', $pendingExits)
                    ->whereIn('employment_status', ['', 'active'])
                    ->update(['employment_status' => 'notice_period']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new')) {
            DB::statement("ALTER TABLE employees_new MODIFY COLUMN employment_status ENUM('active', 'resigned', 'terminated', 'inactive') NOT NULL DEFAULT 'active'");
        }
    }
};

