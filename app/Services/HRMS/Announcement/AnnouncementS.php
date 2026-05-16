<?php

namespace App\Services\HRMS\Announcement;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\HRMS\Department\DepartmentM as Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnnouncementS
{
    public function paginate(int $count = 10)
    {
        return Announcement::with(['creator', 'department'])
            ->latest()
            ->paginate($count);
    }

    public function departments(): Collection
    {
        return Schema::hasTable('departments')
            ? Department::query()->orderBy('name')->get()
            : collect();
    }

    public function create(array $payload): Announcement
    {
        return Announcement::create($payload);
    }

    public function updateOwned(int $id, int $creatorId, array $payload): int
    {
        return Announcement::where('id', $id)
            ->where(function ($q) use ($creatorId) {
                $q->where('created_by', $creatorId);

                if (Schema::hasColumn('announcements', 'created_by_user_id')) {
                    $q->orWhere('created_by_user_id', $creatorId);
                }
            })
            ->update($payload);
    }

    public function deleteOwned(int $id, int $creatorId): int
    {
        return Announcement::where('id', $id)
            ->where(function ($q) use ($creatorId) {
                $q->where('created_by', $creatorId);

                if (Schema::hasColumn('announcements', 'created_by_user_id')) {
                    $q->orWhere('created_by_user_id', $creatorId);
                }
            })
            ->delete();
    }

    public function storeAttachment($file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('attachments', 'public');
    }

    /**
     * Get target users for announcement notification.
     *
     * target_type:
     * - all
     * - employee
     * - admin
     * - hr
     */
    public function targetUsers(string $targetType = 'all'): Collection
    {
        $targetType = strtolower(trim($targetType ?: 'all'));

        $query = User::query()
            ->where(function ($q) {
                if (Schema::hasColumn('users', 'is_active')) {
                    $q->where('is_active', 1);
                }
            });

        if ($targetType === 'all') {
            return $query->get();
        }

        $roleSlugs = match ($targetType) {
            'employee' => ['employee'],
            'admin' => [
                'super_admin',
                'admin',
                'hr_admin',
                'finance_admin',
                'project_admin',
                'operations_admin',
                'custom_admin',
            ],
            'hr' => ['hr_admin', 'super_admin'],
            default => [],
        };

        if (empty($roleSlugs)) {
            return collect();
        }

        return $this->usersByRoleSlugs($query, $roleSlugs);
    }

    private function usersByRoleSlugs($baseQuery, array $roleSlugs): Collection
    {
        if (! Schema::hasTable('roles')) {
            return collect();
        }

        $roleIds = DB::table('roles')
            ->whereIn('slug', $roleSlugs)
            ->pluck('id')
            ->filter()
            ->values();

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $query = clone $baseQuery;

        if (Schema::hasTable('user_roles')) {
            return $query
                ->whereExists(function ($sub) use ($roleIds) {
                    $sub->select(DB::raw(1))
                        ->from('user_roles')
                        ->whereColumn('user_roles.user_id', 'users.id')
                        ->whereIn('user_roles.role_id', $roleIds);
                })
                ->get();
        }

        if (Schema::hasColumn('users', 'role_id')) {
            return $query->whereIn('role_id', $roleIds)->get();
        }

        if (Schema::hasColumn('users', 'system_role_id')) {
            return $query->whereIn('system_role_id', $roleIds)->get();
        }

        return collect();
    }
}
