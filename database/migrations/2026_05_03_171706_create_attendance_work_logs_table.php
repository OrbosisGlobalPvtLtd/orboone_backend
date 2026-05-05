<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceWorkLogsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_work_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('attendance_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->date('work_date')->index();
            $table->text('work_summary');

            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('project_task_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees_new')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_work_logs');
    }
}