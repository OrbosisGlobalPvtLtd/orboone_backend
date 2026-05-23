<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createEnterpriseTables();
        $this->syncPermissions();
        $this->syncMenus();
        $this->syncRoleAccess();
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_fnf_settlements');
        Schema::dropIfExists('enterprise_payroll_audits');
        Schema::dropIfExists('enterprise_reimbursements');
        Schema::dropIfExists('enterprise_bonus_incentives');
        Schema::dropIfExists('enterprise_payslips');
        Schema::dropIfExists('enterprise_payroll_adjustments');
        Schema::dropIfExists('enterprise_payroll_items');
        Schema::dropIfExists('enterprise_payrolls');
        Schema::dropIfExists('enterprise_payroll_runs');
        Schema::dropIfExists('enterprise_salary_structure_histories');
        Schema::dropIfExists('enterprise_salary_structures');
    }

    private function createEnterpriseTables(): void
    {
        if (! Schema::hasTable('enterprise_salary_structures')) {
            Schema::create('enterprise_salary_structures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->date('effective_from')->index();
                $table->date('effective_to')->nullable()->index();
                $table->decimal('annual_ctc', 14, 2)->default(0);
                $table->decimal('monthly_ctc', 14, 2)->default(0);
                $table->decimal('basic_annual', 14, 2)->default(0);
                $table->decimal('basic_monthly', 14, 2)->default(0);
                $table->decimal('hra_annual', 14, 2)->default(0);
                $table->decimal('hra_monthly', 14, 2)->default(0);
                $table->decimal('special_allowance_annual', 14, 2)->default(0);
                $table->decimal('special_allowance_monthly', 14, 2)->default(0);
                $table->decimal('professional_tax_monthly', 12, 2)->default(0);
                $table->decimal('tds_annual', 14, 2)->default(0);
                $table->decimal('tds_monthly', 12, 2)->default(0);
                $table->decimal('other_deduction_monthly', 12, 2)->default(0);
                $table->string('status', 40)->default('active')->index();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status'], 'eps_employee_status_idx');
                $table->index(['employee_id', 'effective_from', 'effective_to'], 'eps_employee_effective_idx');
            });
        }

        if (! Schema::hasTable('enterprise_salary_structure_histories')) {
            Schema::create('enterprise_salary_structure_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('salary_structure_id')->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('revision_reason')->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('enterprise_payroll_runs')) {
            Schema::create('enterprise_payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');
                $table->string('status', 40)->default('draft')->index();
                $table->unsignedInteger('total_employees')->default(0);
                $table->decimal('total_gross', 16, 2)->default(0);
                $table->decimal('total_deductions', 16, 2)->default(0);
                $table->decimal('total_net', 16, 2)->default(0);
                $table->unsignedBigInteger('processed_by_user_id')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('locked_by_user_id')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->unsignedBigInteger('reopened_by_user_id')->nullable();
                $table->timestamp('reopened_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['month', 'year'], 'enterprise_payroll_runs_period_unique');
                $table->index(['month', 'year', 'status'], 'epr_period_status_idx');
            });
        }

        if (! Schema::hasTable('enterprise_payrolls')) {
            Schema::create('enterprise_payrolls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_run_id')->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');
                $table->decimal('total_working_days', 8, 2)->default(0);
                $table->decimal('present_days', 8, 2)->default(0);
                $table->decimal('paid_leave_days', 8, 2)->default(0);
                $table->decimal('sick_leave_days', 8, 2)->default(0);
                $table->decimal('comp_off_days', 8, 2)->default(0);
                $table->decimal('holiday_days', 8, 2)->default(0);
                $table->decimal('week_off_days', 8, 2)->default(0);
                $table->decimal('half_days', 8, 2)->default(0);
                $table->decimal('lwp_days', 8, 2)->default(0);
                $table->decimal('absent_days', 8, 2)->default(0);
                $table->unsignedInteger('late_count')->default(0);
                $table->unsignedInteger('early_out_count')->default(0);
                $table->unsignedInteger('missed_punch_count')->default(0);
                $table->decimal('payable_days', 8, 2)->default(0);
                $table->decimal('annual_ctc', 14, 2)->default(0);
                $table->decimal('monthly_ctc', 14, 2)->default(0);
                $table->decimal('per_day_salary', 14, 4)->default(0);
                $table->decimal('basic_salary', 14, 2)->default(0);
                $table->decimal('hra', 14, 2)->default(0);
                $table->decimal('special_allowance', 14, 2)->default(0);
                $table->decimal('gross_salary', 14, 2)->default(0);
                $table->decimal('professional_tax', 12, 2)->default(0);
                $table->decimal('tds', 12, 2)->default(0);
                $table->decimal('attendance_deduction', 14, 2)->default(0);
                $table->decimal('lwp_deduction', 14, 2)->default(0);
                $table->decimal('half_day_deduction', 14, 2)->default(0);
                $table->decimal('absent_deduction', 14, 2)->default(0);
                $table->decimal('other_deduction', 14, 2)->default(0);
                $table->decimal('total_deductions', 14, 2)->default(0);
                $table->decimal('bonus_amount', 14, 2)->default(0);
                $table->decimal('incentive_amount', 14, 2)->default(0);
                $table->decimal('reimbursement_amount', 14, 2)->default(0);
                $table->decimal('net_salary', 14, 2)->default(0);
                $table->text('net_salary_words')->nullable();
                $table->string('status', 40)->default('draft')->index();
                $table->timestamp('generated_at')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->text('remarks')->nullable();
                $table->json('calculation_snapshot')->nullable();
                $table->timestamps();

                $table->unique(['employee_id', 'month', 'year'], 'enterprise_payroll_employee_period_unique');
                $table->index(['month', 'year', 'status'], 'ep_period_status_idx');
            });
        }

        if (! Schema::hasTable('enterprise_payroll_items')) {
            Schema::create('enterprise_payroll_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_id')->index();
                $table->string('item_type', 40)->index();
                $table->string('item_code', 80)->index();
                $table->string('item_name');
                $table->decimal('amount', 14, 2)->default(0);
                $table->boolean('is_taxable')->default(false);
                $table->string('source_type', 120)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('enterprise_payroll_adjustments')) {
            Schema::create('enterprise_payroll_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->string('adjustment_type', 40)->index();
                $table->string('title');
                $table->decimal('amount', 14, 2)->default(0);
                $table->text('reason')->nullable();
                $table->string('status', 40)->default('pending')->index();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('enterprise_payslips')) {
            Schema::create('enterprise_payslips', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_id')->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');
                $table->string('payslip_no')->unique();
                $table->string('pdf_path');
                $table->string('pdf_url')->nullable();
                $table->unsignedBigInteger('generated_by_user_id')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->boolean('is_visible_to_employee')->default(true)->index();
                $table->timestamps();

                $table->unique(['employee_id', 'month', 'year'], 'enterprise_payslip_employee_period_unique');
            });
        }

        if (! Schema::hasTable('enterprise_bonus_incentives')) {
            Schema::create('enterprise_bonus_incentives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->string('type', 40)->index();
                $table->string('title');
                $table->string('target_range')->nullable();
                $table->decimal('target_amount', 14, 2)->default(0);
                $table->decimal('achievement_amount', 14, 2)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');
                $table->string('status', 40)->default('pending')->index();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('paid_in_payroll_id')->nullable()->index();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'month', 'year', 'status'], 'ebi_employee_period_status_idx');
            });
        }

        if (! Schema::hasTable('enterprise_reimbursements')) {
            Schema::create('enterprise_reimbursements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->string('title');
                $table->date('claim_date')->index();
                $table->decimal('amount', 14, 2)->default(0);
                $table->decimal('approved_amount', 14, 2)->default(0);
                $table->string('attachment_path')->nullable();
                $table->string('status', 40)->default('pending')->index();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('paid_in_payroll_id')->nullable()->index();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status'], 'er_employee_status_idx');
            });
        }

        if (! Schema::hasTable('enterprise_payroll_audits')) {
            Schema::create('enterprise_payroll_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_run_id')->nullable()->index();
                $table->unsignedBigInteger('payroll_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->nullable()->index();
                $table->string('action', 80)->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->unsignedBigInteger('performed_by_user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('enterprise_fnf_settlements')) {
            Schema::create('enterprise_fnf_settlements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->unsignedBigInteger('exit_process_id')->nullable()->index();
                $table->unsignedTinyInteger('settlement_month');
                $table->unsignedInteger('settlement_year');
                $table->decimal('pending_salary', 14, 2)->default(0);
                $table->decimal('leave_encashment', 14, 2)->default(0);
                $table->decimal('reimbursement_amount', 14, 2)->default(0);
                $table->decimal('deductions', 14, 2)->default(0);
                $table->decimal('final_payable', 14, 2)->default(0);
                $table->string('status', 40)->default('draft')->index();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    private function syncPermissions(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = Carbon::now();
        $permissions = [
            ['hrms', 'enterprise_payroll_dashboard', 'view', 'enterprise_payroll.dashboard.view', 'View enterprise payroll dashboard'],
            ['hrms', 'enterprise_salary_structure', 'view', 'enterprise_salary_structure.view', 'View enterprise salary structures'],
            ['hrms', 'enterprise_salary_structure', 'manage', 'enterprise_salary_structure.manage', 'Manage enterprise salary structures'],
            ['hrms', 'enterprise_payroll_run', 'view', 'enterprise_payroll_run.view', 'View enterprise payroll runs'],
            ['hrms', 'enterprise_payroll_run', 'generate', 'enterprise_payroll_run.generate', 'Generate enterprise payroll'],
            ['hrms', 'enterprise_payroll_run', 'approve', 'enterprise_payroll_run.approve', 'Approve enterprise payroll'],
            ['hrms', 'enterprise_payroll_run', 'lock', 'enterprise_payroll_run.lock', 'Lock enterprise payroll'],
            ['hrms', 'enterprise_payroll_run', 'reopen', 'enterprise_payroll_run.reopen', 'Reopen locked enterprise payroll'],
            ['hrms', 'enterprise_payslip', 'view', 'enterprise_payslip.view', 'View enterprise payslips'],
            ['hrms', 'enterprise_payslip', 'generate', 'enterprise_payslip.generate', 'Generate enterprise payslips'],
            ['hrms', 'enterprise_payslip', 'download', 'enterprise_payslip.download', 'Download enterprise payslips'],
            ['hrms', 'enterprise_bonus_incentive', 'view', 'enterprise_bonus_incentive.view', 'View enterprise bonus and incentives'],
            ['hrms', 'enterprise_bonus_incentive', 'manage', 'enterprise_bonus_incentive.manage', 'Manage enterprise bonus and incentives'],
            ['hrms', 'enterprise_reimbursement', 'view', 'enterprise_reimbursement.view', 'View enterprise reimbursements'],
            ['hrms', 'enterprise_reimbursement', 'manage', 'enterprise_reimbursement.manage', 'Manage enterprise reimbursements'],
            ['hrms', 'enterprise_fnf', 'view', 'enterprise_fnf.view', 'View enterprise FNF settlements'],
            ['hrms', 'enterprise_fnf', 'manage', 'enterprise_fnf.manage', 'Manage enterprise FNF settlements'],
            ['hrms', 'enterprise_payroll_reports', 'view', 'enterprise_payroll_reports.view', 'View enterprise payroll reports'],
        ];

        foreach ($permissions as [$module, $submodule, $action, $key, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                compact('module', 'submodule', 'action', 'description') + [
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }

    private function syncMenus(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $now = Carbon::now();

        DB::table('menus')->whereIn('id', [40, 41, 42, 43, 44, 45, 141, 142, 147, 155])->update([
            'is_active' => 0,
            'updated_at' => $now,
        ]);

        $menus = [
            [300, 'Enterprise Payroll', null, 'fas fa-money-check-alt', 'enterprise_payroll', null, 40],
            [301, 'Dashboard', 'enterprise-payroll.dashboard', 'fas fa-chart-pie', 'enterprise_payroll', 300, 1],
            [302, 'Salary Structures', 'enterprise-payroll.salary-structures.index', 'fas fa-layer-group', 'enterprise_payroll', 300, 2],
            [303, 'Payroll Runs', 'enterprise-payroll.runs.index', 'fas fa-play-circle', 'enterprise_payroll', 300, 3],
            [304, 'Payslips', 'enterprise-payroll.payslips.index', 'fas fa-file-invoice-dollar', 'enterprise_payroll', 300, 4],
            [305, 'Bonus & Incentives', 'enterprise-payroll.bonus-incentives.index', 'fas fa-gift', 'enterprise_payroll', 300, 5],
            [306, 'Reimbursements', 'enterprise-payroll.reimbursements.index', 'fas fa-receipt', 'enterprise_payroll', 300, 6],
            [307, 'FNF Settlements', 'enterprise-payroll.fnf.index', 'fas fa-hand-holding-usd', 'enterprise_payroll', 300, 7],
            [308, 'Reports', 'enterprise-payroll.reports.index', 'fas fa-chart-bar', 'enterprise_payroll', 300, 8],
            [309, 'My Payslips', 'enterprise-payroll.self.payslips', 'fas fa-file-invoice-dollar', 'employee.salary', null, 42],
            [310, 'My Reimbursements', 'enterprise-payroll.self.reimbursements', 'fas fa-receipt', 'employee.salary', null, 43],
        ];

        foreach ($menus as [$id, $name, $route, $icon, $moduleKey, $parentId, $sortOrder]) {
            DB::table('menus')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $name,
                    'route' => $route,
                    'icon' => $icon,
                    'module_key' => $moduleKey,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }

    private function syncRoleAccess(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions') || ! Schema::hasTable('role_permissions')) {
            return;
        }

        $now = Carbon::now();
        $permissionIds = DB::table('permissions')
            ->where('key', 'like', 'enterprise_%')
            ->pluck('id', 'key')
            ->toArray();

        $roleIds = DB::table('roles')->pluck('id', 'slug')->toArray();
        $allKeys = array_keys($permissionIds);
        $operationalKeys = array_values(array_filter($allKeys, fn ($key) => $key !== 'enterprise_payroll_run.reopen'));
        $roleKeys = [
            'super_admin' => $allKeys,
            'finance_admin' => $operationalKeys,
            'admin' => $operationalKeys,
            'hr_admin' => [
                'enterprise_payroll.dashboard.view',
                'enterprise_salary_structure.view',
                'enterprise_payroll_run.view',
                'enterprise_payslip.view',
                'enterprise_payslip.download',
                'enterprise_bonus_incentive.view',
                'enterprise_reimbursement.view',
                'enterprise_reimbursement.manage',
                'enterprise_fnf.view',
                'enterprise_payroll_reports.view',
            ],
            'employee' => [
                'enterprise_payslip.view',
                'enterprise_payslip.download',
                'enterprise_reimbursement.view',
            ],
        ];

        foreach ($roleKeys as $slug => $keys) {
            $roleId = $roleIds[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($keys as $key) {
                $permissionId = $permissionIds[$key] ?? null;
                if (! $permissionId) {
                    continue;
                }

                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }

        if (Schema::hasTable('role_menu_access')) {
            $menuIds = [300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310];
            $adminMenuIds = [300, 301, 302, 303, 304, 305, 306, 307, 308];
            $employeeMenuIds = [309, 310];

            foreach (['super_admin', 'finance_admin', 'admin'] as $slug) {
                $roleId = $roleIds[$slug] ?? null;
                if (! $roleId) {
                    continue;
                }
                foreach ($adminMenuIds as $menuId) {
                    DB::table('role_menu_access')->updateOrInsert(
                        ['role_id' => $roleId, 'menu_id' => $menuId],
                        ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                    );
                }
            }

            $hrRoleId = $roleIds['hr_admin'] ?? null;
            if ($hrRoleId) {
                foreach ([300, 301, 302, 303, 304, 306, 307, 308] as $menuId) {
                    DB::table('role_menu_access')->updateOrInsert(
                        ['role_id' => $hrRoleId, 'menu_id' => $menuId],
                        ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                    );
                }
            }

            $employeeRoleId = $roleIds['employee'] ?? null;
            if ($employeeRoleId) {
                foreach ($employeeMenuIds as $menuId) {
                    DB::table('role_menu_access')->updateOrInsert(
                        ['role_id' => $employeeRoleId, 'menu_id' => $menuId],
                        ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                    );
                }
            }
        }
    }
};
