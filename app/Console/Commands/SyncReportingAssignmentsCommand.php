<?php

namespace App\Console\Commands;

use App\Services\HRMS\Reporting\ReportingManagerAssignmentService;
use Illuminate\Console\Command;

class SyncReportingAssignmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:sync-reporting-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize employees_new.reporting_manager_employee_id into reporting_assignments table';

    /**
     * Execute the console command.
     */
    public function handle(ReportingManagerAssignmentService $service): int
    {
        $this->info('Starting Reporting Manager assignments synchronization...');
        $count = $service->syncAllExistingEmployees();
        $this->info("Successfully synchronized {$count} employee reporting manager assignment(s).");
        return 0;
    }
}
