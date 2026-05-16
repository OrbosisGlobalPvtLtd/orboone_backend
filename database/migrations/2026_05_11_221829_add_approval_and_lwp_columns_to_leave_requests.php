<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'approval_level')) {
                $table->string('approval_level', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('leave_requests', 'manager_approved_by')) {
                $table->unsignedBigInteger('manager_approved_by')->nullable()->after('approval_level');
            }

            if (!Schema::hasColumn('leave_requests', 'manager_approved_at')) {
                $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_approved_by')) {
                $table->unsignedBigInteger('hr_approved_by')->nullable()->after('manager_approved_at');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_approved_at')) {
                $table->timestamp('hr_approved_at')->nullable()->after('hr_approved_by');
            }

            if (!Schema::hasColumn('leave_requests', 'auto_converted_to_lwp')) {
                $table->boolean('auto_converted_to_lwp')->default(false)->after('lwp_days');
            }

            if (!Schema::hasColumn('leave_requests', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('leave_requests', 'cancelled_by_user_id')) {
                $table->unsignedBigInteger('cancelled_by_user_id')->nullable()->after('cancel_reason');
            }

            if (!Schema::hasColumn('leave_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            foreach (
                [
                    'cancelled_at',
                    'cancelled_by_user_id',
                    'cancel_reason',
                    'auto_converted_to_lwp',
                    'hr_approved_at',
                    'hr_approved_by',
                    'manager_approved_at',
                    'manager_approved_by',
                    'approval_level',
                ] as $column
            ) {
                if (Schema::hasColumn('leave_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
