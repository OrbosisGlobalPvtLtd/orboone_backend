<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('wfh_requests')) {
            $existingIndexes = collect(Schema::getIndexes('wfh_requests'))->pluck('name')->all();

            Schema::table('wfh_requests', function (Blueprint $table) use ($existingIndexes) {
                if (in_array('wfh_emp_date_unique', $existingIndexes, true)) {
                    $table->dropUnique('wfh_emp_date_unique');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wfh_requests')) {
            $existingIndexes = collect(Schema::getIndexes('wfh_requests'))->pluck('name')->all();

            Schema::table('wfh_requests', function (Blueprint $table) use ($existingIndexes) {
                if (! in_array('wfh_emp_date_unique', $existingIndexes, true)) {
                    $table->unique(['employee_id', 'request_date'], 'wfh_emp_date_unique');
                }
            });
        }
    }
};

