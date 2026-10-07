<?php

namespace App\Console\Commands;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateScheduledPermanentEmployees extends Command
{
    protected $signature = 'hrms:activate-scheduled-permanent';
    protected $description = 'Automatically activates scheduled permanent employees on their confirmation effective date';

    public function handle(): int
    {
        $today = Carbon::today('Asia/Kolkata');
        $this->info('Starting scheduled permanent activations for: ' . $today->toDateString());

        $candidates = DB::table('employees_new')
            ->where('employee_stage', 'probation')
            ->where(function ($q) use ($today) {
                $q->where(function ($scheduled) use ($today) {
                    $scheduled->where('probation_status', 'scheduled_permanent')
                        ->whereNotNull('confirmation_effective_date')
                        ->where('confirmation_effective_date', '<=', $today->toDateString());
                })->orWhere(function ($expired) use ($today) {
                    $expired->where(function ($status) {
                        $status->whereNotIn('probation_status', ['completed', 'confirmed', 'extended', 'scheduled_permanent'])
                            ->orWhereNull('probation_status');
                    })
                        ->whereNotNull('probation_end_date')
                        ->where('probation_end_date', '<', $today->toDateString());
                });
            });

        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $lifecycle = app(EmployeeLifecycleService::class);
        $candidates->select(['id', 'probation_status', 'confirmation_effective_date'])
            ->chunkById(250, function ($batch) use ($lifecycle, &$successCount, &$skippedCount, &$failedCount) {
                foreach ($batch as $candidate) {
                    try {
                        $source = $candidate->probation_status === 'scheduled_permanent' ? 'scheduled' : 'auto_expiry';
                        $result = $lifecycle->activatePermanent(
                            new EmployeeM(['id' => (int) $candidate->id]),
                            (string) ($candidate->confirmation_effective_date ?? ''),
                            $source
                        );
                        if ($result['status'] === 'activated') $successCount++;
                        else $skippedCount++;
                    } catch (\Throwable $e) {
                        $failedCount++;
                        Log::error("Scheduled permanent activation failed for employee ID {$candidate->id}: " . $e->getMessage(), ['exception' => $e]);
                    }
                }
            }, 'id');

        $this->info("Permanent activation complete. Activated: {$successCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");
        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
