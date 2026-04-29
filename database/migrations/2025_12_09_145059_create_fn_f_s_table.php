<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFnFSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('fnf_settlements', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('employee_id');
        $table->date('last_working_day');

        $table->decimal('pending_salary', 10, 2)->default(0);
        $table->decimal('leave_encashment', 10, 2)->default(0);
        $table->decimal('reimbursements', 10, 2)->default(0);
        $table->decimal('deductions', 10, 2)->default(0);

        $table->decimal('net_payable', 10, 2)->default(0);

        $table->timestamps();

        $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('fnf_settlements');
}

}
