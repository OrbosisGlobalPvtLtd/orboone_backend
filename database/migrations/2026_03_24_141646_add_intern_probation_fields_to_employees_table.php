<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternProbationFieldsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('internship_type')->nullable(); // Paid Intern, Unpaid Intern
            $table->integer('internship_duration')->nullable(); // in months
            $table->date('internship_end_date')->nullable();
            
            $table->string('probation_status')->nullable(); // Probation, Permanent
            $table->date('probation_start_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->integer('probation_extension')->nullable(); // extension in months
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
            $table->dropColumn([
                'internship_type',
                'internship_duration',
                'internship_end_date',
                'probation_status',
                'probation_start_date',
                'probation_end_date',
                'probation_extension',
            ]);
        });
    }
}
