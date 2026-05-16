<?php

namespace App\Http\Controllers\Api\V1\HRMS\Announcement;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Announcement\AnnouncementM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementApiC extends Controller
{
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
                    'attachment' => $item->attachment,
                    'attachment_url' => $this->announcementAttachmentUrl($item->attachment),
                    'attachment_type' => $this->resolveAttachmentType($item->attachment),
                    'attachment_name' => $item->attachment ? basename($item->attachment) : null,
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

        if (
            ! (bool) $announcement->is_active ||
            ($announcement->start_date && $announcement->start_date->isAfter(today())) ||
            ($announcement->end_date && $announcement->end_date->isBefore(today())) ||
            !$this->isUserInTarget($user, $announcement)
        ) {
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
                'attachment' => $announcement->attachment,
                'attachment_url' => $this->announcementAttachmentUrl($announcement->attachment),
                'attachment_type' => $this->resolveAttachmentType($announcement->attachment),
                'attachment_name' => $announcement->attachment ? basename($announcement->attachment) : null,
                'created_at' => $announcement->created_at,
                'updated_at' => $announcement->updated_at,
                'created_by' => optional($announcement->creator)->name ?? 'System',
            ]
        ]);
    }

    private function isUserInTarget($user, $announcement): bool
    {
        if ($announcement->target_type === 'all') {
            return true;
        }

        if ($announcement->target_type === 'role' && $announcement->target_role_id) {
            return $user->system_role_id == $announcement->target_role_id;
        }

        if ($announcement->target_type === 'department' && $announcement->target_department_id) {
            return $user->employee && $user->employee->department_id == $announcement->target_department_id;
        }

        if ($announcement->target_type === 'user' && $announcement->target_user_id) {
            return $user->id == $announcement->target_user_id;
        }

        // Legacy support
        if (in_array($announcement->target_type, ['employee', 'admin', 'hr'])) {
            $roleSlugs = match ($announcement->target_type) {
                'employee' => ['employee'],
                'admin' => ['super_admin', 'admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin'],
                'hr' => ['super_admin', 'admin', 'hr_admin'],
                default => [],
            };
            
            $userRoleSlug = optional($user->role)->slug;
            return in_array($userRoleSlug, $roleSlugs);
        }

        return false;
    }

    private function announcementAttachmentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url('storage/' . ltrim($path, '/'));
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
