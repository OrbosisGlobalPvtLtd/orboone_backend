<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wfh_requests')) {
            return;
        }

        Schema::table('wfh_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('wfh_requests', 'from_date')) {
                $table->date('from_date')->nullable()->after('request_date');
            }
            if (! Schema::hasColumn('wfh_requests', 'to_date')) {
                $table->date('to_date')->nullable()->after('from_date');
            }
            if (! Schema::hasColumn('wfh_requests', 'batch_id')) {
                $table->string('batch_id', 64)->nullable()->after('to_date');
                $table->index('batch_id', 'wfh_batch_idx');
            }
            if (! Schema::hasColumn('wfh_requests', 'total_days')) {
                $table->unsignedInteger('total_days')->default(1)->after('batch_id');
            }
            if (! Schema::hasColumn('wfh_requests', 'working_days')) {
                $table->unsignedInteger('working_days')->default(1)->after('total_days');
            }
            if (! Schema::hasColumn('wfh_requests', 'weekoff_days')) {
                $table->unsignedInteger('weekoff_days')->default(0)->after('working_days');
            }
            if (! Schema::hasColumn('wfh_requests', 'holiday_days')) {
                $table->unsignedInteger('holiday_days')->default(0)->after('weekoff_days');
            }
            if (! Schema::hasColumn('wfh_requests', 'actual_wfh_days')) {
                $table->unsignedInteger('actual_wfh_days')->default(1)->after('holiday_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wfh_requests')) {
            return;
        }

        Schema::table('wfh_requests', function (Blueprint $table) {
            if (Schema::hasColumn('wfh_requests', 'batch_id')) {
                $table->dropIndex('wfh_batch_idx');
                $table->dropColumn('batch_id');
            }
            $columnsToDrop = ['from_date', 'to_date', 'total_days', 'working_days', 'weekoff_days', 'holiday_days', 'actual_wfh_days'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('wfh_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
