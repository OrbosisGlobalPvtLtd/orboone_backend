<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Payroll\SalaryStructureM as SalaryStructure;
use App\Models\HRMS\Payroll\PayrollM as Payroll;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class FullMarchPayrollSeeder extends Seeder
{
    public function run()
    {
        $monthInput = '2026-03';
        $startDate = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $monthInput)->endOfMonth();
        $month = $startDate->month;
        $year = $startDate->year;

        // 1. Ensure a default structure exists
        $defaultStructure = SalaryStructure::firstOrCreate(
            ['name' => 'General Employee Structure'],
            [
                'basic_salary' => 15000,
                'hra_percent' => 10,
                'allowance' => 2000,
                'pt_amount' => 200,
                'effective_date' => '2025-01-01',
            ]
        );

        $employees = Employee::where('is_active', true)->get();

        foreach ($employees as $employee) {
            // 2. Assign structure if not assigned
            if (!$employee->salary_structure_id) {
                $employee->update(['salary_structure_id' => $defaultStructure->id]);
            }

            // 3. Generate Attendance (most days present, some random absences for variety)
            for ($i = 0; $i < 31; $i++) {
                $date = $startDate->copy()->addDays($i);
                $status = (rand(1, 10) > 1) ? 'Present' : 'Absent'; // 90% attendance
                
                // For Hemant (ID 2), ensure 100% attendance as requested
                if ($employee->id == 2) {
                    $status = 'Present';
                }

                Attendance::updateOrCreate(
                    ['user_id' => $employee->user_id, 'date' => $date->toDateString()],
                    ['status' => $status, 'clock_in' => '09:00:00', 'clock_out' => '18:00:00']
                );
            }

            // 4. Generate Payroll record (using the logic from the controller)
            $this->generatePayroll($employee, $startDate, $endDate, $month, $year);
        }
    }

    private function generatePayroll($employee, $startDate, $endDate, $month, $year)
    {
        $employee->load('salaryStructure');
        $structure = $employee->salaryStructure;

        $totalWorkingDays = $startDate->diffInDays($endDate) + 1;
        $paidDays = Attendance::where('user_id', $employee->user_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'Present')
            ->count();

        $dailyRate = $totalWorkingDays > 0 ? ($structure->basic_salary / $totalWorkingDays) : 0;
        $basic = $dailyRate * $paidDays;
        $hra = ($structure->hra_percent / 100) * $basic;
        $allowance = (float)$structure->allowance;
        $grossSalary = $basic + $hra + $allowance;
        $pt = (float)$structure->pt_amount;
        $totalDeductions = $pt;
        $netSalary = $grossSalary - $totalDeductions;

        $payroll = Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $month, 'year' => $year],
            [
                'basic' => $basic,
                'hra' => $hra,
                'allowance' => $allowance,
                'gross_salary' => $grossSalary,
                'pt' => $pt,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'working_days' => $totalWorkingDays,
                'paid_days' => $paidDays,
                'status' => 'locked', // Pre-lock it for the seeder
            ]
        );

        // 5. Generate Payslip PDF
        $monthInput = sprintf('%04d-%02d', $year, $month);
        $pdf = Pdf::loadView('pages.payroll.payslip_pdf', [
            'p' => $payroll,
            'month' => $monthInput,
        ]);

        $dir = 'payslips/' . $year . '/' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $fileName = 'salary-slip-' . $monthInput . '-employee-' . $employee->id . '.pdf';
        $filePath = $dir . '/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        Payslip::updateOrCreate(
            ['employee_id' => $employee->id, 'payroll_id' => $payroll->id, 'month' => $month, 'year' => $year],
            ['file_path' => $filePath]
        );
    }
}
