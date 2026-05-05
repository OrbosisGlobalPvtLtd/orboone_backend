<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function sendOtp(Request $request, PasswordOtpService $service)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', $validator->errors(), null, 422);
        }

        $data = $validator->validated();

        try {
            $service->sendOtp(strtolower($data['email']));
        } catch (\Throwable $e) {
            Log::error('API password reset OTP failed: '.$e->getMessage());
        }

        return $this->apiResponse(true, 'If this email exists, an OTP has been sent.');
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

        if (! $service->verifyOtp(strtolower($data['email']), $data['otp'])) {
            return $this->apiResponse(false, 'Invalid or expired OTP.', ['otp' => ['Invalid or expired OTP.']], null, 422);
        }

        return $this->apiResponse(true, 'OTP verified successfully.');
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

        if (! $service->resetPassword(strtolower($data['email']), $data['otp'], $data['password'])) {
            return $this->apiResponse(false, 'Unable to reset password. Please request a new OTP.', null, null, 422);
        }

        return $this->apiResponse(true, 'Password reset successfully.');
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
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
