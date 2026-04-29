<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryFieldsToSalaryStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->default(0)->after('name');
            $table->decimal('hra_percent', 5, 2)->default(0)->after('basic_salary');
            $table->decimal('allowance', 10, 2)->default(0)->after('hra_percent');
            $table->decimal('pt_amount', 10, 2)->default(0)->after('allowance');
            $table->date('effective_date')->nullable()->after('pt_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'hra_percent', 'allowance', 'pt_amount', 'effective_date']);
        });
    }
}
