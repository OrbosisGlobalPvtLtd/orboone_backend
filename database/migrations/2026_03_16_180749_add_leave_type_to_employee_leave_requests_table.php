<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveTypeToEmployeeLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave_requests', 'leave_type')) {
                $table->string('leave_type')->nullable()->after('employee_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_leave_requests', function (Blueprint $table) {
            //
        });
    }
}
