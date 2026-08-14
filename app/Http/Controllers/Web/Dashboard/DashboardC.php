<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\HRMS\Dashboard\DashboardResolverS;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DashboardC extends Controller
{
    private DashboardResolverS $dashboardResolver;

    public function __construct(DashboardResolverS $dashboardResolver)
    {
        $this->middleware('auth');
        $this->dashboardResolver = $dashboardResolver;
    }

    public function redirectDashboard()
    {
        $role = $this->dashboardResolver->resolveRole(auth()->user());

        return redirect()->route($this->dashboardResolver->routeNameFor($role));
    }

    public function superAdmin()
    {
        return $this->renderRoleDashboard('super_admin');
    }

    public function hrAdmin()
    {
        return $this->renderRoleDashboard('hr_admin');
    }

    public function admin()
    {
        return $this->renderRoleDashboard('admin');
    }

    public function financeAdmin()
    {
        return $this->renderRoleDashboard('finance_admin');
    }

    public function projectAdmin()
    {
        return $this->renderRoleDashboard('project_admin');
    }

    public function operationsAdmin()
    {
        return $this->renderRoleDashboard('operations_admin');
    }

    public function customAdmin()
    {
        return $this->renderRoleDashboard('custom_admin');
    }

    public function manager()
    {
        return $this->renderRoleDashboard('manager');
    }

    public function employee()
    {
        return $this->renderRoleDashboard('employee');
    }

    public function getAttendanceCalendar(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
        }

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $todayDate = now()->toDateString();

        $attendances = \Illuminate\Support\Facades\DB::table('attendances')
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('attendance_date');

        $holidays = \Illuminate\Support\Facades\DB::table('holidays')
            ->where('is_active', 1)
            ->whereBetween('holiday_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('holiday_date');

        $leaves = \Illuminate\Support\Facades\DB::table('leave_requests')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $leaveDates = [];
        foreach ($leaves as $l) {
            $s = \Carbon\Carbon::parse($l->start_date);
            $e = \Carbon\Carbon::parse($l->end_date);
            while ($s->lte($e)) {
                $leaveDates[$s->toDateString()] = $l;
                $s->addDay();
            }
        }

        $daysData = [];
        $summary = [
            'present' => 0,
            'late' => 0,
            'half_day' => 0,
            'absent' => 0,
            'leave' => 0,
            'holiday' => 0,
            'week_off' => 0,
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = \Carbon\Carbon::createFromDate($year, $month, $day);
            $dateStr = $dateObj->toDateString();
            $isSunday = $dateObj->isSunday();
            $isPast = $dateObj->lt(now()->startOfDay());
            $isToday = $dateStr === $todayDate;

            $att = $attendances[$dateStr] ?? null;
            $holiday = $holidays[$dateStr] ?? null;
            $leave = $leaveDates[$dateStr] ?? null;

            $statusKey = 'upcoming';
            $label = 'Upcoming';
            $color = '#94A3B8';
            $bg = '#F8FAFC';
            $punchIn = null;
            $punchOut = null;
            $workTime = null;

            if ($att) {
                $punchIn = $att->punch_in_time ? \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') : null;
                $punchOut = $att->punch_out_time ? \Carbon\Carbon::parse($att->punch_out_time)->format('h:i A') : null;
                if ($att->total_work_minutes) {
                    $workTime = floor($att->total_work_minutes / 60) . 'h ' . ($att->total_work_minutes % 60) . 'm';
                }

                $typeCode = strtolower((string) ($att->attendanceType?->code ?? ''));
                $st = strtolower((string) ($att->attendance_status ?? ''));
                $remarks = strtolower((string) ($att->remarks ?? ''));

                if ($typeCode === 'week_off' || $st === 'week_off' || str_contains($remarks, 'week off') || str_contains($remarks, 'weekly off')) {
                    $statusKey = 'week_off';
                    $label = 'Week Off';
                    $color = '#64748B';
                    $bg = '#F1F5F9';
                    $summary['week_off']++;
                } elseif ($typeCode === 'holiday' || $st === 'holiday') {
                    $statusKey = 'holiday';
                    $label = $att->remarks ? $att->remarks : 'Holiday';
                    $color = '#6366F1';
                    $bg = '#EEF2FF';
                    $summary['holiday']++;
                } elseif ($typeCode === 'leave' || $st === 'leave' || $st === 'on_leave') {
                    $statusKey = 'leave';
                    $label = 'On Leave';
                    $color = '#3B82F6';
                    $bg = '#DBEAFE';
                    $summary['leave']++;
                } elseif ($att->is_half_day || $typeCode === 'half_day' || $st === 'half_day') {
                    $statusKey = 'half_day';
                    $label = 'Half Day';
                    $color = '#D97706';
                    $bg = '#FEF3C7';
                    $summary['half_day']++;
                } elseif ($att->is_late || $typeCode === 'late' || $st === 'late' || $st === 'late_present') {
                    $statusKey = 'late';
                    $label = 'Late';
                    $color = '#F59E0B';
                    $bg = '#FEF3C7';
                    $summary['late']++;
                } elseif ($att->is_lwp || $typeCode === 'lwp' || $typeCode === 'absent' || $st === 'absent' || $st === 'lwp') {
                    $statusKey = 'absent';
                    $label = 'Absent';
                    $color = '#EF4444';
                    $bg = '#FEE2E2';
                    $summary['absent']++;
                } elseif (!empty($att->punch_in_time) || $typeCode === 'present' || $st === 'present') {
                    $statusKey = 'present';
                    $label = 'Present';
                    $color = '#10B981';
                    $bg = '#D1FAE5';
                    $summary['present']++;
                } else {
                    if ($isSunday) {
                        $statusKey = 'week_off';
                        $label = 'Sunday Off';
                        $color = '#64748B';
                        $bg = '#F1F5F9';
                        $summary['week_off']++;
                    } elseif ($isPast) {
                        $statusKey = 'absent';
                        $label = 'Absent';
                        $color = '#EF4444';
                        $bg = '#FEE2E2';
                        $summary['absent']++;
                    } else {
                        $statusKey = 'upcoming';
                        $label = 'Upcoming';
                        $color = '#94A3B8';
                        $bg = '#F8FAFC';
                    }
                }
            } elseif ($holiday) {
                $statusKey = 'holiday';
                $label = 'Holiday: ' . $holiday->title;
                $color = '#6366F1';
                $bg = '#EEF2FF';
                $summary['holiday']++;
            } elseif ($leave) {
                $statusKey = 'leave';
                $label = 'On Leave';
                $color = '#3B82F6';
                $bg = '#DBEAFE';
                $summary['leave']++;
            } elseif ($isSunday) {
                $statusKey = 'week_off';
                $label = 'Sunday Off';
                $color = '#64748B';
                $bg = '#F1F5F9';
                $summary['week_off']++;
            } elseif ($isPast) {
                $statusKey = 'absent';
                $label = 'Absent';
                $color = '#EF4444';
                $bg = '#FEE2E2';
                $summary['absent']++;
            } else {
                $statusKey = 'upcoming';
                $label = 'Upcoming';
                $color = '#94A3B8';
                $bg = '#F8FAFC';
            }

            $daysData[$dateStr] = [
                'day' => $day,
                'date' => $dateStr,
                'day_name' => $dateObj->format('D'),
                'status' => $statusKey,
                'label' => $label,
                'color' => $color,
                'bg' => $bg,
                'is_today' => $isToday,
                'punch_in' => $punchIn,
                'punch_out' => $punchOut,
                'work_time' => $workTime,
            ];
        }

        return response()->json([
            'status' => true,
            'year' => $year,
            'month' => $month,
            'month_name' => $startDate->format('F Y'),
            'first_day_of_week' => $startDate->dayOfWeekIso - 1,
            'days_in_month' => $daysInMonth,
            'days' => $daysData,
            'summary' => $summary,
        ]);
    }

    public function adminIndex()
    {
        return $this->redirectDashboard();
    }

    public function employeeIndex()
    {
        return $this->redirectDashboard();
    }

    public function generateStorageLink()
    {
        $user = auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super_admin');

        if (! $isSuperAdmin || ! App::environment(['local', 'development'])) {
            abort(403, 'Unauthorized action.');
        }

        $link = public_path('storage');

        if (File::exists($link)) {
            return response()->json([
                'status' => true,
                'message' => 'Storage link already exists',
            ]);
        }

        try {
            Artisan::call('storage:link');

            return response()->json([
                'status' => true,
                'message' => 'Storage link created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create storage link',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renderRoleDashboard(string $requestedRole)
    {
        $user = auth()->user();

        if (! $this->dashboardResolver->canViewRole($user, $requestedRole)) {
            return redirect()->route('dashboard');
        }

        $role = $requestedRole === 'employee'
            ? $this->dashboardResolver->resolveRole($user)
            : $requestedRole;

        if ($requestedRole === 'employee' && $role !== 'employee') {
            return redirect()->route($this->dashboardResolver->routeNameFor($role));
        }

        $dashboard = $this->dashboardResolver->dashboardData($role, $user);

        return view($this->dashboardResolver->viewFor($role), compact('dashboard'));
    }
}
