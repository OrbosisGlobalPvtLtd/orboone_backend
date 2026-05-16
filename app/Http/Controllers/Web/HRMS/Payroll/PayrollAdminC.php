<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Payroll\SalaryStructureM as SalaryStructure;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Payroll\PayrollM as Payroll;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use App\Models\HRMS\Payroll\FnFM as FnF;
use App\Models\HRMS\Payroll\ClaimM as Claim;
use Illuminate\Http\Request;
use App\Models\HRMS\Payroll\StatutorySettingM as StatutorySetting;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollAdminC extends Controller
{
    use HrmsCrudPage;

      public function structuresIndex()
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.view') || $this->userHasPermission('payroll.salary_structure.manage'), 403);
        $structures = SalaryStructure::withCount('employees')->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
        return view('hrms.payroll.index', compact('structures', 'accesses'))->with('active', 'payroll_index');
    }

    public function structuresCreate()
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
        return view('hrms.payroll.create', compact('accesses'))->with('active', 'payroll_index');
    }

    public function salary_structure(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $data = $request->validate([
            'name'                  => 'required|string',
            'basic_salary'          => 'required|numeric',
            'hra_percent'           => 'required|numeric',
            'allowance'             => 'nullable|numeric',
            'pt_amount'             => 'nullable|numeric',
            'effective_date'        => 'required|date',
        ]);

        $structure = SalaryStructure::create([
            'name'           => $data['name'],
            'basic_salary'   => $data['basic_salary'],
            'hra_percent'    => $data['hra_percent'],
            'allowance'      => $data['allowance'] ?? 0,
            'pt_amount'      => $data['pt_amount'] ?? 0,
            'effective_date' => $data['effective_date'],
        ]);

        return redirect()
            ->route('pages.payroll.index')
            ->with('success', 'Salary structure created successfully');
    }

    public function structuresEdit($id)
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $structure = SalaryStructure::findOrFail($id);
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
        return view('hrms.payroll.edit', compact('structure', 'accesses'))->with('active', 'payroll_index');
    }

    public function structuresUpdate($id, Request $request)
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $structure = SalaryStructure::findOrFail($id);

        $data = $request->validate([
            'name'                  => 'required|string',
            'basic_salary'          => 'required|numeric',
            'hra_percent'           => 'required|numeric',
            'allowance'             => 'nullable|numeric',
            'pt_amount'             => 'nullable|numeric',
            'effective_date'        => 'required|date',
        ]);

        // Mark that it is effective from next month (business rule)
        $structure->update([
            'name'           => $data['name'],
            'basic_salary'   => $data['basic_salary'],
            'hra_percent'    => $data['hra_percent'],
            'allowance'      => $data['allowance'] ?? 0,
            'pt_amount'      => $data['pt_amount'] ?? 0,
            'effective_date' => $data['effective_date'],
        ]);

        return redirect()
            ->route('pages.payroll.index')
            ->with('success', 'Salary structure updated (effective next month)');
    }

    public function structuresAssignForm()
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $employees  = Employee::with('salaryStructure')->get();
        $structures = SalaryStructure::all();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
        return view('hrms.payroll.index', compact('employees', 'structures', 'accesses'));
    }

    public function dashboard()
    {
        abort_unless($this->userHasPermission('payroll.dashboard.view'), 403);
        $employeesCount = Employee::where('is_active', true)->count();
        $totalSalaries = Payroll::where('month', now()->month)->where('year', now()->year)->sum('net_salary');
        $recentPayrolls = Payroll::with('employee')->orderBy('id', 'desc')->limit(5)->get();
        $employees = Employee::where('is_active', true)->leftJoin('users', 'users.id', '=', 'employees_new.user_id')->select('employees_new.*', 'users.name as user_name')->orderByRaw('COALESCE(users.name, employees_new.employee_code)')->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.payroll.dashboard', compact('employeesCount', 'totalSalaries', 'recentPayrolls', 'employees', 'accesses'));
    }

    public function structuresAssign(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.salary_structure.manage'), 403);
        $data = $request->validate([
            'assignments' => 'required|array',
            'assignments.*' => 'nullable|exists:salary_structures,id',
        ]);

        foreach ($data['assignments'] as $employeeId => $structureId) {
            $employee = Employee::findOrFail($employeeId);
            $employee->salary_structure_id = $structureId ?: null;
            $employee->save();
        }

        return back()->with('success', 'Salary structures assigned successfully.');
    }

    public function salaryStructure()
    {
        $employee = Employee::with('salaryStructure')
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.payroll.salary_structure', [
            'employee' => $employee,
            'structure'=> $employee->salaryStructure,
            'accesses' => $accesses,
            'active' => 'my_salary_structure'
        ]);
    }

