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
            if (! Schema::hasColumn('wfh_requests', 'lwp_reason')) {
                $table->text('lwp_reason')->nullable()->after('payroll_impact');
            }
            if (! Schema::hasColumn('wfh_requests', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('lwp_reason');
                $table->index('assigned_by', 'wfh_assigned_by_idx');
            }
            if (! Schema::hasColumn('wfh_requests', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wfh_requests')) {
            return;
        }

        Schema::table('wfh_requests', function (Blueprint $table) {
            if (Schema::hasColumn('wfh_requests', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
            if (Schema::hasColumn('wfh_requests', 'assigned_by')) {
                $table->dropIndex('wfh_assigned_by_idx');
                $table->dropColumn('assigned_by');
            }
            if (Schema::hasColumn('wfh_requests', 'lwp_reason')) {
                $table->dropColumn('lwp_reason');
            }
        });
    }
};

