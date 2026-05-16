<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->year('year');
            $table->decimal('total_allocated', 8, 2)->default(0);
            $table->decimal('paid_allocated', 8, 2)->default(0);
            $table->decimal('sick_allocated', 8, 2)->default(0);
            $table->decimal('comp_off_allocated', 8, 2)->default(0);
            $table->decimal('total_used', 8, 2)->default(0);
            $table->decimal('paid_used', 5, 2)->default(0);
            $table->decimal('sick_used', 5, 2)->default(0);
            $table->decimal('comp_off_used', 8, 2)->default(0);
            $table->decimal('lwp_used', 5, 2)->default(0);
            $table->decimal('total_remaining', 8, 2)->default(0);
            $table->decimal('paid_remaining', 8, 2)->default(0);
            $table->decimal('sick_remaining', 8, 2)->default(0);
            $table->decimal('comp_off_remaining', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_allocations');
    }
}
