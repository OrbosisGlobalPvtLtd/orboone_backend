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
            if (! Schema::hasColumn('enterprise_payroll_policies', 'pf_wage_ceiling')) {
                $table->decimal('pf_wage_ceiling', 14, 2)->nullable();
            }
            if (! Schema::hasColumn('enterprise_payroll_policies', 'esi_wage_ceiling')) {
                $table->decimal('esi_wage_ceiling', 14, 2)->nullable();
            }
            if (! Schema::hasColumn('enterprise_payroll_policies', 'pf_wage_basis')) {
                $table->string('pf_wage_basis', 20)->default('basic');
            }
            if (! Schema::hasColumn('enterprise_payroll_policies', 'esi_wage_basis')) {
                $table->string('esi_wage_basis', 20)->default('gross');
            }
        });

        if (Schema::hasTable('enterprise_salary_structures') && ! Schema::hasColumn('enterprise_salary_structures', 'component_rules_snapshot')) {
            Schema::table('enterprise_salary_structures', function (Blueprint $table) {
                $table->json('component_rules_snapshot')->nullable();
            });
        }

        if (Schema::hasTable('enterprise_employee_statutory_settings')) {
            Schema::table('enterprise_employee_statutory_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('enterprise_employee_statutory_settings', 'pf_wage_ceiling_override')) {
                    $table->decimal('pf_wage_ceiling_override', 14, 2)->nullable();
                }
                if (! Schema::hasColumn('enterprise_employee_statutory_settings', 'esi_wage_ceiling_override')) {
                    $table->decimal('esi_wage_ceiling_override', 14, 2)->nullable();
                }
            });
        }

        if (! Schema::hasTable('enterprise_employee_tax_profiles')) {
            Schema::create('enterprise_employee_tax_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedSmallInteger('financial_year_start');
                $table->string('tax_regime', 40);
                $table->string('residency_status', 30)->nullable();
                $table->string('age_category', 30)->default('all');
                $table->decimal('previous_employer_income_annual', 14, 2)->default(0);
                $table->decimal('previous_employer_tds_annual', 14, 2)->default(0);
                $table->decimal('other_taxable_income_annual', 14, 2)->default(0);
                $table->decimal('deductions_annual', 14, 2)->default(0);
                $table->decimal('exemptions_annual', 14, 2)->default(0);
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by_user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->unique(['employee_id', 'financial_year_start'], 'eetp_employee_tax_year_unique');
            });
        }

        if (! Schema::hasTable('enterprise_income_tax_rules')) {
            Schema::create('enterprise_income_tax_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('financial_year_start');
                $table->string('tax_regime', 40);
                $table->decimal('standard_deduction', 14, 2)->default(0);
                $table->decimal('rebate_income_limit', 14, 2)->default(0);
                $table->decimal('rebate_max_amount', 14, 2)->default(0);
                $table->decimal('cess_percentage', 7, 4)->default(0);
                $table->decimal('surcharge_applicable_from', 14, 2)->nullable();
                $table->boolean('is_complete')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['financial_year_start', 'tax_regime'], 'eit_rules_year_regime_unique');
            });
        }

        if (! Schema::hasTable('enterprise_income_tax_slabs')) {
            Schema::create('enterprise_income_tax_slabs', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('financial_year_start');
                $table->string('tax_regime', 40);
                $table->string('age_category', 30)->default('all');
                $table->decimal('income_from', 14, 2)->default(0);
                $table->decimal('income_to', 14, 2)->nullable();
                $table->decimal('tax_rate', 7, 4)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['financial_year_start', 'tax_regime', 'age_category'], 'eits_year_regime_age_idx');
            });
        }

        if (! Schema::hasTable('enterprise_income_tax_surcharges')) {
            Schema::create('enterprise_income_tax_surcharges', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('financial_year_start');
                $table->string('tax_regime', 40);
                $table->decimal('income_threshold', 14, 2);
                $table->decimal('surcharge_rate', 7, 4)->default(0);
                $table->boolean('marginal_relief')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['financial_year_start', 'tax_regime', 'income_threshold'], 'eitsc_year_regime_threshold_idx');
            });
        }

        // A company-wide PT amount is not an employee eligibility rule.
        if (Schema::hasTable('enterprise_payroll_policies')) {
            DB::table('enterprise_payroll_policies')->update([
                'professional_tax_enabled' => false,
                'professional_tax_amount' => 0,
                'updated_at' => now(),
            ]);
        }
        $this->migrateLinkedLegacyStructures();
        $this->linkHistoricalPayrollStructures();
        $this->removeLegacySalaryStructureReferences();
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'salary_structure_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('salary_structure_id')->nullable();
            });
        }

        if (! Schema::hasTable('salary_structures')) {
            Schema::create('salary_structures', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->json('components')->nullable();
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->decimal('basic_salary', 10, 2)->default(0);
                $table->decimal('hra_percent', 5, 2)->default(0);
                $table->decimal('allowance', 10, 2)->default(0);
                $table->decimal('pt_amount', 10, 2)->default(0);
                $table->date('effective_date')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('employees') && Schema::hasTable('salary_structures')
            && Schema::hasColumn('employees', 'salary_structure_id')) {
            try {
                Schema::table('employees', function (Blueprint $table) {
                    $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->nullOnDelete();
                });
            } catch (Throwable $e) {
                // Rollback can run on installs with a pre-existing equivalent constraint.
            }
        }
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('payrolls', 'legacy_salary_structure_id') && ! Schema::hasColumn('payrolls', 'salary_structure_id')) {
                    $table->renameColumn('legacy_salary_structure_id', 'salary_structure_id');
                }
                if (Schema::hasColumn('payrolls', 'enterprise_salary_structure_id')) {
                    $table->dropColumn('enterprise_salary_structure_id');
                }
            });
        }

        Schema::dropIfExists('enterprise_income_tax_surcharges');
        Schema::dropIfExists('enterprise_income_tax_slabs');
        Schema::dropIfExists('enterprise_income_tax_rules');
        Schema::dropIfExists('enterprise_employee_tax_profiles');

        if (Schema::hasTable('enterprise_employee_statutory_settings')) {
            Schema::table('enterprise_employee_statutory_settings', function (Blueprint $table) {
                foreach (['pf_wage_ceiling_override', 'esi_wage_ceiling_override'] as $column) {
                    if (Schema::hasColumn('enterprise_employee_statutory_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        if (Schema::hasTable('enterprise_payroll_policies')) {
            Schema::table('enterprise_payroll_policies', function (Blueprint $table) {
                foreach (['pf_wage_ceiling', 'esi_wage_ceiling', 'pf_wage_basis', 'esi_wage_basis'] as $column) {
                    if (Schema::hasColumn('enterprise_payroll_policies', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        if (Schema::hasTable('enterprise_salary_structures') && Schema::hasColumn('enterprise_salary_structures', 'component_rules_snapshot')) {
            Schema::table('enterprise_salary_structures', function (Blueprint $table) {
                $table->dropColumn('component_rules_snapshot');
            });
        }
    }

    private function migrateLinkedLegacyStructures(): void
    {
        if (! Schema::hasTable('salary_structures') || ! Schema::hasTable('employees') || ! Schema::hasTable('employees_new')) {
            return;
        }
        if (! Schema::hasColumn('employees', 'salary_structure_id') || ! Schema::hasColumn('employees', 'user_id')) {
            return;
        }

        $legacyRows = DB::table('employees as legacy_employee')
            ->join('salary_structures as legacy_salary', 'legacy_salary.id', '=', 'legacy_employee.salary_structure_id')
            ->join('employees_new as employee', 'employee.user_id', '=', 'legacy_employee.user_id')
            ->select([
                'employee.id as employee_id', 'employee.joining_date',
                'legacy_salary.id as legacy_id', 'legacy_salary.basic_salary', 'legacy_salary.hra_percent',
                'legacy_salary.allowance', 'legacy_salary.pt_amount', 'legacy_salary.effective_date',
            ])->get();

        foreach ($legacyRows as $row) {
            $exists = DB::table('enterprise_salary_structures')->where('employee_id', $row->employee_id)
                ->where('status', 'active')->exists();
            if ($exists) {
                continue;
            }

            $basic = round((float) $row->basic_salary, 2);
            $hra = round($basic * (float) $row->hra_percent / 100, 2);
            $special = round((float) $row->allowance, 2);
            $monthlyGross = round($basic + $hra + $special, 2);
            $effectiveFrom = $row->effective_date ?: ($row->joining_date ?: now()->toDateString());
            $structureId = DB::table('enterprise_salary_structures')->insertGetId([
                'employee_id' => $row->employee_id,
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'annual_ctc' => round($monthlyGross * 12, 2),
                'monthly_ctc' => $monthlyGross,
                'basic_annual' => round($basic * 12, 2),
                'basic_monthly' => $basic,
                'hra_annual' => round($hra * 12, 2),
                'hra_monthly' => $hra,
                'special_allowance_annual' => round($special * 12, 2),
                'special_allowance_monthly' => $special,
                'component_rules_snapshot' => json_encode(['source' => 'legacy_import', 'legacy_salary_structure_id' => $row->legacy_id]),
                'professional_tax_monthly' => 0,
                'tds_annual' => 0,
                'tds_monthly' => 0,
                'other_deduction_monthly' => 0,
                'status' => 'active',
                'source' => 'legacy_salary_migration',
                'revision_reason' => "Imported from legacy salary structure #{$row->legacy_id}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('enterprise_salary_structure_histories')) {
                DB::table('enterprise_salary_structure_histories')->insert([
                    'salary_structure_id' => $structureId,
                    'employee_id' => $row->employee_id,
                    'old_values' => json_encode(['legacy_salary_structure_id' => $row->legacy_id]),
                    'new_values' => json_encode(['monthly_ctc' => $monthlyGross, 'legacy_import' => true]),
                    'revision_reason' => 'Legacy salary structure migrated to Enterprise payroll.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function removeLegacySalaryStructureReferences(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'salary_structure_id')) {
            try {
                Schema::table('employees', fn (Blueprint $table) => $table->dropForeign(['salary_structure_id']));
            } catch (Throwable $e) {
                // Some installations created the column without its foreign key.
            }
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'salary_structure_id')) {
                    $table->dropColumn('salary_structure_id');
                }
            });
        }

        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (! Schema::hasColumn('payrolls', 'enterprise_salary_structure_id')) {
                    $table->unsignedBigInteger('enterprise_salary_structure_id')->nullable()->index();
                }
                if (Schema::hasColumn('payrolls', 'salary_structure_id') && ! Schema::hasColumn('payrolls', 'legacy_salary_structure_id')) {
                    $table->renameColumn('salary_structure_id', 'legacy_salary_structure_id');
                }
            });
        }

        Schema::dropIfExists('salary_structures');
    }

    private function linkHistoricalPayrollStructures(): void
    {
        if (! Schema::hasTable('payrolls') || ! Schema::hasTable('employees') || ! Schema::hasTable('employees_new')) {
            return;
        }
        if (! Schema::hasColumn('payrolls', 'salary_structure_id') || ! Schema::hasColumn('employees', 'user_id')) {
            return;
        }

        Schema::table('payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('payrolls', 'enterprise_salary_structure_id')) {
                $table->unsignedBigInteger('enterprise_salary_structure_id')->nullable()->index();
            }
        });

        foreach (DB::table('payrolls')->whereNull('enterprise_salary_structure_id')->get() as $payroll) {
            $legacyEmployee = DB::table('employees')->where('id', $payroll->employee_id)->first();
            if (! $legacyEmployee) {
                continue;
            }
            $employee = DB::table('employees_new')->where('user_id', $legacyEmployee->user_id)->first();
            if (! $employee) {
                continue;
            }

            $periodStart = sprintf('%04d-%02d-01', $payroll->year, $payroll->month);
            $structure = DB::table('enterprise_salary_structures')->where('employee_id', $employee->id)
                ->whereDate('effective_from', '<=', $periodStart)
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodStart))
                ->orderByDesc('effective_from')->orderByDesc('id')->first();
            if ($structure) {
                DB::table('payrolls')->where('id', $payroll->id)->update([
                    'enterprise_salary_structure_id' => $structure->id,
                ]);
            }
        }
    }
};
