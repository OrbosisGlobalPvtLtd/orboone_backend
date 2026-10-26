<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'monthly_quota',
        'monthly_used_this_month',
        'monthly_carry_forward',
        'total_monthly_remaining_paid',
        'last_month_processed',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('leave_allocations')) {
            return;
        }

        $columns = array_values(array_filter(
            self::COLUMNS,
            static fn (string $column): bool => Schema::hasColumn('leave_allocations', $column)
        ));

        if ($columns !== []) {
            Schema::table('leave_allocations', static function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('leave_allocations')) {
            return;
        }

        Schema::table('leave_allocations', static function (Blueprint $table): void {
            if (! Schema::hasColumn('leave_allocations', 'monthly_quota')) {
                $table->decimal('monthly_quota', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('leave_allocations', 'monthly_used_this_month')) {
                $table->decimal('monthly_used_this_month', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('leave_allocations', 'monthly_carry_forward')) {
                $table->decimal('monthly_carry_forward', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('leave_allocations', 'total_monthly_remaining_paid')) {
                $table->decimal('total_monthly_remaining_paid', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('leave_allocations', 'last_month_processed')) {
                $table->string('last_month_processed', 7)->nullable();
            }
        });
    }
};
