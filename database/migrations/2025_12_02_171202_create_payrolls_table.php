<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');

            $table->integer('month'); // 1-12
            $table->integer('year');  // 2025

            $table->integer('working_days')->default(0);
            $table->integer('paid_days')->default(0);

            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);

            $table->string('status')->default('Draft'); // Draft, Processed, Locked

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}