public function payrollRunForm()
{
    abort_unless($this->userHasPermission('payroll.generate.view'), 403);
    $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
    return view('hrms.payroll.payrollrun', compact('accesses'))->with('active', 'payroll_run');
}

public function payrollRun(Request $request)
{
    abort_unless($this->userHasPermission('payroll.generate.process'), 403);
    $request->validate([
        'month' => 'required|date_format:Y-m',
    ]);

    $monthInput = $request->month;
    $startDate = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
    $endDate   = Carbon::createFromFormat('Y-m', $monthInput)->endOfMonth();

    $month = $startDate->month; // 1-12
    $year = $startDate->year;

    $employees = Employee::with('salaryStructure')
        ->where('is_active', true)
        ->get();

    foreach ($employees as $employee) {
        $this->ensurePayrollForEmployee($employee, $startDate, $endDate, $month, $year);
    }

    return redirect()
        ->route('pages.payroll.preview', $monthInput)
        ->with('success', 'Payroll run completed successfully.');
}

public function payrollPreview($monthInput)
{
    abort_unless($this->userHasPermission('payroll.generate.view'), 403);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payrolls = Payroll::with('employee')
        ->where('month', $month)
        ->where('year', $year)
        ->get();
    $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

    return view('hrms.payroll.preview', compact('payrolls', 'monthInput', 'accesses'))->with('active', 'payroll_run');
}

public function payrollLock($monthInput)
{
    abort_unless($this->userHasPermission('payroll.generate.process'), 403);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    Payroll::where('month', $month)
        ->where('year', $year)
        ->update(['status' => 'locked']);

    // here you can auto-generate payslips if you want
    return back()->with('success', "Payroll locked for {$monthInput}.");
}
public function monthlyList()
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();

    $payrolls = Payroll::where('employee_id', $employee->id)
        ->orderBy('month', 'desc')
        ->get();
    $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

    return view('hrms.payroll.monthly_list', compact('payrolls', 'accesses'))->with('active', 'my_monthly_salary');
}

public function monthlyDetail($monthInput)
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payroll = Payroll::where('employee_id', $employee->id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();
    $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

    return view('hrms.payroll.monthly_detail', compact('payroll', 'monthInput', 'accesses'))->with('active', 'my_monthly_salary');
}
public function payslipsByMonth($monthInput)
{
    abort_unless($this->userHasPermission('payroll.payslips.view_all'), 403);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payrolls = Payroll::with(['employee', 'payslip'])
        ->where('month', $month)
        ->where('year', $year)
        ->where('status', 'locked')
        ->get();
    $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

    return view('hrms.payroll.payslipindex', compact('payrolls', 'monthInput', 'accesses'))->with('active', 'payroll_run');
}

