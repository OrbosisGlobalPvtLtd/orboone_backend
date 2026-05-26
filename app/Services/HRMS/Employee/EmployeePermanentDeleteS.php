<?php

namespace App\Services\HRMS\Employee;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EmployeePermanentDeleteS
{
    public function __construct()
    {
    }

    public function impactReport(int $employeeId): array
    {
        $employee = DB::table('employees_new')->where('id', $employeeId)->first();
        if (! $employee) {
            return ['exists' => false, 'employee_id' => $employeeId, 'tables' => [], 'locked_risks' => []];
        }

        $userId = (int) ($employee->user_id ?? 0);
        $tables = [];
        $counts = function (string $table, string $column, int $value) use (&$tables): void {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $tables[$table] = (int) DB::table($table)->where($column, $value)->count();
            }
        };

        $counts('employee_profiles', 'employee_id', $employeeId);
        $counts('employee_documents_new', 'employee_id', $employeeId);
        $counts('attendances', 'employee_id', $employeeId);
        $counts('attendance_work_logs', 'employee_id', $employeeId);
        $counts('leave_requests', 'employee_id', $employeeId);
        $counts('leave_request_dates', 'employee_id', $employeeId);
        $counts('leave_allocations', 'employee_id', $employeeId);
        $counts('leave_balance_logs', 'employee_id', $employeeId);
        $counts('comp_offs', 'employee_id', $employeeId);
        $counts('payrolls', 'employee_id', $employeeId);
        $counts('payslips', 'employee_id', $employeeId);
        $counts('payroll_adjustments', 'employee_id', $employeeId);
        $counts('employee_salary_histories', 'employee_id', $employeeId);
        $counts('enterprise_salary_structures', 'employee_id', $employeeId);
        $counts('enterprise_salary_structure_histories', 'employee_id', $employeeId);
        $counts('enterprise_payrolls', 'employee_id', $employeeId);
        $counts('enterprise_payslips', 'employee_id', $employeeId);
        $counts('enterprise_reimbursements', 'employee_id', $employeeId);
        $counts('enterprise_bonus_incentives', 'employee_id', $employeeId);
        $counts('enterprise_fnf_settlements', 'employee_id', $employeeId);
        $counts('generated_documents', 'employee_id', $employeeId);
        $counts('generated_document_logs', 'actor_user_id', $userId);
        $counts('asset_allocations', 'employee_id', $employeeId);
        $counts('employee_policy_assignments', 'employee_id', $employeeId);
        $counts('leave_policy_employee_overrides', 'employee_id', $employeeId);
        $counts('attendance_policy_employee_overrides', 'employee_id', $employeeId);

        if ($userId > 0) {
            $counts('notifications', 'user_id', $userId);
            $counts('user_roles', 'user_id', $userId);
            $counts('personal_access_tokens', 'tokenable_id', $userId);
        }

        $lockedPayrolls = Schema::hasTable('payrolls')
            ? (int) DB::table('payrolls')
                ->where('employee_id', $employeeId)
                ->where(function ($q) {
                    if (Schema::hasColumn('payrolls', 'status')) {
                        $q->whereIn('status', ['locked', 'paid']);
                    }
                    if (Schema::hasColumn('payrolls', 'locked_at')) {
                        $q->orWhereNotNull('locked_at');
                    }
                })->count()
            : 0;

        $lockedEnterprise = Schema::hasTable('enterprise_payrolls')
            ? (int) DB::table('enterprise_payrolls')
                ->where('employee_id', $employeeId)
                ->whereIn('status', ['locked', 'paid'])
                ->count()
            : 0;

