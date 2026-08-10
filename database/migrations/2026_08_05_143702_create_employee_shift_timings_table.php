<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeShiftTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_shift_timings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('attendance_time_id');
            $table->time('punch_allowed_from')->nullable();
            $table->time('shift_start_time')->nullable();
            $table->time('late_after_time')->nullable();
            $table->time('half_day_after_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->integer('required_work_minutes')->nullable();
            $table->integer('lunch_minutes')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees_new')->onDelete('cascade');
            $table->foreign('attendance_time_id')->references('id')->on('attendance_times')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_shift_timings');
    }
}
