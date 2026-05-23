<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkSummaryJsonToAttendanceWorkLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_work_logs', function (Blueprint $table) {
            $table->json('work_summary_json')->nullable()->after('work_summary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_work_logs', function (Blueprint $table) {
            $table->dropColumn('work_summary_json');
        });
    }
}
