<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);

        $this->user = UserM::create([
            'name' => 'Forgot Password Test User',
            'email' => 'fp_test_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
    }

    public function test_web_forgot_password_rejects_unregistered_email(): void
    {
        $response = $this->post('/forgot-password/send-otp', [
            'email' => 'non_existent_email@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'This email is not registered.',
            session('errors')->first('email')
        );
    }

    public function test_web_forgot_password_accepts_registered_email(): void
    {
        $response = $this->post('/forgot-password/send-otp', [
            'email' => $this->user->email,
        ]);

        $response->assertRedirect(route('password.otp.form'));
        $response->assertSessionHas('success', 'An OTP has been sent to your email.');
    }

    public function test_api_forgot_password_rejects_unregistered_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password/send-otp', [
            'email' => 'non_existent_email@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This email is not registered.');
    }

    public function test_api_forgot_password_accepts_registered_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password/send-otp', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'An OTP has been sent to your email.');
    }
}
