<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Leave\AutoExpireLeaveService;
use Illuminate\Console\Command;

class AutoExpirePendingLeavesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:expire-pending-leaves';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-expire any pending leave requests whose start dates have passed without approval or rejection.';

    /**
     * Execute the console command.
     */
    public function handle(AutoExpireLeaveService $autoExpireService)
    {
        $this->info('Checking for past pending leave requests to auto-expire...');
        
        $count = $autoExpireService->expirePastPendingRequests();

        $this->info("Auto-expiry complete. Total expired leave requests: {$count}");

        return Command::SUCCESS;
    }
}
