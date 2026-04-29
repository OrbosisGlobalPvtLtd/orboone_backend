<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailedFieldsToPayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('basic', 10, 2)->default(0)->after('employee_id');
            $table->decimal('hra', 10, 2)->default(0)->after('basic');
            $table->decimal('allowance', 10, 2)->default(0)->after('hra');
            $table->decimal('pt', 10, 2)->default(0)->after('total_deductions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['basic', 'hra', 'allowance', 'pt']);
        });
    }
}
