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
            if (Schema::hasColumn('wfh_requests', 'today_tasks')) {
                $table->dropColumn('today_tasks');
            }
            if (Schema::hasColumn('wfh_requests', 'completed_tasks')) {
                $table->dropColumn('completed_tasks');
            }
            if (Schema::hasColumn('wfh_requests', 'work_summary')) {
                $table->dropColumn('work_summary');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wfh_requests')) {
            return;
        }

        Schema::table('wfh_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('wfh_requests', 'today_tasks')) {
                $table->text('today_tasks')->nullable()->after('payroll_impact');
            }
            if (! Schema::hasColumn('wfh_requests', 'completed_tasks')) {
                $table->text('completed_tasks')->nullable()->after('today_tasks');
            }
            if (! Schema::hasColumn('wfh_requests', 'work_summary')) {
                $table->text('work_summary')->nullable()->after('completed_tasks');
            }
        });
    }
};

