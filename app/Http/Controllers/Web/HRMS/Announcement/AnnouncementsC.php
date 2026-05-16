<?php

namespace App\Http\Controllers\Web\HRMS\Announcement;

use App\Http\Controllers\Controller;
use App\Models\Core\UserM;
use App\Models\HRMS\Announcement\AnnouncementM;
use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementsC extends Controller
{
    public function index(Request $request)
    {
        $permissions = [
            'create' => true,
            'edit' => true,
            'update' => true,
            'delete' => true,
            'view' => true,
            'print' => true,
            'export' => true,
            'toggle' => true,

            'canCreate' => true,
            'canEdit' => true,
            'canUpdate' => true,
            'canDelete' => true,
            'canView' => true,
            'canPrint' => true,
            'canExport' => true,
            'canToggle' => true,
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
                            'attachment_url' => $item->attachment ? asset('storage/' . $item->attachment) : null,
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
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('announcements', 'public');
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

    public function update(Request $request, AnnouncementM $announcement)
    {
        $data = $this->validated($request, true);

        if ($request->hasFile('attachment')) {
            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);
            }

            $data['attachment'] = $request->file('attachment')->store('announcements', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');

        $announcement->update($data);

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function destroy(AnnouncementM $announcement)
    {
        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    public function toggleStatus(AnnouncementM $announcement)
    {
        $announcement->update([
            'is_active' => ! (bool) $announcement->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement status updated successfully.',
            'is_active' => (bool) $announcement->is_active,
        ]);
    }

    public function print()
    {
        $announcements = AnnouncementM::with('creator')->latest('id')->get();

        return view('hrms.announcements.print', compact('announcements'));
    }

    public function employeeIndex(Request $request)
    {
        $announcements = AnnouncementM::with('creator')
            ->where('is_active', true)
            ->whereIn('target_type', ['all', 'employee'])
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
        if (
            ! (bool) $announcement->is_active ||
            ! in_array($announcement->target_type, ['all', 'employee'], true) ||
            ($announcement->start_date && $announcement->start_date->isAfter(today())) ||
            ($announcement->end_date && $announcement->end_date->isBefore(today()))
        ) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        return view('hrms.announcements.show', compact('announcement'));
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
                $notificationS->createNotification(
                    userId: $user->id,
                    roleId: $user->system_role_id ?? null,
                    title: $announcement->title,
                    message: Str::limit(strip_tags((string) $announcement->description), 130),
                    type: 'announcement',
                    routeName: 'announcements.index',
                    routeParams: [],
                    data: [
                        'announcement_id' => $announcement->id,
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

        if (Schema::hasColumn('users', 'system_role_id') && Schema::hasTable('system_roles')) {
            $roleSlugs = match ($targetType) {
                'employee' => ['employee'],
                'admin' => ['super_admin', 'admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin'],
                'hr' => ['super_admin', 'admin', 'hr_admin'],
                default => [],
            };

            if (!empty($roleSlugs)) {
                $roleIds = DB::table('system_roles')
                    ->whereIn('slug', $roleSlugs)
                    ->pluck('id');

                return $query->whereIn('system_role_id', $roleIds)->get();
            }
        }

        return collect();
    }
}
