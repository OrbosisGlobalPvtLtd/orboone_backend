<?php

namespace App\Http\Controllers\Web\HRMS\Announcement;

use App\Http\Controllers\Controller;
use App\Models\Core\UserM;
use App\Models\HRMS\Announcement\AnnouncementM;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnouncementsC extends Controller
{
    public function __construct(
        private HrmsStoragePathS $paths,
        private HrmsFileResolverS $resolver
    ) {
    }

    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('announcements.view') && !Auth::user()->hasPermission('announcements.manage')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

        $permissions = [
            'create' => $user->hasPermission('announcements.create') || $user->hasPermission('announcements.manage'),
            'edit' => $user->hasPermission('announcements.edit') || $user->hasPermission('announcements.manage'),
            'update' => $user->hasPermission('announcements.edit') || $user->hasPermission('announcements.manage'),
            'delete' => $user->hasPermission('announcements.delete') || $user->hasPermission('announcements.manage'),
            'view' => $user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage'),
            'print' => $user->hasPermission('announcements.print') || $user->hasPermission('announcements.manage'),
            'export' => $user->hasPermission('announcements.export') || $user->hasPermission('announcements.manage'),
            'toggle' => $user->hasPermission('announcements.publish') || $user->hasPermission('announcements.manage'),

            'canCreate' => $user->hasPermission('announcements.create') || $user->hasPermission('announcements.manage'),
            'canEdit' => $user->hasPermission('announcements.edit') || $user->hasPermission('announcements.manage'),
            'canUpdate' => $user->hasPermission('announcements.edit') || $user->hasPermission('announcements.manage'),
            'canDelete' => $user->hasPermission('announcements.delete') || $user->hasPermission('announcements.manage'),
            'canView' => $user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage'),
            'canPrint' => $user->hasPermission('announcements.print') || $user->hasPermission('announcements.manage'),
            'canExport' => $user->hasPermission('announcements.export') || $user->hasPermission('announcements.manage'),
            'canToggle' => $user->hasPermission('announcements.publish') || $user->hasPermission('announcements.manage'),
        ];

        if ((int) $request->input('ajax_table') === 1) {
            try {
                $query = AnnouncementM::query()->with('creator');
                $recordsTotal = AnnouncementM::count();

                if ($request->filled('type')) {
                    $query->where('type', $request->type);
                }

                if ($request->filled('priority')) {
                    $query->where('priority', $request->priority);
                }

                if ($request->filled('target_type')) {
                    $query->where('target_type', $request->target_type);
                }

                if ($request->filled('status')) {
                    $query->where('is_active', $request->status === 'active');
                }

                $search = $request->input('search.value');

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%")
                            ->orWhere('priority', 'like', "%{$search}%")
                            ->orWhere('target_type', 'like', "%{$search}%");
                    });
                }

                $recordsFiltered = (clone $query)->count();

                $start = max((int) $request->input('start', 0), 0);
                $length = (int) $request->input('length', 10);
                $length = $length > 0 ? $length : 10;

                $rows = $query
                    ->latest('id')
                    ->skip($start)
                    ->take($length)
                    ->get();

                return response()->json([
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $rows->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => e($item->title),
                            'description' => Str::limit(strip_tags((string) $item->description), 80),
                            'type' => $item->type,
                            'priority' => $item->priority,
                            'target_type' => $item->target_type,
                            'target_role_id' => $item->target_role_id ?? null,
                            'target_department_id' => $item->target_department_id ?? null,
                            'target_user_id' => $item->target_user_id ?? null,
                            'is_active' => (bool) $item->is_active,
                            'created_by' => optional($item->creator)->name ?? 'System',
                            'created_at' => optional($item->created_at)->format('d M Y, h:i A'),
                            'attachment_url' => $item->attachment ? route('hrms.announcements.attachment', $item->id) : null,
                            'edit_data' => [
                                'id' => $item->id,
                                'title' => $item->title,
                                'description' => $item->description,
                                'type' => $item->type,
                                'priority' => $item->priority,
                                'target_type' => $item->target_type,
                                'target_role_id' => $item->target_role_id ?? null,
                                'target_department_id' => $item->target_department_id ?? null,
                                'target_user_id' => $item->target_user_id ?? null,
                                'start_date' => $item->start_date ? optional($item->start_date)->format('Y-m-d') : null,
                                'end_date' => $item->end_date ? optional($item->end_date)->format('Y-m-d') : null,
                                'attachment' => $item->attachment,
                                'is_active' => (bool) $item->is_active,
                            ],
                        ];
                    })->values(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Announcement DataTable Ajax Error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return response()->json([
                    'draw' => (int) $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $stats = [
            'total' => AnnouncementM::count(),
            'active' => AnnouncementM::where('is_active', true)->count(),
            'urgent' => AnnouncementM::where('priority', 'urgent')->count(),
            'today' => AnnouncementM::whereDate('created_at', today())->count(),
        ];

        $roles = collect();
        if (Schema::hasTable('system_roles')) {
            $roles = DB::table('system_roles')
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();
        } elseif (Schema::hasTable('roles')) {
            $roles = DB::table('roles')
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();
        }

        $departments = collect();
        if (Schema::hasTable('departments')) {
            $departments = DB::table('departments')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        $users = UserM::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('hrms.announcements.index', compact(
            'stats',
            'permissions',
            'roles',
            'departments',
            'users'
        ));
    }

    public function store(Request $request, NotificationS $notificationS)
    {
        if (!Auth::user()->hasPermission('announcements.create') && !Auth::user()->hasPermission('announcements.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $this->validateAttachment($request->file('attachment'));
            $data['attachment'] = $request->file('attachment')->store(
                $this->paths->announcement((int) now()->format('Y'), (int) now()->format('m'), 'attachments'),
                'private'
            );
        }

        $data['created_by_user_id'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($data, $notificationS) {
            $announcement = AnnouncementM::create($data);

            if ($announcement->is_active) {
                $this->sendAnnouncementNotification($announcement, $notificationS);
            }
        });

        return back()->with('success', 'Announcement published successfully.');
    }

    public function update(Request $request, AnnouncementM $announcement, NotificationS $notificationS)
    {
        if (!Auth::user()->hasPermission('announcements.edit') && !Auth::user()->hasPermission('announcements.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->validated($request, true);

        if ($request->hasFile('attachment')) {
            $this->validateAttachment($request->file('attachment'));
            if ($announcement->attachment) {
                Storage::disk('private')->delete($announcement->attachment);
            }

            $data['attachment'] = $request->file('attachment')->store(
                $this->paths->announcement((int) now()->format('Y'), (int) now()->format('m'), 'attachments'),
                'private'
            );
        }

        $data['is_active'] = $request->boolean('is_active');

        $wasActive = (bool) $announcement->is_active;
        $announcement->update($data);

        if (! $wasActive && (bool) $announcement->is_active) {
            $this->sendAnnouncementNotification($announcement->fresh(), $notificationS);
        }

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function destroy(AnnouncementM $announcement)
    {
        if (!Auth::user()->hasPermission('announcements.delete') && !Auth::user()->hasPermission('announcements.manage')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if ($announcement->attachment) {
            Storage::disk('private')->delete($announcement->attachment);
        }

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    public function toggleStatus(AnnouncementM $announcement, NotificationS $notificationS)
    {
        if (!Auth::user()->hasPermission('announcements.publish') && !Auth::user()->hasPermission('announcements.manage')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $willPublish = ! (bool) $announcement->is_active;
        $announcement->update(['is_active' => $willPublish]);

        if ($willPublish) {
            $this->sendAnnouncementNotification($announcement->fresh(), $notificationS);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement status updated successfully.',
            'is_active' => (bool) $announcement->is_active,
        ]);
    }

    public function print()
    {
        if (!Auth::user()->hasPermission('announcements.print') && !Auth::user()->hasPermission('announcements.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $announcements = AnnouncementM::with('creator')->latest('id')->get();

        return view('hrms.announcements.print', compact('announcements'));
    }

    public function employeeIndex(Request $request)
    {
        if (!Auth::user()->hasPermission('employee.announcements.view')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();
        $employee = $user->employee;
        $departmentId = $employee ? $employee->department_id : null;
        
        $roleIds = $user->roles()->pluck('roles.id')->toArray();
        if ($user->system_role_id) {
            $roleIds[] = $user->system_role_id;
        }
        $roleIds = array_unique(array_filter($roleIds));

        $announcements = AnnouncementM::with('creator')
            ->where('is_active', true)
            ->where(function ($q) use ($user, $departmentId, $roleIds) {
                $q->whereIn('target_type', ['all', 'employee', 'employees']);
                
                if ($departmentId) {
                    $q->orWhere(function ($sq) use ($departmentId) {
                        $sq->where('target_type', 'department')
                           ->where('target_department_id', $departmentId);
                    });
                }
                
                if (!empty($roleIds)) {
                    $q->orWhere(function ($sq) use ($roleIds) {
                        $sq->where('target_type', 'role')
                           ->whereIn('target_role_id', $roleIds);
                    });
                }
                
                $q->orWhere(function ($sq) use ($user) {
                    $sq->where('target_type', 'user')
                       ->where('target_user_id', $user->id);
                });
            })
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', today());
            })
            ->latest('id')
            ->paginate(12);

        return view('hrms.announcements.employee_index', compact('announcements'));
    }

    public function employeeShow(AnnouncementM $announcement)
    {
        if (!Auth::user()->hasPermission('employee.announcements.detail') && !Auth::user()->hasPermission('employee.announcements.view')) {
            abort(403, 'Unauthorized access.');
        }

        if (
            ! (bool) $announcement->is_active ||
            ($announcement->start_date && $announcement->start_date->startOfDay()->isAfter(today())) ||
            ($announcement->end_date && $announcement->end_date->endOfDay()->isBefore(today())) ||
            ! $this->isUserInTarget(Auth::user(), $announcement)
        ) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        return view('hrms.announcements.show', compact('announcement'));
    }

    public function show(AnnouncementM $announcement)
    {
        if (!Auth::user()->hasPermission('announcements.view') && !Auth::user()->hasPermission('announcements.manage')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();
        $accesses = collect();
        if (Schema::hasTable('role_menu_access')) {
            $roleIds = $user->roles()->pluck('roles.id')->toArray();
            if ($user->system_role_id) {
                $roleIds[] = $user->system_role_id;
            }
            $accesses = DB::table('role_menu_access')
                ->whereIn('role_id', array_unique($roleIds))
                ->get();
        }

        return view('hrms.announcements.show', compact('announcement', 'accesses'));
    }

    public function attachment(AnnouncementM $announcement): BinaryFileResponse
    {
        $user = auth()->user();
        $allowed = $this->userCanAccessAnnouncement($user, $announcement);

        Log::info('Announcement attachment access check', [
            'announcement_id' => $announcement->id,
            'user_id' => auth()->id(),
            'target_type' => $announcement->target_type,
            'is_admin' => $user ? ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage')) : false,
            'is_employee' => $user ? $user->isEmployee() : false,
            'allowed' => $allowed
        ]);

        abort_unless($allowed, 403, 'Unauthorized access.');
        abort_if(empty($announcement->attachment), 404, 'Attachment not available.');

        $resolved = $this->resolver->resolve($announcement->attachment);
        abort_if(! $resolved, 404, 'Attachment not available.');

        $mime = mime_content_type($resolved['absolute']) ?: 'application/octet-stream';

        return response()->file($resolved['absolute'], [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($resolved['absolute']) . '"',
        ]);
    }

    private function validateAttachment($file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $allowedMimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        if (!isset($allowedMimes[$ext]) || $mime !== $allowedMimes[$ext]) {
            throw new \Exception('Invalid attachment MIME content type.');
        }
    }

    private function validated(Request $request, bool $update = false): array
    {
        $allowedTargetTypes = ['all', 'employee', 'admin', 'hr'];

        if (Schema::hasColumn('announcements', 'target_role_id')) {
            $allowedTargetTypes[] = 'role';
        }

        if (Schema::hasColumn('announcements', 'target_department_id')) {
            $allowedTargetTypes[] = 'department';
        }

        if (Schema::hasColumn('announcements', 'target_user_id')) {
            $allowedTargetTypes[] = 'user';
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:general,holiday,emergency,policy,meeting'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'target_type' => ['required', 'in:' . implode(',', array_unique($allowedTargetTypes))],
            'target_role_id' => ['nullable', 'integer'],
            'target_department_id' => ['nullable', 'integer'],
            'target_user_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
            'is_active' => ['nullable'],
        ]);
    }

    private function sendAnnouncementNotification(AnnouncementM $announcement, NotificationS $notificationS): void
    {
        $users = $this->targetUsers($announcement);

        foreach ($users as $user) {
            try {
                $routeName = ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage'))
                    ? 'announcements.index'
                    : 'employee.announcements.index';

                $notificationS->createNotification(
                    userId: $user->id,
                    roleId: $user->system_role_id ?? null,
                    title: $announcement->title,
                    message: Str::limit(strip_tags((string) $announcement->description), 130),
                    type: 'announcement_published',
                    routeName: $routeName,
                    routeParams: ['announcement_id' => $announcement->id],
                    data: [
                        'announcement_id' => $announcement->id,
                        'announcement_type' => $announcement->type,
                        'priority' => $announcement->priority,
                        'attachment_url' => $announcement->attachment ? route('hrms.announcements.attachment', $announcement->id) : '',
                        'web_attachment_url' => $announcement->attachment ? route('hrms.announcements.attachment', $announcement->id) : '',
                        'api_attachment_url' => $announcement->attachment ? url('/api/v1/announcements/' . $announcement->id . '/attachment') : '',
                        'attachment_type' => $announcement->attachment ? $this->attachmentType($announcement->attachment) : '',
                        'attachment_name' => $announcement->attachment ? basename($announcement->attachment) : '',
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Announcement notification failed', [
                    'announcement_id' => $announcement->id,
                    'user_id' => $user->id ?? null,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function targetUsers(AnnouncementM $announcement)
    {
        $query = UserM::query()->whereNotNull('id');

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        }

        $targetType = $announcement->target_type ?: 'all';

        if ($targetType === 'all') {
            return $query->get();
        }

        if ($targetType === 'role' && Schema::hasColumn('announcements', 'target_role_id') && $announcement->target_role_id) {
            return $query->where('system_role_id', $announcement->target_role_id)->get();
        }

        if ($targetType === 'user' && Schema::hasColumn('announcements', 'target_user_id') && $announcement->target_user_id) {
            return $query->where('id', $announcement->target_user_id)->get();
        }

        if ($targetType === 'department' && Schema::hasColumn('announcements', 'target_department_id') && $announcement->target_department_id) {
            return $query->whereHas('employee', function ($q) use ($announcement) {
                $q->where('department_id', $announcement->target_department_id);
            })->get();
        }

        $roleSlugs = match ($targetType) {
            'employee' => ['employee'],
            'admin' => ['super_admin', 'admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin'],
            'hr' => ['super_admin', 'admin', 'hr_admin'],
            default => [],
        };

        if (!empty($roleSlugs)) {
            return $this->usersForRoleSlugs($query, $roleSlugs)->get();
        }

        return collect();
    }

    private function usersForRoleSlugs($query, array $roleSlugs)
    {
        $roleIds = collect();

        if (Schema::hasTable('roles')) {
            $roleIds = DB::table('roles')
                ->where(function ($q) use ($roleSlugs) {
                    if (Schema::hasColumn('roles', 'slug')) {
                        $q->orWhereIn('slug', $roleSlugs);
                    }

                    if (Schema::hasColumn('roles', 'name')) {
                        $q->orWhereIn('name', $roleSlugs)
                            ->orWhereIn('name', array_map(fn ($role) => str_replace('_', ' ', $role), $roleSlugs))
                            ->orWhereIn('name', array_map(fn ($role) => ucwords(str_replace('_', ' ', $role)), $roleSlugs));
                    }
                })
                ->pluck('id');
        } elseif (Schema::hasTable('system_roles')) {
            $roleIds = DB::table('system_roles')
                ->whereIn('slug', $roleSlugs)
                ->pluck('id');
        }

        if ($roleIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($roleIds) {
            if (Schema::hasColumn('users', 'system_role_id')) {
                $q->orWhereIn('system_role_id', $roleIds);
            }

            if (Schema::hasTable('user_roles')) {
                $q->orWhereExists(function ($sub) use ($roleIds) {
                    $sub->select(DB::raw(1))
                        ->from('user_roles')
                        ->whereColumn('user_roles.user_id', 'users.id')
                        ->whereIn('user_roles.role_id', $roleIds);
                });
            }
        });
    }

    private function attachmentType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'image';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        return 'document';
    }

    private function userCanAccessAnnouncement($user, AnnouncementM $announcement): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Super Admin / Admin / HR Admin with permission can always view/manage any announcement and its attachments,
        // regardless of active state, date windows, or targeting.
        if ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage')) {
            return true;
        }

        // 2. Otherwise (regular employees):
        if (!(bool) $announcement->is_active) {
            return false;
        }

        if (
            ($announcement->start_date && $announcement->start_date->startOfDay()->isAfter(today())) ||
            ($announcement->end_date && $announcement->end_date->endOfDay()->isBefore(today()))
        ) {
            return false;
        }

        return $this->isUserInTarget($user, $announcement);
    }

    private function isUserInTarget($user, AnnouncementM $announcement): bool
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
}
