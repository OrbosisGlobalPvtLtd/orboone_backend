<?php

namespace App\Console\Commands;

use App\Services\HRMS\Attendance\AttendanceSyncFromLeaveService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttendanceLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:leave {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync approved employee leave records into daily attendance.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param AttendanceSyncFromLeaveService $syncService
     * @return int
     */
    public function handle(AttendanceSyncFromLeaveService $syncService): int
    {
        $dateStr = $this->argument('date') ?: Carbon::now('Asia/Kolkata')->toDateString();
        $count = $syncService->syncDailyApprovedLeaves($dateStr);

        $this->info("Synced {$count} approved employee leaves for {$dateStr}.");
        return self::SUCCESS;
    }
}
