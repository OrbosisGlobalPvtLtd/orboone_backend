<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('employee_id')->index();

            $table->unsignedBigInteger('attendance_time_id')->nullable()->index();
            $table->unsignedBigInteger('attendance_type_id')->nullable()->index();

            $table->date('attendance_date')->index();

            $table->time('punch_in_time')->nullable();
            $table->time('punch_out_time')->nullable();

            $table->enum('work_mode', ['wfo', 'wfh'])->nullable();

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

            $table->unsignedInteger('gross_work_minutes')->default(0);
            $table->unsignedInteger('lunch_break_minutes')->default(0);
            $table->unsignedInteger('total_work_minutes')->default(0);

            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('late_minutes')->default(0);

            $table->boolean('is_early_out')->default(false);
            $table->unsignedInteger('early_out_minutes')->default(0);

            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();

            $table->unsignedBigInteger('hr_approved_by')->nullable()->index();
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_approval_note')->nullable();

            $table->boolean('is_profile_completed_at_punch')->default(false);
            $table->boolean('is_locked')->default(true);

            $table->text('punch_in_note')->nullable();
            $table->text('punch_out_note')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date'], 'attendance_employee_date_unique');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees_new')->onDelete('cascade');
            $table->foreign('attendance_time_id')->references('id')->on('attendance_times')->nullOnDelete();
            $table->foreign('attendance_type_id')->references('id')->on('attendance_types')->nullOnDelete();
            $table->foreign('hr_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}