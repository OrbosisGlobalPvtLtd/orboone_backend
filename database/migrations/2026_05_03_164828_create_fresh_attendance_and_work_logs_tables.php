<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreshAttendanceAndWorkLogsTables extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('employee_id')->index();

            $table->date('attendance_date')->index();

            $table->time('punch_in_time')->nullable();
            $table->time('punch_out_time')->nullable();

            $table->enum('work_mode', ['wfo', 'wfh'])->nullable();

            $table->enum('attendance_status', [
                'present',
                'absent',
                'leave',
                'half_day',
                'holiday',
                'week_off',
            ])->default('present');

            $table->decimal('punch_in_latitude', 10, 7)->nullable();
            $table->decimal('punch_in_longitude', 10, 7)->nullable();
            $table->text('punch_in_address')->nullable();

            $table->decimal('punch_out_latitude', 10, 7)->nullable();
            $table->decimal('punch_out_longitude', 10, 7)->nullable();
            $table->text('punch_out_address')->nullable();

            $table->string('punch_in_ip', 100)->nullable();
            $table->string('punch_out_ip', 100)->nullable();

            $table->string('punch_in_device')->nullable();
            $table->string('punch_out_device')->nullable();

            $table->unsignedInteger('total_work_minutes')->default(0);

            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('late_minutes')->default(0);

            $table->boolean('is_early_out')->default(false);
            $table->unsignedInteger('early_out_minutes')->default(0);

            $table->unsignedInteger('total_break_minutes')->default(0);

            $table->boolean('is_profile_completed_at_punch')->default(false);

            $table->boolean('is_locked')->default(true);

            $table->text('punch_in_note')->nullable();
            $table->text('punch_out_note')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date'], 'attendance_employee_date_unique');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_new')
                ->onDelete('cascade');
        });

        Schema::create('attendance_work_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('attendance_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->date('work_date')->index();

            $table->text('work_summary');

            // Future Project Management linking
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('project_task_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('attendance_id')
                ->references('id')
                ->on('attendances')
                ->onDelete('cascade');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees_new')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_work_logs');
        Schema::dropIfExists('attendances');
    }
}