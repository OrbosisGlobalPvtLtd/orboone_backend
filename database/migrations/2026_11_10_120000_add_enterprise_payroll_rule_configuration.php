<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enterprise_payroll_policies', function (Blueprint $table) {
            if (! Schema::hasColumn('enterprise_payroll_policies', 'proration_method')) {
                $table->string('proration_method', 40)->default('calendar_26');
            }
            if (! Schema::hasColumn('enterprise_payroll_policies', 'salary_day_base')) {
                $table->unsignedSmallInteger('salary_day_base')->default(26);
            }
        });

        if (! Schema::hasTable('enterprise_salary_component_rules')) {
            Schema::create('enterprise_salary_component_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('policy_id')->nullable()->index();
                $table->string('component_code', 50);
                $table->decimal('percentage', 7, 4)->default(0);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['policy_id', 'component_code', 'effective_from'], 'escr_policy_component_effective_unique');
            });
        }

        if (! Schema::hasTable('enterprise_employee_statutory_settings')) {
            Schema::create('enterprise_employee_statutory_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->boolean('professional_tax_applicable')->nullable();
                $table->boolean('pf_applicable')->nullable();
                $table->boolean('esi_applicable')->nullable();
                $table->boolean('tds_applicable')->nullable();
                $table->string('jurisdiction_code', 20)->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();
                $table->index(['employee_id', 'effective_from', 'effective_to'], 'eess_employee_effective_idx');
            });
        }

        if (! Schema::hasTable('enterprise_professional_tax_slabs')) {
            Schema::create('enterprise_professional_tax_slabs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('policy_id')->nullable()->index();
                $table->string('jurisdiction_code', 20);
                $table->decimal('monthly_gross_min', 14, 2)->default(0);
                $table->decimal('monthly_gross_max', 14, 2)->nullable();
                $table->decimal('monthly_tax_amount', 12, 2)->default(0);
                $table->decimal('balancing_month_amount', 12, 2)->nullable();
                $table->unsignedTinyInteger('balancing_month')->nullable();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['jurisdiction_code', 'effective_from', 'effective_to'], 'epts_jurisdiction_effective_idx');
            });
        }

        // Database defaults for future onboarding salary structures. Existing
        // salary rows are intentionally left untouched.
        foreach ([['basic', 50, 1], ['hra', 20, 2], ['special_allowance', 30, 3]] as [$code, $percentage, $order]) {
            DB::table('enterprise_salary_component_rules')->updateOrInsert(
                ['policy_id' => null, 'component_code' => $code, 'effective_from' => null],
                ['percentage' => $percentage, 'sort_order' => $order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        if (Schema::hasTable('enterprise_payrolls')) {
            Schema::table('enterprise_payrolls', function (Blueprint $table) {
                if (! Schema::hasColumn('enterprise_payrolls', 'salary_structure_id')) {
                    $table->unsignedBigInteger('salary_structure_id')->nullable()->index();
                }
                if (! Schema::hasColumn('enterprise_payrolls', 'policy_id')) {
                    $table->unsignedBigInteger('policy_id')->nullable()->index();
                }
                if (! Schema::hasColumn('enterprise_payrolls', 'salary_days')) {
                    $table->decimal('salary_days', 8, 4)->default(0);
                }
                if (! Schema::hasColumn('enterprise_payrolls', 'earned_base_gross')) {
                    $table->decimal('earned_base_gross', 14, 2)->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_professional_tax_slabs');
        Schema::dropIfExists('enterprise_employee_statutory_settings');
        Schema::dropIfExists('enterprise_salary_component_rules');

        if (Schema::hasTable('enterprise_payrolls')) {
            Schema::table('enterprise_payrolls', function (Blueprint $table) {
                foreach (['salary_structure_id', 'policy_id', 'salary_days', 'earned_base_gross'] as $column) {
                    if (Schema::hasColumn('enterprise_payrolls', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('enterprise_payroll_policies')) {
            Schema::table('enterprise_payroll_policies', function (Blueprint $table) {
                foreach (['proration_method', 'salary_day_base'] as $column) {
                    if (Schema::hasColumn('enterprise_payroll_policies', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