public function payslipsGenerate($monthInput)
{
    abort_unless($this->userHasPermission('payroll.generate.process'), 403);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payrolls = Payroll::with('employee')
        ->where('month', $month)
        ->where('year', $year)
        ->where('status', 'locked')
        ->get();

    if ($payrolls->isEmpty()) {
        return back()->with('error', 'No locked payrolls found for that month.');
    }

    foreach ($payrolls as $p) {
        $pdf = Pdf::loadView('hrms.payroll.payslip_pdf', [
            'p' => $p,
            'month' => $monthInput,
        ]);

        $dir = 'payslips/' . $year . '/' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $fileName = 'salary-slip-' . $monthInput . '-employee-' . $p->employee_id . '.pdf';
        $filePath = $dir . '/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        Payslip::updateOrCreate(
            [
                'employee_id' => $p->employee_id,
                'payroll_id' => $p->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'file_path' => $filePath,
            ]
        );
    }

    return back()->with('success', 'Payslips generated successfully.');
}

/**
 * Download a zip containing all employee payslips for a given month.
 * Admin-level feature, called from the payslip-index page.
 */
public function downloadAllPayslips($monthInput)
{
    abort_unless($this->userHasPermission('payroll.payslips.view_all'), 403);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payrolls = Payroll::with('employee')
        ->where('month', $month)
        ->where('year', $year)
        ->where('status', 'locked')
        ->get();

    if ($payrolls->isEmpty()) {
        return back()->with('error', 'No locked payrolls found for that month.');
    }

    // create temporary zip
    $zipName = "payslips_{$monthInput}.zip";
    $zipPath = storage_path('app/public/' . $zipName);
    $zip = new \ZipArchive;
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return back()->with('error', 'Could not create zip archive.');
    }

    foreach ($payrolls as $p) {
        $pdf = Pdf::loadView('hrms.payroll.payslip_pdf', [
            'p' => $p,
            'month' => $monthInput
        ]);
        $fileName = 'Salary Slip-' . date('F-Y', strtotime($monthInput . '-01'))
            . '-' . ($p->employee->display_name ?? $p->employee->employee_code ?? $p->employee_id) . '.pdf';
        $zip->addFromString($fileName, $pdf->output());
    }

    $zip->close();

    // return download and remove when done
    return response()->download($zipPath)->deleteFileAfterSend(true);
}

    public function payslips()
    {
        abort_unless($this->userHasPermission('payroll.payslips.view_own') || $this->userHasPermission('payroll.payslips.view_all') || $this->userHasPermission('payroll.payslips.view'), 403);
        if ($this->userHasPermission('payroll.payslips.view_all')) {
            $payslips = Payslip::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
            $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
            return view('hrms.payroll.payslips', compact('payslips', 'accesses'))->with('active', 'my_payslips');
        }

        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        $payslips = Payslip::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.payroll.payslips', compact('payslips', 'accesses'))->with('active', 'my_payslips');
    }

public function download($id)
{
    $payslip = Payslip::findOrFail($id);
    if (! $this->userHasPermission('payroll.payslips.view_all')) {
        abort_unless((int) $payslip->employee_id === (int) $this->ownEmployeeId(), 403);
    }

    // Check file path exists in DB
    if (empty($payslip->file_path)) {
        return back()->with('error', 'Payslip file path not found.');
    }

    // Check file exists in storage
    if (!Storage::disk('public')->exists($payslip->file_path)) {
        return back()->with('error', 'Payslip PDF file not found in storage.');
    }

    // Optional security (employee can download only own payslip)
    /*
    if (optional(auth()->user()->employee)->id !== $payslip->employee_id) {
        abort(403, 'Unauthorized access');
    }
    */

    return Storage::disk('public')->download(
        $payslip->file_path,
        'Payslip_'.$payslip->month.'_'.$payslip->year.'.pdf'
    );
}

