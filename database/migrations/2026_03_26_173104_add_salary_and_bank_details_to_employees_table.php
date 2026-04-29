<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryAndBankDetailsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Unify Statuses & Work Mode
            if (!Schema::hasColumn('employees', 'employee_status')) {
                $table->string('employee_status')->default('WFO')->after('status'); // WFH/WFO
            }

            // New Functional Fields
            $table->decimal('actual_salary', 15, 2)->nullable()->after('employee_status');
            $table->string('employment_status')->default('Active')->after('actual_salary'); // Active, Resigned, Terminated
            
            // Bank Details
            $table->string('bank_name')->nullable()->after('employment_status');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_type')->nullable()->after('account_number'); // Savings, Current
            $table->string('holder_name')->nullable()->after('account_type');
            $table->string('ifsc_code')->nullable()->after('holder_name');
            $table->string('branch_name')->nullable()->after('ifsc_code');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'employee_status',
                'actual_salary',
                'employment_status',
                'bank_name',
                'account_number',
                'account_type',
                'holder_name',
                'ifsc_code',
                'branch_name'
            ]);
        });
    }
}
