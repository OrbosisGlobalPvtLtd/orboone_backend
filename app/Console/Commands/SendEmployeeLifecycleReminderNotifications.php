<?php

namespace App\Console\Commands;

use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SendEmployeeLifecycleReminderNotifications extends Command
{
    protected $signature = 'hrms:lifecycle-reminders';
    protected $description = 'Send internship and probation ending reminder notifications to HR/Admin users.';

    public function handle(NotificationS $notificationService): int
    {
        if (! Schema::hasTable('employees_new')) {
            $this->warn('employees_new table not found.');
            return self::SUCCESS;
        }

        $today = now()->startOfDay();

        $internshipCount = $this->sendInternshipReminders($notificationService, $today);
        $probationCount = $this->sendProbationReminders($notificationService, $today);

        $this->info("Lifecycle reminder notifications processed. Internship: {$internshipCount}, Probation: {$probationCount}");

        return self::SUCCESS;
    }

    private function sendInternshipReminders(NotificationS $notificationService, Carbon $today): int
    {
        $until = $today->copy()->addDays(10)->toDateString();
        $query = DB::table('employees_new')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                'employees_new.internship_end_date',
                'employees_new.internship_extended_to',
                'employees_new.internship_status',
                'users.name'
            )
            ->where('employees_new.employment_status', 'active')
            ->where('employees_new.employee_stage', 'internship')
            ->where(function ($q) {
                $q->whereNull('employees_new.internship_status')
                    ->orWhereIn('employees_new.internship_status', ['active', 'extended']);
            });

        if (Schema::hasColumn('employees_new', 'is_active')) {
            $query->where('employees_new.is_active', 1);
        }

        $employees = $query
            ->whereRaw(
                "COALESCE(employees_new.internship_extended_to, employees_new.internship_end_date) BETWEEN ? AND ?",
                [$today->toDateString(), $until]
            )
            ->get();

        $count = 0;

        foreach ($employees as $employee) {
            $targetDate = $employee->internship_extended_to ?: $employee->internship_end_date;

            if (! $targetDate) {
                continue;
            }

            $target = Carbon::parse($targetDate)->startOfDay();
            $remainingDays = $today->diffInDays($target, false);

            if ($remainingDays < 0 || $remainingDays > 10) {
                continue;
            }

            $actionUrl = route('hrms.employees.probation_internship', [
                'highlight' => (int) $employee->id,
                'highlight_employee' => (int) $employee->id,
                'stage' => 'internship',
            ]);

            $notificationService->notifyHrAndSuperAdmin(
                title: 'Internship ending soon',
                message: ($employee->name ?? 'Employee') . ' internship is going to complete on ' . $target->format('d M Y') . '.',
                type: 'internship_ending_reminder',
                routeName: 'hrms.employees.probation_internship',
                routeParams: [],
                data: [
                    'employee_id' => (int) $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->name,
                    'lifecycle_type' => 'internship',
                    'end_date' => $target->toDateString(),
                    'target_date' => $target->toDateString(),
                    'days_left' => $remainingDays,
                    'days_remaining' => $remainingDays,
                    'action_url' => $actionUrl,
                    'highlight_employee_id' => (int) $employee->id,
                    'stage' => 'internship',
                    'reminder_date' => $today->toDateString(),
                ]
            );

            $count++;
        }

        return $count;
    }

    private function sendProbationReminders(NotificationS $notificationService, Carbon $today): int
    {
        $until = $today->copy()->addDays(5)->toDateString();
        $query = DB::table('employees_new')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                'employees_new.probation_end_date',
                'employees_new.probation_status',
                'users.name'
            )
            ->where('employees_new.employment_status', 'active')
            ->where('employees_new.employee_stage', 'probation')
            ->where(function ($q) {
                $q->whereNull('employees_new.is_permanent')
                    ->orWhere('employees_new.is_permanent', 0);
            })
            ->where(function ($q) {
                $q->whereNull('employees_new.probation_status')
                    ->orWhereNotIn('employees_new.probation_status', ['scheduled_permanent', 'completed', 'confirmed']);
            })
            ->whereBetween('employees_new.probation_end_date', [$today->toDateString(), $until]);

        if (Schema::hasColumn('employees_new', 'is_active')) {
            $query->where('employees_new.is_active', 1);
        }

        $employees = $query->get();

        $count = 0;

        foreach ($employees as $employee) {
            if (! $employee->probation_end_date) {
                continue;
            }

            $target = Carbon::parse($employee->probation_end_date)->startOfDay();
            $remainingDays = $today->diffInDays($target, false);

            if ($remainingDays < 0 || $remainingDays > 5) {
                continue;
            }

            $actionUrl = route('hrms.employees.probation_internship', [
                'highlight' => (int) $employee->id,
                'highlight_employee' => (int) $employee->id,
                'stage' => 'probation',
            ]);

            $notificationService->notifyHrAndSuperAdmin(
                title: 'Probation ending soon',
                message: ($employee->name ?? 'Employee') . ' probation is ending on ' . $target->format('d M Y') . '.',
                type: 'probation_ending_reminder',
                routeName: 'hrms.employees.probation_internship',
                routeParams: [],
                data: [
                    'employee_id' => (int) $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->name,
                    'lifecycle_type' => 'probation',
                    'end_date' => $target->toDateString(),
                    'target_date' => $target->toDateString(),
                    'days_left' => $remainingDays,
                    'days_remaining' => $remainingDays,
                    'action_url' => $actionUrl,
                    'highlight_employee_id' => (int) $employee->id,
                    'stage' => 'probation',
                    'reminder_date' => $today->toDateString(),
                ]
            );

            $count++;
        }

        return $count;
    }
}
