<?php

namespace App\Services\HRMS\Announcement;

use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\HRMS\Department\DepartmentM as Department;
use Illuminate\Support\Collection;

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
        return Department::all();
    }

    public function create(array $payload): Announcement
    {
        return Announcement::create($payload);
    }

    public function updateOwned(int $id, int $creatorId, array $payload): int
    {
        return Announcement::where('id', $id)
            ->where('created_by', $creatorId)
            ->update($payload);
    }

    public function deleteOwned(int $id, int $creatorId): int
    {
        return Announcement::where('id', $id)
            ->where('created_by', $creatorId)
            ->delete();
    }

    public function storeAttachment($file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('attachments', 'public');
    }
}
