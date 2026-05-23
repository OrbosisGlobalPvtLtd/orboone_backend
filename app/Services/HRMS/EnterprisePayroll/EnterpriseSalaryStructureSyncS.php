<?php

namespace App\Services\HRMS\EnterprisePayroll;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnterpriseSalaryStructureSyncS
{
    /**
     * Safely sync salary from employee onboarding/lifecycle.
     *
     * @param object $employee The employee object or database record
     * @param string|null $reason The reason for salary sync
     */
    public function syncFromEmployee($employee, ?string $reason = null): void
    {
        if (!isset($employee->id) || !isset($employee->actual_salary)) {
            return;
        }

        DB::beginTransaction();

        try {
            $effectiveDate = $employee->salary_effective_from 
                ?? $employee->joining_date 
                ?? $employee->internship_start_date 
                ?? now()->toDateString();
            
            $effectiveDate = Carbon::parse($effectiveDate)->toDateString();

            $monthlyCtc = round((float) ($employee->actual_salary ?? 0), 2);
            $annualCtc = round($monthlyCtc * 12, 2);

            // Fetch active structure
            $activeStructure = DB::table('enterprise_salary_structures')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();

            $stage = $employee->employee_stage ?? 'probation';
            $source = 'onboarding_sync';
            $revisionReason = $reason ?? 'Auto-synced from employee profile/lifecycle';

            if ($activeStructure) {
                // Prevent duplicate if same employee_id + effective_from + monthly_ctc + stage + source already exists
                if (
                    Carbon::parse($activeStructure->effective_from)->toDateString() === $effectiveDate
                    && (float) $activeStructure->monthly_ctc === $monthlyCtc
                    && $activeStructure->stage === $stage
                    && $activeStructure->source === $source
                ) {
                    DB::rollBack();
                    return; // Already up to date
                }

                // Same date correction: update existing same-date structure instead of creating duplicate
                if (Carbon::parse($activeStructure->effective_from)->toDateString() === $effectiveDate) {
                    $breakdown = $this->calculateBreakdown($monthlyCtc);

                    DB::table('enterprise_salary_structures')
                        ->where('id', $activeStructure->id)
                        ->update([
                            'annual_ctc' => $annualCtc,
                            'monthly_ctc' => $monthlyCtc,
                            'basic_monthly' => $breakdown['basic'],
                            'basic_annual' => round($breakdown['basic'] * 12, 2),
                            'hra_monthly' => $breakdown['hra'],
                            'hra_annual' => round($breakdown['hra'] * 12, 2),
                            'special_allowance_monthly' => $breakdown['special_allowance'],
                            'special_allowance_annual' => round($breakdown['special_allowance'] * 12, 2),
                            'professional_tax_monthly' => $breakdown['professional_tax'],
                            'stage' => $stage,
                            'source' => $source,
                            'revision_reason' => $revisionReason,
                            'updated_at' => now(),
                        ]);

                    DB::table('enterprise_salary_structure_histories')->insert([
                        'salary_structure_id' => $activeStructure->id,
                        'employee_id' => $employee->id,
                        'old_values' => json_encode($activeStructure),
                        'new_values' => json_encode(['monthly_ctc' => $monthlyCtc, 'effective_from' => $effectiveDate, 'stage' => $stage]),
                        'revision_reason' => 'Same-date correction: ' . $revisionReason,
                        'changed_by_user_id' => auth()->id() ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::commit();
                    return;
                }
            }

            // Calculate breakdowns
            $breakdown = $this->calculateBreakdown($monthlyCtc);

            // Close old active structure
            if ($activeStructure) {
                $previousEffectiveTo = Carbon::parse($effectiveDate)->subDay()->toDateString();

                DB::table('enterprise_salary_structures')
                    ->where('id', $activeStructure->id)
                    ->update([
                        'effective_to' => $previousEffectiveTo,
                        'status' => 'inactive',
                        'updated_at' => now(),
                    ]);
            }

            $newStructureId = DB::table('enterprise_salary_structures')->insertGetId([
                'employee_id' => $employee->id,
                'effective_from' => $effectiveDate,
                'effective_to' => null,
                'annual_ctc' => $annualCtc,
                'monthly_ctc' => $monthlyCtc,
                
                'basic_monthly' => $breakdown['basic'],
                'basic_annual' => round($breakdown['basic'] * 12, 2),
                
                'hra_monthly' => $breakdown['hra'],
                'hra_annual' => round($breakdown['hra'] * 12, 2),
                
                'special_allowance_monthly' => $breakdown['special_allowance'],
                'special_allowance_annual' => round($breakdown['special_allowance'] * 12, 2),
                
                'professional_tax_monthly' => $breakdown['professional_tax'],
                
                'tds_monthly' => $breakdown['tds'],
                'tds_annual' => round($breakdown['tds'] * 12, 2),
                
                'other_deduction_monthly' => 0,
                
                'status' => 'active',
                'source' => $source,
                'stage' => $stage,
                'sync_reference_type' => 'employee_onboarding',
                'sync_reference_id' => $employee->id,
                'revision_reason' => $revisionReason,
                
                'created_by_user_id' => auth()->id() ?? 1,
                'approved_by_user_id' => auth()->id() ?? 1,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create audit log
            DB::table('enterprise_salary_structure_histories')->insert([
                'salary_structure_id' => $newStructureId,
                'employee_id' => $employee->id,
                'old_values' => $activeStructure ? json_encode($activeStructure) : null,
                'new_values' => json_encode(['monthly_ctc' => $monthlyCtc, 'effective_from' => $effectiveDate]),
                'revision_reason' => $revisionReason,
                'changed_by_user_id' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Enterprise payroll salary sync failed: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            // Do NOT throw error to UI
        }
    }

    /**
     * Calculate basic breakdown logic.
     */
    private function calculateBreakdown(float $monthlyCtc): array
    {
        if ($monthlyCtc <= 0) {
            return [
                'basic' => 0,
                'hra' => 0,
                'special_allowance' => 0,
                'professional_tax' => 0,
                'tds' => 0,
            ];
        }

        $basic = round($monthlyCtc * 0.50, 2);
        $hra = round($basic * 0.50, 2); 
        $pt = $monthlyCtc >= 15000 ? 200.00 : 0.00;

        $specialAllowance = round($monthlyCtc - ($basic + $hra), 2);
        
        if ($specialAllowance < 0) {
            $specialAllowance = 0;
            $hra = $monthlyCtc - $basic;
            if ($hra < 0) {
                $hra = 0;
                $basic = $monthlyCtc;
            }
        }

        return [
            'basic' => $basic,
            'hra' => $hra,
            'special_allowance' => $specialAllowance,
            'professional_tax' => $pt,
            'tds' => 0,
        ];
    }
}
