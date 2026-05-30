<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LegacyPayrollApiRetiredTest extends TestCase
{
    use DatabaseTransactions;

    public function test_legacy_payroll_api_returns_410_gone(): void
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Legacy Payroll API User',
            'email' => 'legacy_payroll_api_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/payroll/dashboard');

        $response->assertStatus(410)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Legacy payroll is retired. Use Enterprise Payroll APIs.');
    }
}

