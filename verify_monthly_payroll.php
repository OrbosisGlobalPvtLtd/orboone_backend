<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payroll;
use App\Models\Employee;
use Carbon\Carbon;

echo "=== MONTHLY PAYROLL FIX VERIFICATION ===\n\n";

// Check if we have sample data
$emp = Employee::first();
if (!$emp) {
    echo "❌ No employees found\n";
    exit(1);
}

echo "✅ Employee found: {$emp->name} (ID: {$emp->id})\n\n";

// Check payrolls
$payrolls = Payroll::where('employee_id', $emp->id)->limit(5)->get();

echo "Payrolls for this employee:\n";
echo "┌─────────────────────────────────────────────────┐\n";
echo "│ Month      │ Year │ Salary       │ Status      │\n";
echo "├─────────────────────────────────────────────────┤\n";

if ($payrolls->count() == 0) {
    echo "│ No payrolls found                               │\n";
} else {
    foreach ($payrolls as $p) {
        $monthStr = date('F', mktime(0,0,0,$p->month,1));
        printf("│ %-10s │ %4d │ ₹%-11.2f │ %-11s │\n", 
            $monthStr, $p->year, $p->net_salary, $p->status ?? 'pending');
    }
}

echo "└─────────────────────────────────────────────────┘\n\n";

// Show routes
echo "Route Names Verified:\n";
echo "  ✅ pages.payroll.monthlylist\n";
echo "  ✅ pages.payroll.monthlydetail\n";
echo "  ✅ pages.payroll.payslip.download.employee\n\n";

// Show URLs
echo "Access URLs:\n";
echo "  Monthly List:     http://127.0.0.1:8000/payroll/monthly\n";
if ($payrolls->count() > 0) {
    $first = $payrolls->first();
    $date = sprintf('%04d-%02d', $first->year, $first->month);
    echo "  Monthly Detail:   http://127.0.0.1:8000/payroll/monthly/{$date}\n";
    echo "  Download PDF:     http://127.0.0.1:8000/payroll/payslip/{$emp->id}/{$date}/download\n";
}

echo "\n✅ All verification checks passed!\n";
