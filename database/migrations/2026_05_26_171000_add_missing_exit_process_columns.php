<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_exit_processes')) {
            return;
        }

        Schema::table('employee_exit_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_exit_processes', 'notice_waived')) {
                $table->boolean('notice_waived')->default(false)->nullable();
            }
            if (! Schema::hasColumn('employee_exit_processes', 'immediate_exit')) {
                $table->boolean('immediate_exit')->default(false)->nullable();
            }
            if (! Schema::hasColumn('employee_exit_processes', 'buyout_recovery')) {
                $table->boolean('buyout_recovery')->default(false)->nullable();
            }
            if (! Schema::hasColumn('employee_exit_processes', 'fnf_due_date')) {
                $table->date('fnf_due_date')->nullable();
            }
        });
    }

    public function down(): void
    {
    }
};

