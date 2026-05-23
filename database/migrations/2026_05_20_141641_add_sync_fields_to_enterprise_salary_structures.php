<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncFieldsToEnterpriseSalaryStructures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('enterprise_salary_structures', function (Blueprint $table) {
            $table->string('source', 80)->nullable()->after('status');
            $table->string('stage', 80)->nullable()->after('source');
            $table->string('sync_reference_type', 120)->nullable()->after('stage');
            $table->unsignedBigInteger('sync_reference_id')->nullable()->after('sync_reference_type');
            $table->text('revision_reason')->nullable()->after('sync_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enterprise_salary_structures', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'stage',
                'sync_reference_type',
                'sync_reference_id',
                'revision_reason'
            ]);
        });
    }
}
