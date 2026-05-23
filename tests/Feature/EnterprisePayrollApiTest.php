<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class EnterprisePayrollApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_employee_with_payslip_sees_own_payslips(): void
    {
        [$user, $employee] = $this->employeeUser('own');
        $payroll = $this->payroll($employee->id);
        $this->payslip($employee->id, $payroll->id);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/hrms/enterprise-payroll/payslips?year=2026')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.net_salary', 29800);
    }

    public function test_employee_cannot_access_another_employee_payslip(): void
    {
        [$user] = $this->employeeUser('first');
        [, $otherEmployee] = $this->employeeUser('second');
        $payroll = $this->payroll($otherEmployee->id);
        $payslip = $this->payslip($otherEmployee->id, $payroll->id);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/hrms/enterprise-payroll/payslips/{$payslip->id}")
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_missing_payslip_returns_json_404(): void
    {
        [$user] = $this->employeeUser('missing');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/hrms/enterprise-payroll/payslips/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Payslip not found.');
    }

    public function test_missing_pdf_returns_json_404(): void
    {
        [$user, $employee] = $this->employeeUser('pdf');
        $payroll = $this->payroll($employee->id);
        $payslip = $this->payslip($employee->id, $payroll->id, 'missing.pdf');

        Storage::fake('public');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/hrms/enterprise-payroll/payslips/{$payslip->id}/download")
            ->assertNotFound()
            ->assertJsonPath('message', 'Payslip PDF not found.');
    }

    public function test_reimbursement_submit_works(): void
    {
        [$user] = $this->employeeUser('claim');
        Storage::fake('public');
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/hrms/enterprise-payroll/reimbursements', [
            'title' => 'Travel claim',
            'claim_date' => '2026-05-20',
            'amount' => 450.75,
            'remarks' => 'Client visit',
            'attachment' => UploadedFile::fake()->create('receipt.pdf', 64, 'application/pdf'),
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Travel claim')
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_invalid_reimbursement_returns_validation_error_json(): void
    {
        [$user] = $this->employeeUser('invalid-claim');
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/hrms/enterprise-payroll/reimbursements', [
            'title' => '',
            'claim_date' => '',
            'amount' => 0,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['title', 'claim_date', 'amount']]);
    }

    private function employeeUser(string $suffix): array
    {
        $user = UserM::create([
            'name' => "Employee {$suffix}",
            'email' => "employee-{$suffix}-" . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . strtoupper($suffix) . '-' . uniqid(),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        return [$user, $employee];
    }

    private function payroll(int $employeeId): EnterprisePayrollM
    {
        return EnterprisePayrollM::create([
            'payroll_run_id' => 1,
            'employee_id' => $employeeId,
            'month' => 5,
            'year' => 2026,
            'total_working_days' => 22,
            'present_days' => 22,
            'payable_days' => 22,
            'gross_salary' => 30000,
            'total_deductions' => 200,
            'net_salary' => 29800,
            'status' => 'locked',
            'generated_at' => '2026-05-20 12:00:00',
        ]);
    }

    private function payslip(int $employeeId, int $payrollId, string $pdfPath = 'payslips/test.pdf'): EnterprisePayslipM
    {
        return EnterprisePayslipM::create([
            'payroll_id' => $payrollId,
            'employee_id' => $employeeId,
            'month' => 5,
            'year' => 2026,
            'payslip_no' => 'ORB-EP-2026-05-' . str_pad((string) $employeeId, 5, '0', STR_PAD_LEFT),
            'pdf_path' => $pdfPath,
            'generated_at' => '2026-05-20 12:00:00',
            'is_visible_to_employee' => true,
        ]);
    }
}
