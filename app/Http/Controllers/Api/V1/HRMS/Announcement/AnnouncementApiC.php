<?php

namespace App\Http\Controllers\Api\V1\HRMS\Announcement;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Announcement\AnnouncementM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnouncementApiC extends Controller
{
    public function __construct(private HrmsFileResolverS $resolver)
    {
    }

    public function index()
    {
        $user = Auth::user();
        
        // Use the same logic as employeeIndex to filter announcements for the API
        $query = AnnouncementM::with('creator')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', today());
            });

        $announcements = $query->get()->filter(function ($item) use ($user) {
            return $this->isUserInTarget($user, $item);
        });

        return response()->json([
            'success' => true,
            'data' => $announcements->map(function ($item) {
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
            })->values()
        ]);
    }

    public function show(AnnouncementM $announcement)
    {
        $user = Auth::user();

        $allowed = false;
        if ($user) {
            if ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage')) {
                $allowed = true;
            } elseif (
                (bool) $announcement->is_active &&
                (! $announcement->end_date || $announcement->end_date->format('Y-m-d') >= today()->format('Y-m-d')) &&
                $this->isUserInTarget($user, $announcement)
            ) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            Log::error('Announcement API access denied', [
                'announcement_id' => $announcement->id,
                'user_id' => $user ? $user->id : null,
                'is_active' => (bool) $announcement->is_active,
                'start_date' => $announcement->start_date ? $announcement->start_date->toDateString() : null,
                'end_date' => $announcement->end_date ? $announcement->end_date->toDateString() : null,
                'today' => today()->toDateString(),
                'is_target' => $user ? $this->isUserInTarget($user, $announcement) : false,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this announcement or it is inactive.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => $announcement->description,
                'type' => $announcement->type,
                'priority' => $announcement->priority,
                'target_type' => $announcement->target_type,
                'target_role_id' => $announcement->target_role_id,
                'target_department_id' => $announcement->target_department_id,
                'target_user_id' => $announcement->target_user_id,
                'start_date' => $announcement->start_date,
                'end_date' => $announcement->end_date,
                'has_attachment' => !empty($announcement->attachment),
                'attachment_url' => $announcement->attachment ? route('hrms.announcements.attachment', $announcement->id) : null,
                'attachment_api_url' => $this->announcementAttachmentUrl($announcement->id, $announcement->attachment),
                'attachment_type' => $this->resolveAttachmentType($announcement->attachment),
                'attachment_name' => $announcement->attachment ? basename($announcement->attachment) : null,
                'is_image' => $this->resolveAttachmentType($announcement->attachment) === 'image',
                'created_at' => $announcement->created_at,
                'updated_at' => $announcement->updated_at,
                'created_by' => optional($announcement->creator)->name ?? 'System',
            ]
        ]);
    }

    public function attachment(AnnouncementM $announcement): BinaryFileResponse
    {
        $user = Auth::user();

        $allowed = false;
        if ($user) {
            if ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage')) {
                $allowed = true;
            } elseif (
                (bool) $announcement->is_active &&
                (! $announcement->end_date || $announcement->end_date->format('Y-m-d') >= today()->format('Y-m-d')) &&
                $this->isUserInTarget($user, $announcement)
            ) {
                $allowed = true;
            }
        }

        Log::info('Announcement attachment access check', [
            'announcement_id' => $announcement->id,
            'user_id' => auth()->id(),
            'target_type' => $announcement->target_type,
            'is_admin' => $user ? ($user->hasPermission('announcements.view') || $user->hasPermission('announcements.manage')) : false,
            'is_employee' => $user ? $user->isEmployee() : false,
            'allowed' => $allowed,
            'start_date' => $announcement->start_date ? $announcement->start_date->toDateString() : null,
            'end_date' => $announcement->end_date ? $announcement->end_date->toDateString() : null,
            'today' => today()->toDateString(),
        ]);

        if (! $allowed) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        if (empty($announcement->attachment)) {
            abort(404, 'Attachment not available.');
        }

        $resolved = $this->resolver->resolve($announcement->attachment);

        if (! $resolved) {
            abort(404, 'Attachment not available.');
        }

        $mime = mime_content_type($resolved['absolute']) ?: 'application/octet-stream';

        return response()->file($resolved['absolute'], [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($resolved['absolute']) . '"',
        ]);
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
