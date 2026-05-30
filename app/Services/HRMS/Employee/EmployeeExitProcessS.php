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

        $record = [
            'employee_id' => $employeeId,
            'exit_type' => $exitType,
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

        $openExit = DB::table($this->exitTable)
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['exit_completed', 'cancelled'])
            ->orderByDesc('id')
            ->first();

        if ($openExit) {
            DB::table($this->exitTable)->where('id', $openExit->id)->update($record);
            $exitId = (int) $openExit->id;
        } else {
            $record['created_at'] = now();
            $exitId = (int) DB::table($this->exitTable)->insertGetId($record);
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

        $immediateDisable = (bool) ($payload['immediate_disable_login'] ?? false);
        if (in_array($exitType, ['termination', 'absconding'], true) && ($immediateExit || $immediateDisable)) {
            $this->disableUser((int) $employee->user_id);
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
                $this->disableUser((int) $employee->user_id);
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
        $exit = DB::table($this->exitTable)->where('id', $exitId)->first();
        abort_if(! $exit, 404, 'Exit process not found.');

        DB::table($this->exitTable)->where('id', $exitId)->update([
            'status' => 'cancelled',
            'final_status' => 'cancelled',
            'remarks' => $remarks ?: $exit->remarks,
            'updated_at' => now(),
        ]);

        return (array) DB::table($this->exitTable)->where('id', $exitId)->first();
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
}
