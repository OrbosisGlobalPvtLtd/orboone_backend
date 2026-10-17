<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $policyColumns = [
            'lapse_grace_days' => fn (Blueprint $table) => $table->unsignedTinyInteger('lapse_grace_days')->default(10),
            'request_unit' => fn (Blueprint $table) => $table->decimal('request_unit', 4, 2)->default('0.50'),
            'unpaid_intern_leave_limit' => fn (Blueprint $table) => $table->decimal('unpaid_intern_leave_limit', 4, 2)->default('1.00'),
        ];

        foreach ($policyColumns as $column => $definition) {
            if (! Schema::hasColumn('leave_policies', $column)) {
                Schema::table('leave_policies', static function (Blueprint $table) use ($definition): void {
                    $definition($table);
                });
            }
        }

        if (Schema::hasTable('leave_balance_transactions')
            && ! Schema::hasColumn('leave_balance_transactions', 'source')) {
            Schema::table('leave_balance_transactions', static function (Blueprint $table): void {
                $table->string('source', 16)->default('system')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_balance_transactions')
            && Schema::hasColumn('leave_balance_transactions', 'source')) {
            Schema::table('leave_balance_transactions', static function (Blueprint $table): void {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            });
        }

        foreach (['unpaid_intern_leave_limit', 'request_unit', 'lapse_grace_days'] as $column) {
            if (Schema::hasTable('leave_policies') && Schema::hasColumn('leave_policies', $column)) {
                Schema::table('leave_policies', static function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