        return [
            'exists' => true,
            'employee_id' => $employeeId,
            'user_id' => $userId,
            'tables' => $tables,
            'locked_risks' => [
                'payrolls_locked_or_paid' => $lockedPayrolls,
                'enterprise_payrolls_locked_or_paid' => $lockedEnterprise,
            ],
            'employee_hrms_dir' => storage_path('app/private/hrms/employees/' . $employeeId),
        ];
    }

    public function deactivate(int $employeeId, int $actorUserId): array
    {
        return DB::transaction(function () use ($employeeId, $actorUserId) {
            $employee = DB::table('employees_new')->where('id', $employeeId)->first();
            if (! $employee) {
                throw new \RuntimeException('Employee not found.');
            }

            $updated = [];
            if (Schema::hasColumn('employees_new', 'is_active')) {
                $updated['employees_new'] = DB::table('employees_new')->where('id', $employeeId)->update([
                    'is_active' => 0,
                    'employment_status' => 'inactive',
                    'updated_by' => $actorUserId,
                    'updated_at' => now(),
                ]);
            }

            if (! empty($employee->user_id) && Schema::hasColumn('users', 'is_active')) {
                $updated['users'] = DB::table('users')->where('id', $employee->user_id)->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }

            return $updated;
        });
    }

    public function permanentDelete(int $employeeId, int $actorUserId, bool $forceLockedRecords = false): array
    {
        $impact = $this->impactReport($employeeId);
        if (! ($impact['exists'] ?? false)) {
            throw new \RuntimeException('Employee not found.');
        }

        $lockedRisks = $impact['locked_risks'] ?? [];
        $riskCount = (int) ($lockedRisks['payrolls_locked_or_paid'] ?? 0) + (int) ($lockedRisks['enterprise_payrolls_locked_or_paid'] ?? 0);
        if ($riskCount > 0 && ! $forceLockedRecords) {
            throw new \RuntimeException('Locked/legal payroll records detected. Set force flag to proceed.');
        }

        $employee = DB::table('employees_new')->where('id', $employeeId)->first();
        $userId = (int) ($employee->user_id ?? 0);
        $deleted = [];
        $deleteBy = function (string $table, string $column, int $value) use (&$deleted): void {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $deleted[$table] = DB::table($table)->where($column, $value)->delete();
            }
        };

        DB::transaction(function () use ($employeeId, $userId, $deleteBy, &$deleted) {
            $deleteBy('employee_documents_new', 'employee_id', $employeeId);
            $deleteBy('employee_profiles', 'employee_id', $employeeId);
            $deleteBy('attendance_work_logs', 'employee_id', $employeeId);
            $deleteBy('attendances', 'employee_id', $employeeId);
            $deleteBy('leave_request_dates', 'employee_id', $employeeId);
            $deleteBy('leave_requests', 'employee_id', $employeeId);
            $deleteBy('leave_balance_logs', 'employee_id', $employeeId);
            $deleteBy('leave_allocations', 'employee_id', $employeeId);
            $deleteBy('comp_offs', 'employee_id', $employeeId);
            $deleteBy('payslips', 'employee_id', $employeeId);
            $deleteBy('payroll_adjustments', 'employee_id', $employeeId);
            $deleteBy('payrolls', 'employee_id', $employeeId);
            $deleteBy('employee_salary_histories', 'employee_id', $employeeId);
            $deleteBy('enterprise_payslips', 'employee_id', $employeeId);
            $deleteBy('enterprise_reimbursements', 'employee_id', $employeeId);
            $deleteBy('enterprise_bonus_incentives', 'employee_id', $employeeId);
            $deleteBy('enterprise_fnf_settlements', 'employee_id', $employeeId);
            $deleteBy('enterprise_payrolls', 'employee_id', $employeeId);
            $deleteBy('enterprise_salary_structure_histories', 'employee_id', $employeeId);
            $deleteBy('enterprise_salary_structures', 'employee_id', $employeeId);
            $deleteBy('generated_documents', 'employee_id', $employeeId);
            $deleteBy('asset_allocations', 'employee_id', $employeeId);
            $deleteBy('employee_policy_assignments', 'employee_id', $employeeId);
            $deleteBy('leave_policy_employee_overrides', 'employee_id', $employeeId);
            $deleteBy('attendance_policy_employee_overrides', 'employee_id', $employeeId);

            $deleteBy('employees_new', 'id', $employeeId);

            if ($userId > 0) {
                $deleteBy('notifications', 'user_id', $userId);
                $deleteBy('personal_access_tokens', 'tokenable_id', $userId);
                $deleteBy('user_roles', 'user_id', $userId);
                $deleted['users'] = DB::table('users')->where('id', $userId)->delete();
            }
        });

        $employeeFolder = storage_path('app/private/hrms/employees/' . $employeeId);
        if (is_dir($employeeFolder)) {
            File::deleteDirectory($employeeFolder);
        }

        Log::channel('daily')->info('Employee permanent delete completed', [
            'employee_id' => $employeeId,
            'actor_user_id' => $actorUserId,
            'deleted_counts' => $deleted,
        ]);

        return [
            'employee_id' => $employeeId,
            'deleted_counts' => $deleted,
            'employee_folder_deleted' => ! is_dir($employeeFolder),
        ];
    }
}
