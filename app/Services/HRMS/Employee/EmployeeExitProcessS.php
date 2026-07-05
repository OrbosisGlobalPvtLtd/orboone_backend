<?php

namespace App\Services\HRMS\Employee;

use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeExitProcessS
{
    private string $employeeTable = 'employees_new';
    private string $exitTable = 'employee_exit_processes';

    public function __construct(
        private NotificationS $notifications,
        private EmployeeExitPolicyS $policyService
    )
    {
    }

    public function initiate(int $employeeId, array $payload, int $actorUserId): array
    {
        abort_if(! Schema::hasTable($this->employeeTable), 500, 'Employee table missing.');
        abort_if(! Schema::hasTable($this->exitTable), 500, 'Employee exit process table missing.');

        $employee = DB::table($this->employeeTable)->where('id', $employeeId)->first();
        abort_if(! $employee, 404, 'Employee not found.');

        $exitType = strtolower((string) ($payload['exit_type'] ?? 'resignation'));
        $employeeModel = \App\Models\HRMS\Employee\EmployeeM::find($employeeId);
        $policyFlags = $this->policyService->flags($employeeModel, $exitType);
        $resolvedNoticeDays = $this->policyService->getNoticePeriodDays($employeeModel, $exitType);
        $resolvedFnfDays = $this->policyService->getFnfProcessingDays($employeeModel, $exitType);

        $resignationDate = $payload['resignation_date'] ?? now()->toDateString();
        $terminationDate = $payload['termination_date'] ?? now()->toDateString();
        $requestedNoticeWaived = (bool) ($payload['notice_waived'] ?? false);
        $requestedImmediateExit = (bool) ($payload['immediate_exit'] ?? false);
        $requestedBuyoutRecovery = (bool) ($payload['buyout_recovery'] ?? false);
        $actorIsSuperAdmin = (bool) ($payload['actor_is_super_admin'] ?? false);
        $noticeWaived = $requestedNoticeWaived && ($policyFlags['allow_waiver'] || $actorIsSuperAdmin);
        $immediateExit = $requestedImmediateExit && ($policyFlags['allow_immediate_exit'] || $actorIsSuperAdmin);
        $buyoutRecovery = $requestedBuyoutRecovery && ($policyFlags['allow_buyout'] || $actorIsSuperAdmin);

        $noticeDays = $resolvedNoticeDays;
        if ($actorIsSuperAdmin && isset($payload['notice_period_days']) && is_numeric($payload['notice_period_days'])) {
            $noticeDays = max(0, (int) $payload['notice_period_days']);
        }

        $lastWorkingDay = now()->toDateString();
        if (in_array($exitType, ['resignation', 'contract_end', 'internship_exit', 'internship_completed'], true)) {
            if ($noticeWaived && $policyFlags['allow_waiver']) {
                $lastWorkingDay = $payload['last_working_day'] ?? $resignationDate;
                $noticeDays = 0;
            } else {
                $lastWorkingDay = $this->policyService->calculateLastWorkingDay($resignationDate, $noticeDays);
            }
        } elseif (in_array($exitType, ['termination', 'absconding'], true)) {
            $lastWorkingDay = $terminationDate;
        } else {
            $lastWorkingDay = $payload['last_working_day'] ?? now()->toDateString();
        }

        $status = match ($exitType) {
            'absconding' => 'absconded',
            'termination' => ($immediateExit ? 'asset_pending' : 'exit_initiated'),
            'resignation', 'contract_end', 'internship_exit', 'internship_completed' => 'notice_period',
            default => 'exit_initiated',
        };

        $openExit = DB::table($this->exitTable)
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['exit_completed', 'cancelled'])
            ->orderByDesc('id')
            ->first();

        $previousEmploymentStatus = $employee->employment_status ?? 'active';
        $isExistingExitStatus = in_array($previousEmploymentStatus, ['notice_period', 'terminated', 'absconded', 'exited'], true);

        $prevStatusToSave = $previousEmploymentStatus;
        if ($openExit && !empty($openExit->previous_employment_status)) {
            $prevStatusToSave = $openExit->previous_employment_status;
        } elseif ($isExistingExitStatus) {
            $prevStatusToSave = 'active';
        }

        $loginDisabledByExit = 0;
        if ($openExit && !empty($openExit->login_disabled_by_exit)) {
            $loginDisabledByExit = $openExit->login_disabled_by_exit;
        }

        $immediateDisable = (bool) ($payload['immediate_disable_login'] ?? false);
        if (in_array($exitType, ['termination', 'absconding'], true) && ($immediateExit || $immediateDisable)) {
            if (!empty($employee->user_id) && Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
                $user = DB::table('users')->where('id', $employee->user_id)->first();
                if ($user && $user->is_active) {
                    $this->disableUser((int) $employee->user_id);
                    $loginDisabledByExit = 1;
                }
            }
        }

        $record = [
            'employee_id' => $employeeId,
            'exit_type' => $exitType,
            'previous_employment_status' => $prevStatusToSave,
            'login_disabled_by_exit' => $loginDisabledByExit,
            'resignation_date' => $resignationDate,
            'termination_date' => $terminationDate,
            'exit_initiated_date' => $payload['exit_initiated_date'] ?? now()->toDateString(),
            'last_working_day' => $lastWorkingDay,
            'last_working_date' => $lastWorkingDay,
            'notice_period_days' => $noticeDays,
            'notice_waived' => $noticeWaived ? 1 : 0,
            'immediate_exit' => $immediateExit ? 1 : 0,
            'buyout_recovery' => $buyoutRecovery ? 1 : 0,
            'fnf_due_date' => Carbon::parse($lastWorkingDay)->addDays($resolvedFnfDays)->toDateString(),
            'reason' => $payload['reason'] ?? null,
            'remarks' => $payload['remarks'] ?? null,
            'status' => $status,
            'asset_status' => 'pending',
            'fnf_status' => 'pending',
            'document_status' => 'pending',
            'handover_status' => 'pending',
            'asset_handover_status' => 'pending',
            'final_status' => 'pending',
            'initiated_by_user_id' => $actorUserId,
            'updated_at' => now(),
        ];

        if ($openExit) {
            DB::table($this->exitTable)->where('id', $openExit->id)->update($record);
            $exitId = (int) $openExit->id;
        } else {
            $record['created_at'] = now();
            $exitId = (int) DB::table($this->exitTable)->insertGetId($record);
        }

        // Initialize department clearances
        $this->initializeClearances($exitId);

        // Create an audit log: "Exit Clearance Started" in employee_lifecycle_logs
        if (Schema::hasTable('employee_lifecycle_logs')) {
            $exists = DB::table('employee_lifecycle_logs')
                ->where('employee_id', $employeeId)
                ->where('action', 'Exit Clearance Started')
                ->exists();
            if (!$exists) {
                DB::table('employee_lifecycle_logs')->insert([
                    'employee_id' => $employeeId,
                    'action' => 'Exit Clearance Started',
                    'performed_by_user_id' => $actorUserId,
                    'performed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasColumn($this->employeeTable, 'employment_status')) {
            $employmentStatus = match ($exitType) {
                'termination' => 'terminated',
                'absconding' => 'absconded',
                default => 'notice_period',
            };

            DB::table($this->employeeTable)->where('id', $employeeId)->update([
                'employment_status' => $employmentStatus,
                'relieving_date' => $lastWorkingDay,
                'updated_at' => now(),
            ]);
        }

        $this->notifications->notifyHrAndSuperAdmin(
            'Employee Exit Initiated',
            'Exit process initiated for employee #' . $employeeId . '.',
            'employee_exit_initiated',
            'hrms.employees.exit',
            [],
            ['employee_id' => $employeeId, 'exit_process_id' => $exitId]
        );

        if (! empty($employee->user_id)) {
            $this->notifications->notifyEmployee(
                'Your Exit Process Is Initiated',
                'HR has initiated your exit workflow. You can coordinate for clearance and handover.',
                'employee_exit_initiated',
                null,
                [],
                ['employee_id' => $employeeId, 'exit_process_id' => $exitId],
                (int) $employee->user_id
            );
        }

        return $this->refreshStatus($exitId);
    }

    public function refreshStatus(int $exitId): array
    {
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        abort_if(! $exit, 404, 'Exit process not found.');

        $employeeId = (int) $exit->employee_id;
        $assetPending = $this->hasPendingAssets($employeeId);
        $fnfDone = $this->isFnfCompleted($employeeId, $exitId);
        $documentDone = $this->isDocumentCompleted($employeeId, (string) $exit->exit_type);

        $assetStatus = in_array((string) $exit->asset_status, ['waived'], true) ? 'waived' : ($assetPending ? 'pending' : 'cleared');
        $fnfStatus = in_array((string) $exit->fnf_status, ['waived', 'approved', 'paid', 'completed'], true)
            ? (string) $exit->fnf_status
            : ($fnfDone ? 'completed' : 'pending');
        $documentStatus = in_array((string) $exit->document_status, ['waived', 'generated', 'sent', 'completed'], true)
            ? (string) $exit->document_status
            : ($documentDone ? 'completed' : 'pending');
        $handoverStatus = in_array((string) $exit->handover_status, ['cleared', 'completed', 'waived'], true)
            ? (string) $exit->handover_status
            : 'pending';

        $overall = 'ready_for_final_approval';
        if ($assetStatus !== 'cleared' && $assetStatus !== 'waived') {
            $overall = 'asset_pending';
        } elseif (! in_array($fnfStatus, ['completed', 'waived'], true)) {
            $overall = 'fnf_pending';
        } elseif (! in_array($documentStatus, ['completed', 'waived'], true)) {
            $overall = 'document_pending';
        } elseif (! in_array($handoverStatus, ['completed', 'waived'], true)) {
            $overall = 'handover_pending';
        }

        if ((string) $exit->exit_type === 'absconding') {
            $overall = 'absconded';
        }

        DB::table($this->exitTable)->where('id', $exitId)->update([
            'asset_status' => $assetStatus,
            'asset_handover_status' => $assetStatus === 'waived' ? 'waived' : ($assetStatus === 'cleared' ? 'completed' : 'pending'),
            'fnf_status' => $fnfStatus,
            'document_status' => $documentStatus,
            'handover_status' => $handoverStatus,
            'status' => $overall,
            'updated_at' => now(),
        ]);

        return (array) DB::table($this->exitTable)->where('id', $exitId)->first();
    }

    public function complete(int $exitId, int $actorUserId, bool $waive = false): array
    {
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        abort_if(! $exit, 404, 'Exit process not found.');

        $updated = $this->refreshStatus($exitId);

        // Final Clearance Rule Check
        $mandatoryDepts = ['hr', 'manager', 'it', 'admin', 'finance', 'asset'];
        $approvedCount = DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->whereIn('department_key', $mandatoryDepts)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount < count($mandatoryDepts)) {
            abort(422, 'Cannot proceed to Final Settlement. All mandatory department clearances (HR, Reporting Manager, IT, Admin, Finance, Asset Team) must be approved.');
        }

        $canComplete = in_array($updated['asset_status'], ['cleared', 'waived'], true)
            && in_array($updated['fnf_status'], ['completed', 'approved', 'paid', 'waived'], true)
            && in_array($updated['document_status'], ['completed', 'generated', 'sent', 'waived'], true)
            && in_array(($updated['handover_status'] ?? 'pending'), ['cleared', 'completed', 'waived'], true);

        if (! $canComplete && ! $waive) {
            $pending = [];
            if (! in_array($updated['asset_status'] ?? 'pending', ['cleared', 'waived'], true)) {
                $pending[] = 'Asset';
            }
            if (! in_array($updated['fnf_status'] ?? 'pending', ['completed', 'approved', 'paid', 'waived'], true)) {
                $pending[] = 'FnF';
            }
            if (! in_array($updated['document_status'] ?? 'pending', ['completed', 'generated', 'sent', 'waived'], true)) {
                $pending[] = 'Document';
            }
            if (! in_array($updated['handover_status'] ?? 'pending', ['cleared', 'completed', 'waived'], true)) {
                $pending[] = 'Handover';
            }
            $suffix = empty($pending) ? '' : (' Pending: ' . implode('/', $pending));
            abort(422, 'Exit checklist is not fully completed.' . $suffix);
        }

        DB::transaction(function () use ($exit, $actorUserId) {
            DB::table($this->exitTable)->where('id', $exit->id)->update([
                'status' => 'exit_completed',
                'final_status' => 'completed',
                'completed_by_user_id' => $actorUserId,
                'approved_by_user_id' => $actorUserId,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable($this->employeeTable)) {
                $finalStatus = in_array((string) $exit->exit_type, ['termination'], true) ? 'terminated' : 'exited';
                DB::table($this->employeeTable)->where('id', $exit->employee_id)->update([
                    'employment_status' => $finalStatus,
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }

            $employee = DB::table($this->employeeTable)->where('id', $exit->employee_id)->first();
            if ($employee && ! empty($employee->user_id)) {
                $user = DB::table('users')->where('id', $employee->user_id)->first();
                if ($user && $user->is_active) {
                    $this->disableUser((int) $employee->user_id);
                    DB::table($this->exitTable)->where('id', $exit->id)->update([
                        'login_disabled_by_exit' => 1,
                    ]);
                }
            }
        });

        $this->notifications->notifyHrAndSuperAdmin(
            'Employee Exit Completed',
            'Exit workflow completed for employee #' . $exit->employee_id . '.',
            'employee_exit_completed',
            'hrms.employees.exit',
            [],
            ['employee_id' => $exit->employee_id, 'exit_process_id' => $exit->id]
        );

        $employee = DB::table($this->employeeTable)->where('id', $exit->employee_id)->first();
        if ($employee && ! empty($employee->user_id)) {
            $this->notifications->notifyEmployee(
                'Exit Approved & Processed',
                'Your exit process has been successfully approved and completed by HR.',
                'employee_exit_completed',
                null,
                [],
                ['employee_id' => $exit->employee_id, 'exit_process_id' => $exit->id],
                (int) $employee->user_id
            );
        }

        return (array) DB::table($this->exitTable)->where('id', $exitId)->first();
    }

    public function cancel(int $exitId, int $actorUserId, ?string $remarks = null): array
    {
        return DB::transaction(function () use ($exitId, $actorUserId, $remarks) {
            $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
            abort_if(! $exit, 404, 'Exit process not found.');

            DB::table($this->exitTable)->where('id', $exitId)->update([
                'status' => 'cancelled',
                'final_status' => 'cancelled',
                'remarks' => $remarks ?: $exit->remarks,
                'updated_at' => now(),
            ]);

            // Restore employee status and remove exit flags
            if (Schema::hasTable($this->employeeTable)) {
                $employee = DB::table($this->employeeTable)->where('id', $exit->employee_id)->first();
                if ($employee) {
                    // Safety Rule: Restore previous valid active state only if the Exit Process changed it.
                    $targetStatus = $exit->previous_employment_status ?: 'active';
                    
                    $updateData = [
                        'employment_status' => $targetStatus,
                        'relieving_date' => null,
                        'updated_at' => now(),
                    ];
                    if (Schema::hasColumn($this->employeeTable, 'is_active')) {
                        $updateData['is_active'] = 1;
                    }
                    if (Schema::hasColumn($this->employeeTable, 'notice_status')) {
                        $updateData['notice_status'] = null;
                    }
                    DB::table($this->employeeTable)->where('id', $exit->employee_id)->update($updateData);

                    // Safety Rule: Restore login ONLY IF the Exit Process itself disabled the login.
                    if (!empty($employee->user_id) && $exit->login_disabled_by_exit) {
                        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
                            DB::table('users')->where('id', $employee->user_id)->update([
                                'is_active' => 1,
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // Create an audit log: "Exit Process Cancelled" in employee_lifecycle_logs
            if (Schema::hasTable('employee_lifecycle_logs')) {
                DB::table('employee_lifecycle_logs')->insert([
                    'employee_id' => $exit->employee_id,
                    'action' => 'Exit Process Cancelled',
                    'old_value' => json_encode(['status' => $exit->status, 'final_status' => $exit->final_status]),
                    'new_value' => json_encode(['status' => 'cancelled', 'final_status' => 'cancelled']),
                    'remarks' => $remarks ?: $exit->remarks,
                    'performed_by_user_id' => $actorUserId,
                    'performed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return (array) DB::table($this->exitTable)->where('id', $exitId)->first();
        });
    }

    public function updateClearance(int $exitId, array $payload, int $actorUserId): array
    {
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        abort_if(! $exit, 404, 'Exit process not found.');

        $updates = [
            'updated_at' => now(),
        ];

        if (array_key_exists('asset_status', $payload) && $payload['asset_status'] !== null) {
            $asset = strtolower((string) $payload['asset_status']);
            if (in_array($asset, ['pending', 'cleared', 'waived'], true)) {
                $updates['asset_status'] = $asset;
                $updates['asset_handover_status'] = $asset === 'waived' ? 'waived' : ($asset === 'cleared' ? 'completed' : 'pending');
            }
        }

        if (array_key_exists('fnf_status', $payload) && $payload['fnf_status'] !== null) {
            $fnf = strtolower((string) $payload['fnf_status']);
            if (in_array($fnf, ['pending', 'processing', 'approved', 'paid', 'completed', 'waived'], true)) {
                $updates['fnf_status'] = $fnf;
            }
        }

        if (array_key_exists('document_status', $payload) && $payload['document_status'] !== null) {
            $doc = strtolower((string) $payload['document_status']);
            if (in_array($doc, ['pending', 'generated', 'sent', 'completed', 'waived'], true)) {
                $updates['document_status'] = $doc;
            }
        }

        if (array_key_exists('handover_status', $payload) && $payload['handover_status'] !== null) {
            $handover = strtolower((string) $payload['handover_status']);
            if (in_array($handover, ['pending', 'cleared', 'completed', 'waived'], true)) {
                $updates['handover_status'] = $handover;
            }
        }

        if (array_key_exists('remarks', $payload) && $payload['remarks'] !== null) {
            $updates['remarks'] = trim((string) $payload['remarks']) ?: $exit->remarks;
        }

        DB::table($this->exitTable)->where('id', $exitId)->update($updates);

        return $this->refreshStatus($exitId);
    }

    private function hasPendingAssets(int $employeeId): bool
    {
        if (! Schema::hasTable('asset_allocations')) {
            return false;
        }

        return DB::table('asset_allocations')
            ->where('employee_id', $employeeId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Returned');
            })
            ->exists();
    }

    private function isFnfCompleted(int $employeeId, int $exitId): bool
    {
        if (Schema::hasTable('enterprise_fnf_settlements')) {
            return DB::table('enterprise_fnf_settlements')
                ->where('employee_id', $employeeId)
                ->where(function ($q) use ($exitId) {
                    $q->whereNull('exit_process_id')->orWhere('exit_process_id', $exitId);
                })
                ->whereIn('status', ['approved', 'paid', 'completed'])
                ->exists();
        }

        if (Schema::hasTable('fnf_settlements')) {
            return DB::table('fnf_settlements')->where('employee_id', $employeeId)->exists();
        }

        return false;
    }

    private function isDocumentCompleted(int $employeeId, string $exitType): bool
    {
        if (! Schema::hasTable('generated_documents')) {
            return false;
        }

        $required = match ($exitType) {
            'internship_completed', 'internship_exit' => ['internship_certificate'],
            'termination' => ['termination_letter'],
            'absconding' => [],
            default => ['relieving_letter', 'experience_letter'],
        };

        if (empty($required)) {
            return true;
        }

        $count = DB::table('generated_documents')
            ->where('employee_id', $employeeId)
            ->whereIn('document_type', $required)
            ->whereIn('status', ['generated', 'sent', 'reviewed'])
            ->distinct('document_type')
            ->count('document_type');

        return $count >= count($required);
    }

    private function disableUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
            DB::table('users')->where('id', $userId)->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\Core\\UserM')
                ->where('tokenable_id', $userId)
                ->delete();
        }
    }

    public function initializeClearances(int $exitId): void
    {
        if (! Schema::hasTable('employee_exit_clearances')) {
            return;
        }

        $defaultChecklists = [
            'hr' => [
                ['item' => 'Documents verified', 'completed' => false],
                ['item' => 'Leave balance verified', 'completed' => false],
                ['item' => 'Exit interview completed', 'completed' => false],
            ],
            'manager' => [
                ['item' => 'Knowledge transfer completed', 'completed' => false],
                ['item' => 'Handover of tasks done', 'completed' => false],
            ],
            'it' => [
                ['item' => 'Laptop returned', 'completed' => false],
                ['item' => 'Email disabled', 'completed' => false],
                ['item' => 'VPN disabled', 'completed' => false],
                ['item' => 'Access revoked', 'completed' => false],
            ],
            'admin' => [
                ['item' => 'ID Card returned', 'completed' => false],
                ['item' => 'Parking card returned', 'completed' => false],
                ['item' => 'Office keys returned', 'completed' => false],
            ],
            'finance' => [
                ['item' => 'Salary hold check', 'completed' => false],
                ['item' => 'Loan recovery', 'completed' => false],
                ['item' => 'Advance recovery', 'completed' => false],
                ['item' => 'Reimbursement verification', 'completed' => false],
            ],
            'asset' => [
                ['item' => 'Mobile returned', 'completed' => false],
                ['item' => 'Laptop returned', 'completed' => false],
                ['item' => 'Accessories returned', 'completed' => false],
            ],
            'security' => [
                ['item' => 'Access card deactivated', 'completed' => false],
            ],
            'accounts' => [
                ['item' => 'Final ledger reconciliation', 'completed' => false],
            ],
        ];

        foreach ($defaultChecklists as $dept => $items) {
            DB::table('employee_exit_clearances')->insertOrIgnore([
                'exit_process_id' => $exitId,
                'department_key' => $dept,
                'status' => 'pending',
                'checklist' => json_encode($items),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function updateDepartmentClearance(int $exitId, string $dept, string $status, ?string $remarks, ?array $checklistItems, int $actorUserId): array
    {
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        abort_if(! $exit, 404, 'Exit process not found.');

        $clearance = DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->where('department_key', $dept)
            ->first();
        abort_if(! $clearance, 404, 'Clearance record not found for department.');

        $update = [
            'status' => $status,
            'remarks' => $remarks,
            'approved_by_user_id' => $actorUserId,
            'approved_at' => now(),
            'updated_at' => now(),
        ];

        if ($checklistItems !== null) {
            $update['checklist'] = json_encode($checklistItems);
        }

        DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->where('department_key', $dept)
            ->update($update);

        if ($status === 'approved') {
            $logAction = match ($dept) {
                'hr' => 'HR Cleared',
                'it' => 'IT Cleared',
                'finance' => 'Finance Cleared',
                'asset' => 'Assets Cleared',
                default => null,
            };

            if ($logAction && Schema::hasTable('employee_lifecycle_logs')) {
                DB::table('employee_lifecycle_logs')->insert([
                    'employee_id' => $exit->employee_id,
                    'action' => $logAction,
                    'remarks' => $remarks,
                    'performed_by_user_id' => $actorUserId,
                    'performed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($status === 'approved' && !empty($exit->employee_id)) {
            $employee = DB::table($this->employeeTable)->where('id', $exit->employee_id)->first();
            if ($employee && !empty($employee->user_id)) {
                $deptLabel = strtoupper($dept);
                if ($dept === 'manager') {
                    $deptLabel = 'Reporting Manager';
                }
                $this->notifications->notifyEmployee(
                    'Department Clearance Approved',
                    "Your clearance for department {$deptLabel} has been approved by HR/Admin.",
                    'clearance_approved',
                    null,
                    [],
                    ['employee_id' => $exit->employee_id, 'exit_process_id' => $exitId],
                    (int) $employee->user_id
                );
            }
        }

        $this->checkAndCompleteClearances($exitId, $actorUserId);

        return $this->refreshStatus($exitId);
    }

    public function checkAndCompleteClearances(int $exitId, int $actorUserId): void
    {
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        if (! $exit) {
            return;
        }

        $totalDepts = DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->count();

        $approvedDepts = DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->where('status', 'approved')
            ->count();

        if ($totalDepts > 0 && $totalDepts === $approvedDepts) {
            if (Schema::hasTable('employee_lifecycle_logs')) {
                $completedExists = DB::table('employee_lifecycle_logs')
                    ->where('employee_id', $exit->employee_id)
                    ->where('action', 'Clearance Completed')
                    ->exists();

                if (!$completedExists) {
                    DB::table('employee_lifecycle_logs')->insert([
                        'employee_id' => $exit->employee_id,
                        'action' => 'Clearance Completed',
                        'performed_by_user_id' => $actorUserId,
                        'performed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('employee_lifecycle_logs')->insert([
                        'employee_id' => $exit->employee_id,
                        'action' => 'Ready For Final Settlement',
                        'performed_by_user_id' => $actorUserId,
                        'performed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->notifications->notifyHrAndSuperAdmin(
                'Exit Clearance Completed',
                "All department clearances have been completed for employee #{$exit->employee_id}.",
                'exit_clearance_completed',
                'hrms.employees.exit',
                [],
                ['employee_id' => $exit->employee_id, 'exit_process_id' => $exitId]
            );
        }
    }

    public function getModuleSummary(int $employeeId, ?object $exit = null): array
    {
        $summary = [
            'attendance_pending' => 0,
            'leave_remaining' => 0,
            'assets_assigned' => 0,
            'payroll_pending' => 0,
            'documents_count' => 0,
            'loans_pending' => 0,
            'wfh_pending' => 0,
            'holiday_work_pending' => 0,
            'notice_days_remaining' => 0,
        ];

        if (Schema::hasTable('attendance_regularizations')) {
            $summary['attendance_pending'] += DB::table('attendance_regularizations')
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
        }
        if (Schema::hasTable('attendance_violations')) {
            $summary['attendance_pending'] += DB::table('attendance_violations')
                ->where('employee_id', $employeeId)
                ->count();
        }

        if (Schema::hasTable('leave_allocations')) {
            $summary['leave_remaining'] = (float) DB::table('leave_allocations')
                ->where('employee_id', $employeeId)
                ->orderByDesc('year')
                ->value('total_remaining') ?? 0;
        }

        if (Schema::hasTable('asset_allocations')) {
            $summary['assets_assigned'] = DB::table('asset_allocations')
                ->where('employee_id', $employeeId)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Returned');
                })
                ->count();
        }

        if (Schema::hasTable('enterprise_payrolls')) {
            $summary['payroll_pending'] += DB::table('enterprise_payrolls')
                ->where('employee_id', $employeeId)
                ->whereNotIn('status', ['paid', 'approved', 'completed'])
                ->count();
        }
        if (Schema::hasTable('payrolls')) {
            $summary['payroll_pending'] += DB::table('payrolls')
                ->where('employee_id', $employeeId)
                ->whereNotIn('status', ['paid', 'approved', 'completed'])
                ->count();
        }

        if (Schema::hasTable('generated_documents')) {
            $summary['documents_count'] = DB::table('generated_documents')
                ->where('employee_id', $employeeId)
                ->count();
        }

        if (Schema::hasTable('enterprise_payroll_adjustments')) {
            $summary['loans_pending'] += DB::table('enterprise_payroll_adjustments')
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
        }
        if (Schema::hasTable('payroll_adjustments')) {
            $summary['loans_pending'] += DB::table('payroll_adjustments')
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
        }

        if (Schema::hasTable('wfh_requests')) {
            $summary['wfh_pending'] = DB::table('wfh_requests')
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
        }

        if (Schema::hasTable('holiday_work_requests')) {
            $summary['holiday_work_pending'] += DB::table('holiday_work_requests')
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
        }

        if ($exit && !empty($exit->last_working_day)) {
            $lastWorking = \Carbon\Carbon::parse($exit->last_working_day)->startOfDay();
            $today = now()->startOfDay();
            if ($lastWorking->isAfter($today)) {
                $summary['notice_days_remaining'] = (int) $today->diffInDays($lastWorking);
            }
        }

        return $summary;
    }
}
