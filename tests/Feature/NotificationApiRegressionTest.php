<?php

namespace Tests\Feature;

use App\Models\Core\NotificationM;
use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $this->user = UserM::create([
            'name' => 'Notification API Employee',
            'email' => 'notification_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->user->roles()->sync([$role->id]);
    }

    public function test_unread_count_mark_read_and_deep_link_payload(): void
    {
        $notification = NotificationM::create([
            'user_id' => $this->user->id,
            'title' => 'Leave Approved',
            'message' => 'Your leave was approved',
            'type' => 'leave_approved',
            'route_name' => 'leave.requests.show',
            'route_params' => ['id' => 101],
            'data' => ['route_name' => 'leave.requests.show', 'route_params' => ['id' => 101]],
            'is_read' => 0,
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);

        $this->postJson('/api/v1/notifications/' . $notification->id . '/read')
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->getJson('/api/v1/notifications/' . $notification->id)
            ->assertOk()
            ->assertJsonPath('data.route_name', 'leave.requests.show')
            ->assertJsonPath('data.route_params.id', 101);
    }
}

