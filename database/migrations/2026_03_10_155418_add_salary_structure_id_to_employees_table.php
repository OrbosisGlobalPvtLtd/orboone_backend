<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryStructureIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'salary_structure_id')) {
                $table->unsignedBigInteger('salary_structure_id')->nullable()->after('position_id');
                $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('set null');
            }
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
            $table->dropForeign(['salary_structure_id']);
            $table->dropColumn('salary_structure_id');
        });
    }
}
