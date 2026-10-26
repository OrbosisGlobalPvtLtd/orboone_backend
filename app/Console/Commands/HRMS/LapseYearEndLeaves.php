<?php

namespace App\Console\Commands\HRMS;

use Illuminate\Console\Command;

class LapseYearEndLeaves extends Command
{
    protected $signature = 'hrms:leave-lapse-year-end {--year=}';
    protected $description = 'Retired legacy snapshot lapse command; ledger lapse owns permanent balances.';

    public function handle(): int
    {
        $this->warn('Legacy leave lapse is retired. Use hrms:leave-lapse-ledger. No allocation snapshots were changed.');
        return self::SUCCESS;
    }
}
