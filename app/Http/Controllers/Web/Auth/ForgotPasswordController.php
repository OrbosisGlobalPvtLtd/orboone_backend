<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request, PasswordOtpService $service)
    {
        $request->merge([
            'email' => strtolower($request->input('email')),
        ]);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'This email is not registered.',
        ]);

        try {
            $service->sendOtp($data['email']);
        } catch (\Throwable $e) {
            Log::error('Password reset OTP email failed: '.$e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to send OTP.'], 500);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'An OTP has been sent to your email.',
                'email' => $data['email']
            ]);
        }

        return redirect()
            ->route('password.otp.form')
            ->with('email', $data['email'])
            ->with('success', 'An OTP has been sent to your email.');
    }

    public function showVerifyForm(Request $request)
    {
        return view('auth.verify-otp', [
            'email' => old('email', session('email', $request->query('email'))),
        ]);
    }

    public function verifyOtp(Request $request, PasswordOtpService $service)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $result = $service->verifyOtp(strtolower($data['email']), $data['otp']);

        if (! ($result['success'] ?? false)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Invalid or expired OTP.'
                ], 400);
            }
            return back()->withErrors(['otp' => $result['message'] ?? 'Invalid or expired OTP.'])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. Set your new password.',
                'email' => strtolower($data['email']),
                'otp' => $data['otp']
            ]);
        }

        return redirect()
            ->route('password.reset.form')
            ->with('email', strtolower($data['email']))
            ->with('otp', $data['otp'])
            ->with('success', 'OTP verified. Set your new password.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => old('email', session('email', $request->query('email'))),
            'otp' => old('otp', session('otp')),
        ]);
    }

    public function resetPassword(Request $request, PasswordOtpService $service)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = $service->resetPassword(strtolower($data['email']), $data['otp'], $data['password']);

        if (! ($result['success'] ?? false)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Unable to reset password. Please request a new OTP.'
                ], 400);
            }
            return back()->withErrors(['password' => $result['message'] ?? 'Unable to reset password. Please request a new OTP.'])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. Please login with your new password.'
            ]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
