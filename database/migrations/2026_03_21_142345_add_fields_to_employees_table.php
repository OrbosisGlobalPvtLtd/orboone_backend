<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_id')->unique()->nullable()->after('id');
            $table->string('employment_type')->nullable()->after('name'); // intern, full-time, contract, freelancer
            $table->string('status')->default('Active')->after('employment_type'); // Active, Inactive, Probation, Completed
            $table->unsignedBigInteger('manager_id')->nullable()->after('head_of');
            
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['employee_id', 'employment_type', 'status', 'manager_id']);
        });
    }
}
