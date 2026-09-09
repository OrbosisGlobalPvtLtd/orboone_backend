<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (! Schema::hasColumn('employees_new', 'probation_duration_type')) {
                    $table->enum('probation_duration_type', ['months', 'days'])->nullable()->default('months')->after('probation_months');
                }
                if (! Schema::hasColumn('employees_new', 'probation_duration_value')) {
                    $table->integer('probation_duration_value')->nullable()->after('probation_duration_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (Schema::hasColumn('employees_new', 'probation_duration_value')) {
                    $table->dropColumn('probation_duration_value');
                }
                if (Schema::hasColumn('employees_new', 'probation_duration_type')) {
                    $table->dropColumn('probation_duration_type');
                }
            });
        }
    }
};
