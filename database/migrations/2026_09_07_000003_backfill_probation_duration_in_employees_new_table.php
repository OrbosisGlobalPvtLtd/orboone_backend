<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_duration_value')) {
            DB::table('employees_new')
                ->whereNotNull('probation_months')
                ->whereNull('probation_duration_value')
                ->update([
                    'probation_duration_type' => 'months',
                    'probation_duration_value' => DB::raw('probation_months'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_duration_value')) {
            DB::table('employees_new')
                ->where('probation_duration_type', 'months')
                ->whereColumn('probation_duration_value', 'probation_months')
                ->update([
                    'probation_duration_value' => null,
                ]);
        }
    }
};
