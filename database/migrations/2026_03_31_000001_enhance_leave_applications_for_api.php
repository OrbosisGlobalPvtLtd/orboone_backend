<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceLeaveApplicationsForApi extends Migration
{
    public function up()
    {
        // Add attachment and cancelled status to leave_applications
        Schema::table('leave_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_applications', 'attachment')) {
                $table->string('attachment')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('leave_applications', 'lwp_days')) {
                $table->decimal('lwp_days', 8, 2)->default(0)->after('total_days');
            }
            if (!Schema::hasColumn('leave_applications', 'month')) {
                $table->integer('month')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('leave_applications', 'year')) {
                $table->integer('year')->nullable()->after('month');
            }
        });

        // Modify status enum to include cancelled
        // We do this via raw SQL for safety
        DB::statement("ALTER TABLE leave_applications MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn(['attachment', 'lwp_days', 'month', 'year']);
        });
    }
}
