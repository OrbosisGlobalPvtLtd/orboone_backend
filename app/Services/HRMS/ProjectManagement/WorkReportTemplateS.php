<?php

namespace App\Services\HRMS\ProjectManagement;

use App\Models\HRMS\ProjectManagement\WorkReportTemplateM;
use App\Models\HRMS\ProjectManagement\WorkReportTemplateFieldM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WorkReportTemplateS
{
    /**
     * Get or create default role template for role type.
     */
    public function getTemplateForRole(string $roleType, ?int $projectId = null): ?WorkReportTemplateM
    {
        // 1. Check custom project template
        if ($projectId) {
            $projectTemplate = WorkReportTemplateM::where('project_id', $projectId)
                ->where('employee_role_type', $roleType)
                ->where('is_active', 1)
                ->with('activeFields')
                ->first();

            if ($projectTemplate) return $projectTemplate;
        }

        // 2. Check global template
        $globalTemplate = WorkReportTemplateM::whereNull('project_id')
            ->where('employee_role_type', $roleType)
            ->where('is_active', 1)
            ->with('activeFields')
            ->first();

        if ($globalTemplate) return $globalTemplate;

        // 3. Fallback general template
        return WorkReportTemplateM::where('code', 'general_report')
            ->where('is_active', 1)
            ->with('activeFields')
            ->first();
    }

    /**
     * Seed initial default templates for standard role types if empty.
     */
    public function seedDefaultTemplates(): void
    {
        if (WorkReportTemplateM::count() > 0) return;

        DB::transaction(function () {
            // 1. Developer Template
            $dev = WorkReportTemplateM::create([
                'name' => 'Developer Daily Work Report',
                'code' => 'dev_report',
                'employee_role_type' => 'developer',
                'description' => 'Standard daily work report template for Software Developers',
                'is_active' => 1,
            ]);

            $devFields = [
                ['field_key' => 'module', 'field_label' => 'Module / Component', 'field_type' => 'text', 'placeholder' => 'e.g. Authentication, Billing', 'is_required' => 1, 'sort_order' => 1],
                ['field_key' => 'work_type', 'field_label' => 'Work Type', 'field_type' => 'select', 'options_json' => ['Feature', 'Bug Fix', 'Enhancement', 'Research', 'Code Review', 'Support'], 'is_required' => 1, 'sort_order' => 2],
                ['field_key' => 'git_pr_reference', 'field_label' => 'Git Branch / PR Link', 'field_type' => 'text', 'placeholder' => 'e.g. PR #104 or branch-name', 'is_required' => 0, 'sort_order' => 3],
                ['field_key' => 'blocker', 'field_label' => 'Blockers / Issues', 'field_type' => 'textarea', 'placeholder' => 'Describe any blockers faced', 'is_required' => 0, 'sort_order' => 4],
            ];

            foreach ($devFields as $field) {
                WorkReportTemplateFieldM::create(array_merge($field, ['template_id' => $dev->id]));
            }

            // 2. Tester / QA Template
            $qa = WorkReportTemplateM::create([
                'name' => 'QA / Tester Work Report',
                'code' => 'qa_report',
                'employee_role_type' => 'tester',
                'description' => 'Daily work report template for QA / Test Engineers',
                'is_active' => 1,
            ]);

            $qaFields = [
                ['field_key' => 'testing_type', 'field_label' => 'Testing Type', 'field_type' => 'select', 'options_json' => ['Manual', 'Automation', 'API Testing', 'Performance', 'Regression'], 'is_required' => 1, 'sort_order' => 1],
                ['field_key' => 'test_cases_passed', 'field_label' => 'Test Cases Passed', 'field_type' => 'number', 'placeholder' => '0', 'is_required' => 1, 'sort_order' => 2],
                ['field_key' => 'test_cases_failed', 'field_label' => 'Test Cases Failed', 'field_type' => 'number', 'placeholder' => '0', 'is_required' => 1, 'sort_order' => 3],
                ['field_key' => 'bug_ids', 'field_label' => 'Bug IDs / Links', 'field_type' => 'text', 'placeholder' => 'e.g. BUG-101, BUG-102', 'is_required' => 0, 'sort_order' => 4],
            ];

            foreach ($qaFields as $field) {
                WorkReportTemplateFieldM::create(array_merge($field, ['template_id' => $qa->id]));
            }

            // 3. Team Lead Template
            $tl = WorkReportTemplateM::create([
                'name' => 'Team Lead Work Report',
                'code' => 'tl_report',
                'employee_role_type' => 'team_lead',
                'description' => 'Daily work report for Team Leads covering technical work and management',
                'is_active' => 1,
            ]);

            $tlFields = [
                ['field_key' => 'technical_work', 'field_label' => 'Technical Work Done', 'field_type' => 'textarea', 'placeholder' => 'Describe coding/architecture work', 'is_required' => 0, 'sort_order' => 1],
                ['field_key' => 'team_coordination', 'field_label' => 'Team Coordination & Reviews', 'field_type' => 'textarea', 'placeholder' => 'Describe team management & code reviews', 'is_required' => 0, 'sort_order' => 2],
                ['field_key' => 'blockers_resolved', 'field_label' => 'Blockers Resolved', 'field_type' => 'textarea', 'placeholder' => 'Blockers resolved for team members', 'is_required' => 0, 'sort_order' => 3],
            ];

            foreach ($tlFields as $field) {
                WorkReportTemplateFieldM::create(array_merge($field, ['template_id' => $tl->id]));
            }

            // 4. General Template
            $gen = WorkReportTemplateM::create([
                'name' => 'General Work Report',
                'code' => 'general_report',
                'employee_role_type' => 'general',
                'description' => 'Fallback work report template',
                'is_active' => 1,
            ]);

            WorkReportTemplateFieldM::create([
                'template_id' => $gen->id,
                'field_key' => 'remarks',
                'field_label' => 'Additional Remarks',
                'field_type' => 'textarea',
                'placeholder' => 'Any additional notes',
                'is_required' => 0,
                'sort_order' => 1,
            ]);
        });
    }
}
