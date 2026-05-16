<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_times', function (Blueprint $col) {
            if (!Schema::hasColumn('attendance_times', 'required_office_minutes')) {
                $col->integer('required_office_minutes')->default(540);
            }
            if (!Schema::hasColumn('attendance_times', 'half_day_min_minutes')) {
                $col->integer('half_day_min_minutes')->default(270);
            }
        });
    }

    public function down()
    {
    }
};
