<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function sendOtp(Request $request, PasswordOtpService $service)
    {
        Log::info('[ForgotPassword] request received', [
            'ip' => (string) $request->ip(),
            'email' => strtolower((string) $request->input('email', '')),
        ]);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', $validator->errors(), null, 422);
        }

        $data = $validator->validated();
        $email = strtolower($data['email']);
        $ip = (string) $request->ip();

        if (! $this->allowOtpRequest($email, $ip)) {
            return $this->apiResponse(false, 'Too many requests. Please try again shortly.', [
                'rate_limit' => ['Too many requests. Please try again shortly.'],
            ], [], 429);
        }

        try {
            $service->sendOtp($email, $ip, (string) $request->userAgent());
        } catch (\Throwable $e) {
            Log::error('API password reset OTP failed: '.$e->getMessage());
        }

        return $this->apiResponse(true, 'If this email is registered, an OTP has been sent.', null, []);
    }

    public function verifyOtp(Request $request, PasswordOtpService $service)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', $validator->errors(), null, 422);
        }

        $data = $validator->validated();

        $result = $service->verifyOtp(strtolower($data['email']), $data['otp']);
        if (! ($result['success'] ?? false)) {
            return $this->apiResponse(false, (string) ($result['message'] ?? 'Invalid or expired OTP.'), [
                'otp' => [(string) ($result['message'] ?? 'Invalid or expired OTP.')],
            ], [], 422);
        }

        return $this->apiResponse(true, 'OTP verified successfully.', null, []);
    }

    public function reset(Request $request, PasswordOtpService $service)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', $validator->errors(), null, 422);
        }

        $data = $validator->validated();

        $result = $service->resetPassword(strtolower($data['email']), $data['otp'], $data['password']);
        if (! ($result['success'] ?? false)) {
            return $this->apiResponse(false, (string) ($result['message'] ?? 'Unable to reset password. Please request a new OTP.'), null, [], 422);
        }

        return $this->apiResponse(true, 'Password reset successfully. Please login.', null, []);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', $validator->errors(), null, 422);
        }

        $data = $validator->validated();

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return $this->apiResponse(false, 'Current password is incorrect.', ['current_password' => ['Current password is incorrect.']], null, 422);
        }

        $userUpdate = [
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $userUpdate['must_change_password'] = false;
        }

        DB::table('users')->where('id', $user->id)->update($userUpdate);

        return $this->apiResponse(true, 'Password changed successfully.');
    }

    private function apiResponse(bool $success, string $message, $errors = null, $data = null, int $status = 200)
    {
        return response()->json([
            'success' => $success,
            'status' => $success,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }

    private function allowOtpRequest(string $email, string $ip): bool
    {
        $emailKey = 'forgot-password:email:' . sha1($email);
        $ipKey = 'forgot-password:ip:' . sha1($ip);

        $emailAllowed = ! RateLimiter::tooManyAttempts($emailKey, 5);
        $ipAllowed = ! RateLimiter::tooManyAttempts($ipKey, 20);

        if (! $emailAllowed || ! $ipAllowed) {
            return false;
        }

        RateLimiter::hit($emailKey, 60);
        RateLimiter::hit($ipKey, 60);

        return true;
    }
}
