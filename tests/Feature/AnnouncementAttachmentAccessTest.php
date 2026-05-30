<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Announcement\AnnouncementM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementAttachmentAccessTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $this->user = UserM::create([
            'name' => 'Announcement API Employee',
            'email' => 'announcement_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->user->roles()->sync([$role->id]);
    }

    public function test_image_attachment_access_for_valid_user(): void
    {
        $path = 'hrms/announcements/2026/05/test-image.jpg';
        $absolute = storage_path('app/private/' . $path);
        File::ensureDirectoryExists(dirname($absolute));
        File::put($absolute, 'fake-image');

        $announcement = AnnouncementM::create([
            'title' => 'Image Announcement',
            'description' => 'Image attachment',
            'type' => 'general',
            'priority' => 'medium',
            'target_type' => 'all',
            'is_active' => 1,
            'attachment' => $path,
            'created_by_user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->get('/api/v1/announcements/' . $announcement->id . '/attachment');
        $response->assertOk();
    }

    public function test_pdf_attachment_access_for_valid_user(): void
    {
        $path = 'hrms/announcements/2026/05/test-file.pdf';
        $absolute = storage_path('app/private/' . $path);
        File::ensureDirectoryExists(dirname($absolute));
        File::put($absolute, '%PDF-1.4 fake');

        $announcement = AnnouncementM::create([
            'title' => 'PDF Announcement',
            'description' => 'PDF attachment',
            'type' => 'general',
            'priority' => 'medium',
            'target_type' => 'all',
            'is_active' => 1,
            'attachment' => $path,
            'created_by_user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->get('/api/v1/announcements/' . $announcement->id . '/attachment');
        $response->assertOk();
    }
}

