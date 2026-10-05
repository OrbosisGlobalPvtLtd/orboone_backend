<?php

namespace App\Console\Commands;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateScheduledProbationEmployees extends Command
{
    protected $signature = 'hrms:activate-scheduled-probation';
    protected $description = 'Automatically activates scheduled probation employees when their probation start date is reached';

    public function handle(): int
    {
        $today = Carbon::today('Asia/Kolkata');
        $this->info('Starting scheduled probation activations for: ' . $today->toDateString());

        $candidates = DB::table('employees_new')
            ->where('internship_status', 'scheduled_probation')
            ->whereNotNull('probation_start_date')
            ->where('probation_start_date', '<=', $today->toDateString())
            ->where('employment_status', 'active')
            ->where('is_active', 1);

        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $lifecycle = app(EmployeeLifecycleService::class);

        $candidates->select(['id', 'internship_status', 'probation_start_date'])
            ->chunkById(250, function ($batch) use ($lifecycle, &$successCount, &$skippedCount, &$failedCount) {
                foreach ($batch as $candidate) {
                    try {
                        $result = $lifecycle->activateProbation(
                            new EmployeeM(['id' => (int) $candidate->id]),
                            (string) ($candidate->probation_start_date ?? ''),
                            'scheduled'
                        );
                        if (($result['status'] ?? '') === 'activated') {
                            $successCount++;
                        } else {
                            $skippedCount++;
                        }
                    } catch (\Throwable $e) {
                        $failedCount++;
                        Log::error("Scheduled probation activation failed for employee ID {$candidate->id}: " . $e->getMessage(), ['exception' => $e]);
                    }
                }
            }, 'id');

        $this->info("Probation activation complete. Activated: {$successCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");
        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
