<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hrms_exit_policies')) {
            Schema::create('hrms_exit_policies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Default Exit Policy');
                $table->string('applies_to', 50)->default('all')->index();
                $table->string('exit_type', 50)->nullable()->index();
                $table->unsignedInteger('notice_period_days')->default(15);
                $table->boolean('allow_waiver')->default(true);
                $table->boolean('allow_buyout')->default(true);
                $table->boolean('allow_immediate_exit')->default(true);
                $table->unsignedInteger('fnf_processing_days')->default(15);
                $table->boolean('is_active')->default(true)->index();
                $table->date('effective_from')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();
            });
        }

        $exists = DB::table('hrms_exit_policies')
            ->where('applies_to', 'all')
            ->whereNull('exit_type')
            ->where('is_active', 1)
            ->exists();

        if (! $exists) {
            DB::table('hrms_exit_policies')->insert([
                'name' => 'Default Notice Policy',
                'applies_to' => 'all',
                'exit_type' => null,
                'notice_period_days' => 15,
                'allow_waiver' => 1,
                'allow_buyout' => 1,
                'allow_immediate_exit' => 1,
                'fnf_processing_days' => 15,
                'is_active' => 1,
                'effective_from' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
    }
};

