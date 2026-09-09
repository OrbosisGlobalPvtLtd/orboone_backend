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
        if (Schema::hasTable('employee_profiles')) {
            Schema::table('employee_profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_profiles', 'experience_type')) {
                    $table->string('experience_type', 50)->nullable()->after('total_experience');
                }
                if (! Schema::hasColumn('employee_profiles', 'approved_by_user_id')) {
                    $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('profile_completed_at');
                }
                if (! Schema::hasColumn('employee_profiles', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_profiles')) {
            Schema::table('employee_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('employee_profiles', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('employee_profiles', 'approved_by_user_id')) {
                    $table->dropColumn('approved_by_user_id');
                }
                if (Schema::hasColumn('employee_profiles', 'experience_type')) {
                    $table->dropColumn('experience_type');
                }
            });
        }
    }
};
