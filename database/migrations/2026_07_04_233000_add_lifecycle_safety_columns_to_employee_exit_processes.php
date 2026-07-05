<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_exit_processes')) {
            Schema::table('employee_exit_processes', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_exit_processes', 'previous_employment_status')) {
                    $table->string('previous_employment_status', 50)->nullable()->after('exit_type');
                }
                if (! Schema::hasColumn('employee_exit_processes', 'login_disabled_by_exit')) {
                    $table->boolean('login_disabled_by_exit')->default(false)->after('previous_employment_status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_exit_processes')) {
            Schema::table('employee_exit_processes', function (Blueprint $table) {
                if (Schema::hasColumn('employee_exit_processes', 'previous_employment_status')) {
                    $table->dropColumn('previous_employment_status');
                }
                if (Schema::hasColumn('employee_exit_processes', 'login_disabled_by_exit')) {
                    $table->dropColumn('login_disabled_by_exit');
                }
            });
        }
    }
};
