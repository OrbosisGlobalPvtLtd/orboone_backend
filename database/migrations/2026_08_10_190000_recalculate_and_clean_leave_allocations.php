<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Services\HRMS\Leave\LeaveAllocationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allocationService = app(LeaveAllocationService::class);
        $allocations = LeaveAllocationM::all();

        foreach ($allocations as $allocation) {
            $allocationService->recalculateAllocationFields($allocation);
            $allocation->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse required as this cleans up invalid balances
    }
};
