<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (! Schema::hasColumn('attendances', 'gross_duration')) {
                    $table->string('gross_duration', 50)->nullable()->after('gross_work_minutes');
                }
            });

            DB::statement("UPDATE attendances SET gross_duration = CONCAT(LPAD(FLOOR(COALESCE(gross_work_minutes, 0) / 60), 2, '0'), ':', LPAD(COALESCE(gross_work_minutes, 0) % 60, 2, '0')) WHERE gross_duration IS NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'gross_duration')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('gross_duration');
            });
        }
    }
};
