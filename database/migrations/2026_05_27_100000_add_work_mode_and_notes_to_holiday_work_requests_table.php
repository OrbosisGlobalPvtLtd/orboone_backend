<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holiday_work_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('holiday_work_requests', 'work_mode')) {
                $table->string('work_mode')->nullable()->after('work_type');
            }
            if (!Schema::hasColumn('holiday_work_requests', 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('holiday_work_requests', function (Blueprint $table) {
            if (Schema::hasColumn('holiday_work_requests', 'work_mode')) {
                $table->dropColumn('work_mode');
            }
            if (Schema::hasColumn('holiday_work_requests', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
