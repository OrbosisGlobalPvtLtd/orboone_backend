<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Leave\CompOffService;
use Illuminate\Console\Command;

class ExpireCompOffs extends Command
{
    protected $signature = 'hrms:comp-offs-expire';
    protected $description = 'Expire due comp off balances.';

    public function handle(CompOffService $compOffService): int
    {
        $count = $compOffService->expireDue();
        $this->info("Expired {$count} comp off record(s).");
        return self::SUCCESS;
    }
}