public function downloadByEmployeeMonth($employee_id, $monthInput)
{
    $employee = Employee::findOrFail($employee_id);
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payroll = Payroll::where('employee_id', $employee->id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    if (!$payroll) {
        $payroll = $this->ensurePayrollForEmployee(
            $employee->load('salaryStructure'),
            $startDate->copy()->startOfMonth(),
            $startDate->copy()->endOfMonth(),
            $month,
            $year
        );
    }

    // Check if payslip exists, if not generate it
    $payslip = Payslip::where('employee_id', $employee->id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    if (!$payslip || !Storage::disk('public')->exists($payslip->file_path ?? '')) {
        $pdf = Pdf::loadView('hrms.payroll.payslip_pdf', [
            'p' => $payroll,
            'month' => $monthInput,
        ]);

        $dir = 'payslips/' . $year . '/' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $fileName = 'salary-slip-' . $monthInput . '-employee-' . $employee->id . '.pdf';
        $filePath = $dir . '/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        $payslip = Payslip::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'payroll_id' => $payroll->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'file_path' => $filePath,
            ]
        );
    }

    return Storage::disk('public')->download(
        $payslip->file_path,
        'Salary Slip-' . date('F-Y', strtotime($monthInput . '-01')) . '-' . ($employee->display_name ?? $employee->employee_code ?? $employee->id) . '.pdf'
    );
}

public function salarySlipForm()
    {
        abort_unless($this->userHasPermission('payroll.payslips.view_all'), 403);
        $employees = Employee::where('is_active', true)->leftJoin('users', 'users.id', '=', 'employees_new.user_id')->select('employees_new.*', 'users.name as user_name')->orderByRaw('COALESCE(users.name, employees_new.employee_code)')->get();
        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();
        return view('hrms.payroll.salary_slip_form', compact('employees', 'accesses'))->with('active', 'payroll_run');
    }

public function salarySlipDownload(Request $request)
{
    abort_unless($this->userHasPermission('payroll.payslips.view_all'), 403);
    $data = $request->validate([
        'employee_id' => 'required|integer|exists:employees_new,id',
        'month' => 'required|date_format:Y-m',
    ]);

    return redirect()->route(
        'pages.payroll.payslip.download.employee',
        [$data['employee_id'], $data['month']]
    );
}

private function ensurePayrollForEmployee(Employee $employee, Carbon $startDate, Carbon $endDate, int $month, int $year): ?Payroll
{
    $employee->loadMissing('salaryStructure');

    if (!$employee->salaryStructure) {
        return null;
    }

    $structure = $employee->salaryStructure;

    $existing = Payroll::where('employee_id', $employee->id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    // If already locked, do not recalculate
    if ($existing && $existing->status === 'locked') {
        return $existing;
    }

    $totalWorkingDays = $startDate->diffInDays($endDate) + 1;

    $attendanceCount = Attendance::where('user_id', $employee->user_id)
        ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
        ->count();

    $paidDays = Attendance::where('user_id', $employee->user_id)
        ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
        ->where('status', 'Present')
        ->count();

    // If no attendance records at all, assume full attendance (fallback for new system)
    // Otherwise, use the actual count of 'Present' days
    if ($attendanceCount === 0) {
        $paidDays = $totalWorkingDays;
    }

    $dailyRate = $totalWorkingDays > 0 ? ($structure->basic_salary / $totalWorkingDays) : 0;

    $basic = $dailyRate * $paidDays;
    $hra = ((float) $structure->hra_percent / 100) * $basic;
    $allowance = (float) ($structure->allowance ?? 0);

    $grossSalary = $basic + $hra + $allowance;

    $pt = (float) ($structure->pt_amount ?? 0);
    $totalDeductions = $pt;
    $netSalary = $grossSalary - $totalDeductions;

    return Payroll::updateOrCreate(
        [
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
        ],
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
            'status' => $existing?->status ?? 'generated',
        ]
    );
}

public function statutorySettingsForm()
{
    $settings = StatutorySetting::first();
    return view('hrms.payroll.statutorysettings', compact('settings'));
}

public function statutorySettingsSave(Request $request)
{
    $data = $request->validate([
        'pf_percent'  => 'nullable|numeric',
        'esi_percent' => 'nullable|numeric',
        'pt_percent'  => 'nullable|numeric',
        'tds_slabs'   => 'nullable|string', // JSON or text
    ]);

    $settings = StatutorySetting::firstOrNew([]);
    $settings->fill($data)->save();

    return back()->with('success', 'Statutory settings updated.');
}

