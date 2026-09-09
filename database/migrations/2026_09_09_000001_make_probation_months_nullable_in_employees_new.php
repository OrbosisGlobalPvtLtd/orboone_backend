<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class MakeProbationMonthsNullableInEmployeesNew extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_months')) {
            Schema::table('employees_new', function (Blueprint $table) {
               
                $table->integer('probation_months')->nullable()->default(3)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'probation_months')) {
            Schema::table('employees_new', function (Blueprint $table) {
             
                \Illuminate\Support\Facades\DB::table('employees_new')
                    ->whereNull('probation_months')
                    ->update(['probation_months' => 0]);

                $table->integer('probation_months')->nullable(false)->default(3)->change();
            });
        }
    }
}
