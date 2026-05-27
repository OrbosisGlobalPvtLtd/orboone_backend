<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Services\HRMS\Leave\CompOffService;
use Illuminate\Console\Command;

class ProcessHolidayWorkCompOffs extends Command
{
    protected $signature = 'hrms:process-holiday-work-comp-offs';
    protected $description = 'Process approved holiday/weekoff work requests, validate attendance, and generate comp offs.';

    public function handle(CompOffService $compOffService): int
    {
        $today = now('Asia/Kolkata')->toDateString();
        $processedCount = 0;
        $scanned = 0;
        $query = HolidayWorkRequestM::query()
            ->where('status', 'approved')
            ->where('comp_off_generated', false)
            ->whereDate('worked_date', '<=', $today)
            ->orderBy('id');

        $query->chunkById(100, function ($requests) use ($compOffService, &$processedCount, &$scanned) {
            foreach ($requests as $request) {
                $scanned++;
                try {
                    $success = $compOffService->validateAndProcessRequest($request->fresh(['employee']));
                    if ($success) {
                        $processedCount++;
                        $this->info("Generated comp off for employee ID {$request->employee_id} on {$request->worked_date}");
                    }
                } catch (\Throwable $e) {
                    \Log::error('Holiday work comp-off processing failed', [
                        'request_id' => $request->id,
                        'employee_id' => $request->employee_id,
                        'worked_date' => (string) $request->worked_date,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Completed processing. Scanned {$scanned} approved request(s), generated {$processedCount} comp off(s).");
        return self::SUCCESS;
    }
}
