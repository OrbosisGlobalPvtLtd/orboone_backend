<?php

namespace App\Http\Controllers\Api\V1\MobileApp;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeProfile;
use App\Services\HRMS\Document\EmployeeDocumentCompletionS;
use App\Services\HRMS\Attendance\AttendanceMobileService;
use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\HRMS\Leave\LeaveAllocationM as LeaveAllocation;
use App\Models\HRMS\Leave\LeaveRequestM as LeaveRequest;
use App\Models\Core\NotificationM as Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileDashboardController extends Controller
{
    public function __construct(
        private AttendanceMobileService $attendanceMobileService
    ) {
    }

    public function bootstrap()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => null
            ], 401);
        }

        // Fetch basic employee summary
        $employee = Employee::with([
            'user',
            'department',
            'designation',
            'systemRole',
            'reportingManager.user',
            'profile',
        ])->where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found.',
                'data' => null
            ], 404);
        }

        $profile = $employee->profile;
        if (!$profile) {
            $profile = EmployeeProfile::create([
                'employee_id' => $employee->id,
            ]);
            $employee->load('profile');
            $profile = $employee->profile;
        }

        // 1. Profile completion and gate status
        $profileGateStatus = $this->buildCompletionStatus($employee, $profile);

        // 2. User summary & employee summary
        $userSummary = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $employeeSummary = [
            'id'                            => $employee->id,
            'user_id'                       => $employee->user_id,
            'employee_code'                 => $employee->employee_code,
            'system_role_id'                => $employee->system_role_id,
            'department_id'                 => $employee->department_id,
            'designation_id'                => $employee->designation_id,
            'reporting_manager_employee_id' => $employee->reporting_manager_employee_id,
            'employment_type'               => $employee->employment_type,
            'experience_type'               => $profile->experience_type ?? 'fresher',
            'employee_stage'                => $employee->employee_stage,
            'work_mode'                     => $employee->work_mode,
            'work_schedule_type'            => $employee->work_schedule_type,
            'joining_date'                  => $employee->joining_date,
            'employment_status'             => $employee->employment_status,
            'is_active'                     => $employee->is_active,

            'department' => [
                'id'   => $employee->department?->id,
                'name' => $employee->department?->name,
            ],

            'designation' => [
                'id'   => $employee->designation?->id,
                'name' => $employee->designation?->name,
            ],

            'system_role' => [
                'id'   => $employee->systemRole?->id,
                'name' => $employee->systemRole?->name,
            ],

            'reporting_manager' => [
                'id'   => $employee->reportingManager?->id,
                'name' => $employee->reportingManager?->user?->name,
            ],
        ];

        $profileImage = $profile->profile_image ? url('/api/v1/file?path=' . urlencode($profile->profile_image)) : null;

        $profileSummary = [
            'id'                    => $profile->id,
            'employee_id'           => $profile->employee_id,
            'profile_image'         => $profileImage,
            'profile_status'        => $profile->profile_status ?? 'pending',
            'rejection_reason'      => $profile->rejection_reason,
            'is_profile_completed'  => (bool) $profile->is_profile_completed,
            'updated_at'            => $profile->updated_at ? $profile->updated_at->timestamp : time(),
        ];

        // 3. Attendance Today Status
        $attendanceToday = [];
        try {
            $todayStatusResp = $this->attendanceMobileService->todayStatus($user->id);
            if ($todayStatusResp['status']) {
                $attendanceToday = $todayStatusResp['data'];
            }
        } catch (\Throwable $e) {
            // Fallback empty
        }

        // 4. Leave Summary (balances + summary counts)
        $leaveSummary = [
            'summary' => [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0,
            ],
            'balances' => [],
        ];

        try {
            $statusCounts = LeaveRequest::query()
                ->where('employee_id', $employee->id)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $leaveSummary['summary'] = [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'approved' => (int) ($statusCounts['approved'] ?? 0),
                'rejected' => (int) ($statusCounts['rejected'] ?? 0),
                'total' => (int) $statusCounts->sum(),
            ];

            $year = Carbon::now('Asia/Kolkata')->year;
            $allocation = LeaveAllocation::where('employee_id', $employee->id)
                ->where('year', $year)
                ->latest()
                ->first();

            if ($allocation) {
                $leaveSummary['balances'] = [
                    [
                        'type' => 'total',
                        'label' => 'Total Leave',
                        'allocated' => (float) ($allocation->total_allocated ?? 0),
                        'used' => (float) ($allocation->total_used ?? 0),
                        'remaining' => (float) ($allocation->total_remaining ?? 0),
                    ],
                    [
                        'type' => 'paid',
                        'label' => 'Paid Leave',
                        'allocated' => (float) ($allocation->paid_allocated ?? 0),
                        'used' => (float) ($allocation->paid_used ?? 0),
                        'remaining' => (float) ($allocation->paid_remaining ?? 0),
                    ],
                    [
                        'type' => 'sick',
                        'label' => 'Sick Leave',
                        'allocated' => (float) ($allocation->sick_allocated ?? 0),
                        'used' => (float) ($allocation->sick_used ?? 0),
                        'remaining' => (float) ($allocation->sick_remaining ?? 0),
                    ],
                    [
                        'type' => 'comp_off',
                        'label' => 'Comp Off',
                        'allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                        'used' => (float) ($allocation->comp_off_used ?? 0),
                        'remaining' => (float) ($allocation->comp_off_remaining ?? 0),
                    ],
                    [
                        'type' => 'lwp',
                        'label' => 'Leave Without Pay',
                        'allocated' => 0.0,
                        'used' => (float) ($allocation->lwp_used ?? 0),
                        'remaining' => 0.0,
                    ],
                ];
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        // 5. Latest Announcements (limit 3)
        $latestAnnouncements = [];
        try {
            $announcementsQuery = Announcement::with('creator')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', today());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', today());
                })
                ->latest('created_at');

            // Filter by user target first
            $filtered = $announcementsQuery->get()->filter(function ($item) use ($user) {
                return $this->isUserInTarget($user, $item);
            })->take(3);

            $latestAnnouncements = $filtered->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'type' => $item->type,
                    'priority' => $item->priority,
                    'has_attachment' => !empty($item->attachment),
                    'attachment_url' => $item->attachment ? route('hrms.announcements.attachment', $item->id) : null,
                    'attachment_api_url' => $this->announcementAttachmentUrl($item->id, $item->attachment),
                    'attachment_type' => $this->resolveAttachmentType($item->attachment),
                    'attachment_name' => $item->attachment ? basename($item->attachment) : null,
                    'is_image' => $this->resolveAttachmentType($item->attachment) === 'image',
                    'created_at' => $item->created_at,
                    'created_by' => optional($item->creator)->name ?? 'System',
                    'target_role_id' => $item->target_role_id,
                    'target_department_id' => $item->target_department_id,
                    'target_user_id' => $item->target_user_id,
                    'target_type' => $item->target_type,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'updated_at' => $item->updated_at,
                ];
            })->values()->toArray();
        } catch (\Throwable $e) {
            // Fallback
        }

        // 6. Unread Notification Count
        $unreadNotificationCount = 0;
        try {
            $unreadNotificationCount = Notification::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('is_read', 0)
                      ->orWhereNull('read_at');
                })
                ->count();
        } catch (\Throwable $e) {
            // Fallback
        }

        $exitStatusCard = $this->getExitStatusCard((int) $employee->id);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard bootstrap data fetched successfully.',
            'data' => [
                'user' => $userSummary,
                'employee' => $employeeSummary,
                'profile' => $profileSummary,
                'completion_status' => $profileGateStatus,

                'user_summary' => $userSummary,
                'employee_summary' => $employeeSummary,
                'profile_summary' => $profileSummary,
                'profile_gate_status' => $profileGateStatus,
                'attendance_today' => $attendanceToday,
                'leave_summary' => $leaveSummary,
                'exit_status' => $exitStatusCard,
                'latest_announcements' => $latestAnnouncements,
                'unread_notification_count' => $unreadNotificationCount,
            ]
        ]);
    }

    private function getExitStatusCard(int $employeeId): ?array
    {
        if (! Schema::hasTable('employee_exit_processes')) {
            return null;
        }

        $exit = DB::table('employee_exit_processes')
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->first();

        if (! $exit) {
            return null;
        }

        return [
            'exit_type' => $exit->exit_type,
            'status' => $exit->status,
            'notice_period_days' => $exit->notice_period_days,
            'last_working_day' => $exit->last_working_day ?: $exit->last_working_date,
            'asset_status' => $exit->asset_status ?: $exit->asset_handover_status,
            'fnf_status' => $exit->fnf_status,
            'document_status' => $exit->document_status,
            'handover_status' => $exit->handover_status,
            'fnf_due_date' => property_exists($exit, 'fnf_due_date') ? $exit->fnf_due_date : null,
        ];
    }

    private function buildCompletionStatus(Employee $employee, $profile): array
    {
        $documentCompletion = app(EmployeeDocumentCompletionS::class);

        $isEmployee = method_exists($employee->user, 'isEmployee')
            ? (bool) $employee->user->isEmployee()
            : true;

        $missingProfileFields = $documentCompletion->missingProfileFields($profile, $employee);
        $profileFieldsCompleted = count($missingProfileFields) === 0;

        $documentStatus = $documentCompletion->completion($employee);

        $requiredUploaded = ($documentStatus['uploaded_required_count'] ?? 0) === ($documentStatus['required_count'] ?? 0)
            && ($documentStatus['required_count'] ?? 0) > 0;

        $requiredVerified = ($documentStatus['verified_count'] ?? 0) === ($documentStatus['required_count'] ?? 0)
            && ($documentStatus['required_count'] ?? 0) > 0;

        $canPunchAttendance = ! $isEmployee || (
            $profile->profile_status === 'approved' && $requiredVerified
        );

        $docVerificationStatus = 'missing';

        if (($documentStatus['rejected_count'] ?? 0) > 0) {
            $docVerificationStatus = 'rejected';
        } elseif (($documentStatus['pending_count'] ?? 0) > 0) {
            $docVerificationStatus = 'pending';
        } elseif ($requiredVerified) {
            $docVerificationStatus = 'verified';
        }

        $isProfileCompleted = (bool) $profile->is_profile_completed;

        $mustCompleteProfile = $isEmployee ? (! $isProfileCompleted || $profile->profile_status === 'rejected') : false;
        $attendanceBlocked = $isEmployee ? ! $canPunchAttendance : false;

        $nextRoute = 'dashboard';

        if ($mustCompleteProfile) {
            if (! $profileFieldsCompleted || $profile->profile_status === 'rejected') {
                $nextRoute = 'profile_completion';
            } elseif (! $requiredUploaded) {
                $nextRoute = 'document_completion';
            } else {
                $nextRoute = 'document_completion';
            }
        }

        return [
            'is_profile_completed'         => $isProfileCompleted,
            'profile_verification_status'  => $profile->profile_status ?? 'pending',
            'rejection_reason'             => $profile->rejection_reason,
            'document_verification_status' => $docVerificationStatus,
            'required_documents_verified'  => $requiredVerified,
            'can_punch_attendance'         => $canPunchAttendance,
            'attendance_blocked'           => $attendanceBlocked,
            'next_route'                   => $nextRoute,

            'must_complete_profile'        => $mustCompleteProfile,
            'completion_percentage'        => $documentCompletion->profileCompletionPercentage($profile, $employee),
            'missing_profile_fields'       => $missingProfileFields,
            'document_completion_status'   => $documentStatus,
            'experience_type'              => $profile->experience_type ?? 'fresher',
        ];
    }

    private function isUserInTarget($user, $announcement): bool
    {
        if ($announcement->target_type === 'all') {
            return true;
        }

        if ($announcement->target_type === 'role' && $announcement->target_role_id) {
            if ($user->system_role_id == $announcement->target_role_id) {
                return true;
            }
            $tableName = Schema::hasTable('system_roles') ? 'system_roles' : 'roles';
            $slug = DB::table($tableName)->where('id', $announcement->target_role_id)->value('slug');
            return $slug && $user->hasRole($slug);
        }

        if ($announcement->target_type === 'department' && $announcement->target_department_id) {
            return $user->employee && $user->employee->department_id == $announcement->target_department_id;
        }

        if ($announcement->target_type === 'user' && $announcement->target_user_id) {
            return $user->id == $announcement->target_user_id;
        }

        // Legacy support
        if (in_array($announcement->target_type, ['employee', 'employees', 'admin', 'hr'], true)) {
            $roleSlugs = match ($announcement->target_type) {
                'employee', 'employees' => ['employee'],
                'admin' => ['super_admin', 'admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin'],
                'hr' => ['super_admin', 'admin', 'hr_admin'],
                default => [],
            };
            
            $userRoleSlug = optional($user->role)->slug;
            return in_array($userRoleSlug, $roleSlugs, true) || $user->hasRole($roleSlugs);
        }

        return false;
    }

    private function announcementAttachmentUrl(int $announcementId, ?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return url('/api/v1/announcements/' . $announcementId . '/attachment');
    }

    private function resolveAttachmentType(?string $path): string
    {
        if (!$path) return 'file';

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' => 'file',
            default => 'file',
        };
    }
}
