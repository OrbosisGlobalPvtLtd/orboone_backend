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
                if (! Schema::hasColumn('employees_new', 'internship_status')) {
                    $table->string('internship_status', 50)->nullable()->after('internship_extended_to');
                }
                if (! Schema::hasColumn('employees_new', 'is_permanent')) {
                    $table->boolean('is_permanent')->default(false)->after('is_paid_intern');
                }
                if (! Schema::hasColumn('employees_new', 'permanent_at')) {
                    $table->timestamp('permanent_at')->nullable()->after('is_permanent');
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
                if (Schema::hasColumn('employees_new', 'permanent_at')) {
                    $table->dropColumn('permanent_at');
                }
                if (Schema::hasColumn('employees_new', 'is_permanent')) {
                    $table->dropColumn('is_permanent');
                }
                if (Schema::hasColumn('employees_new', 'internship_status')) {
                    $table->dropColumn('internship_status');
                }
            });
        }
    }
};
