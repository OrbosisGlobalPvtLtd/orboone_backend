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
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $service->sendOtp(strtolower($data['email']));
        } catch (\Throwable $e) {
            Log::error('Password reset OTP email failed: '.$e->getMessage());
        }

        return redirect()
            ->route('password.otp.form')
            ->with('email', strtolower($data['email']))
            ->with('success', 'If this email exists, an OTP has been sent.');
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

        if (! $service->verifyOtp(strtolower($data['email']), $data['otp'])) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
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

        if (! $service->resetPassword(strtolower($data['email']), $data['otp'], $data['password'])) {
            return back()->withErrors(['password' => 'Unable to reset password. Please request a new OTP.'])->withInput();
        }

        return redirect()
            ->route('login')
            ->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
