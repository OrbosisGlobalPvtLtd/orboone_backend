<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlockAfterTimeToEmployeeShiftTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_shift_timings', function (Blueprint $table) {
            $table->time('block_after_time')->nullable()->after('half_day_after_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_shift_timings', function (Blueprint $table) {
            $table->dropColumn('block_after_time');
        });
    }
}
