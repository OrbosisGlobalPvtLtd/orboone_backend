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
        if (Schema::hasTable('leave_allocations')) {
            // Step 1: Clean duplicate leave_allocations records for (employee_id, year)
            $duplicates = DB::table('leave_allocations')
                ->select('employee_id', 'year', DB::raw('COUNT(*) as total_count'))
                ->groupBy('employee_id', 'year')
                ->having('total_count', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $rows = DB::table('leave_allocations')
                    ->where('employee_id', $dup->employee_id)
                    ->where('year', $dup->year)
                    ->orderByRaw("CASE WHEN employment_stage = 'permanent' THEN 1 ELSE 2 END")
                    ->orderByDesc('id')
                    ->get();

                if ($rows->count() > 1) {
                    $keep = $rows->first();
                    $removeIds = $rows->slice(1)->pluck('id')->toArray();

                    $totalPaidUsed = $rows->sum('paid_used');
                    $totalSickUsed = $rows->sum('sick_used');
                    $totalCompUsed = $rows->sum('comp_off_used');
                    $totalLwpUsed = $rows->sum('lwp_used');

                    // Update logs pointing to duplicate records to point to kept record
                    DB::table('leave_balance_logs')
                        ->whereIn('leave_allocation_id', $removeIds)
                        ->update(['leave_allocation_id' => $keep->id]);

                    // Update kept record's used leaves
                    $paidRem = max(0.0, (float) $keep->paid_allocated - (float) $totalPaidUsed);
                    $sickRem = max(0.0, (float) $keep->sick_allocated - (float) $totalSickUsed);
                    $compRem = max(0.0, (float) $keep->comp_off_allocated - (float) $totalCompUsed);

                    DB::table('leave_allocations')
                        ->where('id', $keep->id)
                        ->update([
                            'paid_used' => $totalPaidUsed,
                            'sick_used' => $totalSickUsed,
                            'comp_off_used' => $totalCompUsed,
                            'lwp_used' => $totalLwpUsed,
                            'total_used' => round($totalPaidUsed + $totalSickUsed + $totalCompUsed, 2),
                            'paid_remaining' => round($paidRem, 2),
                            'sick_remaining' => round($sickRem, 2),
                            'comp_off_remaining' => round($compRem, 2),
                            'total_remaining' => round($paidRem + $sickRem + $compRem, 2),
                            'updated_at' => now(),
                        ]);

                    // Delete obsolete duplicate rows
                    DB::table('leave_allocations')->whereIn('id', $removeIds)->delete();
                }
            }

            // Step 2: Add unique index on (employee_id, year)
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->unique(['employee_id', 'year'], 'leave_allocations_emp_year_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leave_allocations')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->dropUnique('leave_allocations_emp_year_unique');
            });
        }
    }
};
