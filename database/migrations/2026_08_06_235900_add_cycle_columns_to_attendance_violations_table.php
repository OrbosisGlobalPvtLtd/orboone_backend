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
        if (Schema::hasTable('attendance_violations')) {
            Schema::table('attendance_violations', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_violations', 'violation_count')) {
                    $table->unsignedTinyInteger('violation_count')->default(1)->after('type');
                }
                if (! Schema::hasColumn('attendance_violations', 'cycle_month')) {
                    $table->string('cycle_month', 7)->nullable()->index()->after('violation_date');
                }
                if (! Schema::hasColumn('attendance_violations', 'status')) {
                    $table->string('status', 20)->default('pending')->index()->after('cycle_month');
                }
                if (! Schema::hasColumn('attendance_violations', 'resolved_at')) {
                    $table->timestamp('resolved_at')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_violations')) {
            Schema::table('attendance_violations', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_violations', 'resolved_at')) {
                    $table->dropColumn('resolved_at');
                }
                if (Schema::hasColumn('attendance_violations', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('attendance_violations', 'cycle_month')) {
                    $table->dropColumn('cycle_month');
                }
                if (Schema::hasColumn('attendance_violations', 'violation_count')) {
                    $table->dropColumn('violation_count');
                }
            });
        }
    }
};
