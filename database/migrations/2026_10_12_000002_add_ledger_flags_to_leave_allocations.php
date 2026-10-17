<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_allocations', function (Blueprint $table): void {
            $table->boolean('uses_ledger')->default(false);
            $table->date('ledger_effective_from')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leave_allocations', function (Blueprint $table): void {
            $table->dropColumn(['uses_ledger', 'ledger_effective_from']);
        });
    }
};
