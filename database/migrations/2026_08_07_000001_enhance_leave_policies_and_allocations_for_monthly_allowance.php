<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_policies')) {
            Schema::table('leave_policies', function (Blueprint $table) {
                if (! Schema::hasColumn('leave_policies', 'monthly_leave_limit')) {
                    $table->decimal('monthly_leave_limit', 6, 2)->default(2.0);
                }
                if (! Schema::hasColumn('leave_policies', 'allow_monthly_carry_forward')) {
                    $table->boolean('allow_monthly_carry_forward')->default(true)->after('monthly_leave_limit');
                }
                if (! Schema::hasColumn('leave_policies', 'carry_forward_reset_month')) {
                    $table->unsignedTinyInteger('carry_forward_reset_month')->default(12)->after('carry_forward_enabled');
                }
                if (! Schema::hasColumn('leave_policies', 'carry_forward_reset_day')) {
                    $table->unsignedTinyInteger('carry_forward_reset_day')->default(31)->after('carry_forward_reset_month');
                }
                if (! Schema::hasColumn('leave_policies', 'nov_dec_half_usage_enabled')) {
                    $table->boolean('nov_dec_half_usage_enabled')->default(true)->after('carry_forward_reset_day');
                }
                if (! Schema::hasColumn('leave_policies', 'nov_dec_threshold_balance')) {
                    $table->decimal('nov_dec_threshold_balance', 6, 2)->default(10.0)->after('nov_dec_half_usage_enabled');
                }
                if (! Schema::hasColumn('leave_policies', 'nov_dec_usage_percentage')) {
                    $table->decimal('nov_dec_usage_percentage', 5, 2)->default(50.0)->after('nov_dec_threshold_balance');
                }
            });
        }

        if (Schema::hasTable('leave_allocations')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                if (! Schema::hasColumn('leave_allocations', 'monthly_used_this_month')) {
                    $table->decimal('monthly_used_this_month', 8, 2)->default(0.0)->after('comp_off_remaining');
                }
                if (! Schema::hasColumn('leave_allocations', 'monthly_carry_forward')) {
                    $table->decimal('monthly_carry_forward', 8, 2)->default(0.0)->after('monthly_used_this_month');
                }
                if (! Schema::hasColumn('leave_allocations', 'last_month_processed')) {
                    $table->string('last_month_processed', 7)->nullable()->after('monthly_carry_forward');
                }

                // Drop duplicate / legacy columns if present
                $legacyColumns = [
                    'monthly_paid_available',
                    'monthly_paid_used',
                    'monthly_paid_allowance',
                    'allowance_year',
                    'allowance_month',
                    'last_monthly_credit_at',
                    'last_monthly_credit_month'
                ];
                foreach ($legacyColumns as $col) {
                    if (Schema::hasColumn('leave_allocations', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_policies')) {
            Schema::table('leave_policies', function (Blueprint $table) {
                $columns = [
                    'allow_monthly_carry_forward',
                    'carry_forward_reset_month',
                    'carry_forward_reset_day',
                    'nov_dec_half_usage_enabled',
                    'nov_dec_threshold_balance',
                    'nov_dec_usage_percentage',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('leave_policies', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('leave_allocations')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $columns = ['monthly_used_this_month', 'monthly_carry_forward', 'last_month_processed'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('leave_allocations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