public function statutoryReportForm()
{
    return view('hrms.payroll.statutoryreport_form');
}

public function statutoryReportView(Request $request)
{
    $request->validate(['month' => 'required|date_format:Y-m']);

    $monthInput = $request->month;
    $startDate = Carbon::createFromFormat('Y-m', $monthInput);
    $month = $startDate->month;
    $year = $startDate->year;

    $payrolls = Payroll::where('month', $month)->where('year', $year)->get();

    $pt_total = $payrolls->sum('pt_deduction'); // from your calculation field

    return view('hrms.payroll.statutoryreport_view', compact('payrolls', 'monthInput', 'pt_total'));
}

public function deductions()
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();
    $payrolls = Payroll::where('employee_id', $employee->id)
        ->orderBy('month', 'desc')
        ->get();

    return view('hrms.payroll.deductions', compact('payrolls'));
}
public function fnfPendingEmployees()
{
    $employees = Employee::whereDoesntHave('fnf')->get();

    return view('hrms.payroll.fnfpending', compact('employees'));
}


public function fnfCalculateForm(Employee $employee)
{
    // here you can pre-calc pending salary, leave encashment etc
    return view('hrms.payroll.fnfcalculate', compact('employee'));
}

public function fnfProcess(Employee $employee, Request $request)
{
    $data = $request->validate([
        'last_working_day'  => 'required|date',
        'pending_salary'    => 'required|numeric',
        'leave_encashment'  => 'required|numeric',
        'reimbursements'    => 'required|numeric',
        'deductions'        => 'required|numeric',
    ]);

    $net_payable = $data['pending_salary'] + $data['leave_encashment'] + $data['reimbursements'] - $data['deductions'];

    $fnf = FnF::create([
        'employee_id'      => $employee->id,
        'last_working_day' => $data['last_working_day'],
        'pending_salary'   => $data['pending_salary'],
        'leave_encashment' => $data['leave_encashment'],
        'reimbursements'   => $data['reimbursements'],
        'deductions'       => $data['deductions'],
        'net_payable'      => $net_payable,
    ]);

    return redirect()->route('pages.payroll.fnfpending')
        ->with('success', 'F&F processed for '.($employee->display_name ?? $employee->employee_code ?? $employee->id));
}

public function fnfView()
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();
    $fnf = FnF::where('employee_id', $employee->id)->first();

    return view('hrms.payroll.fnf', compact('fnf'));
}

public function claimsIndex()
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();
    $claims = Claim::where('employee_id', $employee->id)
        ->orderBy('created_at','desc')
        ->get();

    return view('hrms.payroll.claims.index', compact('claims'));
}

public function claimsCreate()
{
    return view('hrms.payroll.claims.create');
}

public function claimsStore(Request $request)
{
    $employee = Employee::where('user_id', auth()->id())->firstOrFail();

    $data = $request->validate([
        'amount'   => 'required|numeric',
        'category' => 'required|string',
        'file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $filePath = null;
    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('claims');
    }

    Claim::create([
        'employee_id' => $employee->id,
        'amount'      => $data['amount'],
        'category'    => $data['category'],
        'file'        => $filePath,
        'status'      => 'pending',
    ]);

    return redirect()->route('pages.payroll.claims.index')
        ->with('success', 'Claim submitted successfully.');
}

// public function claimsIndex()
// {
//     $claims = Claim::with('employee')->orderBy('created_at', 'desc')->get();
//     return view('hrms.payroll.claims.index', compact('claims'));
// }

public function claimsApprove($id, Request $request)
{
    $claim = Claim::findOrFail($id);
    $claim->status = 'approved';
    $claim->save();

    // Add to payroll earning of that month (your logic)
    return back()->with('success', 'Claim approved.');
}

public function claimsReject($id, Request $request)
{
    $claim = Claim::findOrFail($id);
    $claim->status = 'rejected';
    $claim->save();

    return back()->with('success', 'Claim rejected.');
}
}

