<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceLogsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            
            $table->string('log_type')->comment('punch_in, punch_out, etc');
            $table->timestamp('time')->index();
            
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->string('device_info')->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees_new')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
}
