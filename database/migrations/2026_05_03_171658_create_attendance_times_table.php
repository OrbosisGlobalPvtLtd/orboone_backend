<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTimesTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_times', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            $table->time('punch_allowed_from')->default('09:00:00');
            $table->time('shift_start_time')->default('10:00:00');
            $table->time('late_after_time')->default('11:15:00');
            $table->time('half_day_after_time')->nullable();
            $table->time('shift_end_time')->default('19:00:00');

            $table->unsignedInteger('required_work_minutes')->default(480);
            $table->unsignedInteger('half_day_min_minutes')->default(240);
            $table->unsignedInteger('lunch_break_minutes')->default(60);

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_times');
    }
}