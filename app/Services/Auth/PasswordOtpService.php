<?php

namespace App\Services\Auth;

use App\Mail\PasswordResetOtpMail;
use App\Models\Core\UserM as User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PasswordOtpService
{
    public function sendOtp(string $email, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $userExists = DB::table('users')->where('email', $email)->exists();
        Log::info('[ForgotPassword] user lookup', ['email' => $email, 'user_found' => $userExists]);

        if (! $userExists) {
            return;
        }

        $latest = $this->latestRecord($email);
        $lastSentAtValue = $latest ? data_get($latest, 'last_sent_at') : null;
        Log::info('[ForgotPassword] last_sent_at checked', [
            'email' => $email,
            'has_previous_record' => (bool) $latest,
            'has_last_sent_at' => (bool) $lastSentAtValue,
        ]);
        if ($lastSentAtValue) {
            $lastSentAt = \Carbon\Carbon::parse($lastSentAtValue);
            if ($lastSentAt->diffInSeconds(now()) < 60) {
                return;
            }
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_otps')->where('email', $email)->delete();

        $payload = [
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('password_reset_otps', 'last_sent_at')) {
            $payload['last_sent_at'] = now();
        }
        if (Schema::hasColumn('password_reset_otps', 'ip_address')) {
            $payload['ip_address'] = $ipAddress;
        }
        if (Schema::hasColumn('password_reset_otps', 'user_agent')) {
            $payload['user_agent'] = $userAgent ? mb_substr($userAgent, 0, 255) : null;
        }
        DB::table('password_reset_otps')->insert($payload);
        Log::info('[ForgotPassword] otp record created/updated', ['email' => $email]);

        try {
            Log::info('[ForgotPassword] sending otp email', ['email' => $email, 'queue' => config('queue.default')]);
            Mail::to($email)->queue(new PasswordResetOtpMail($otp));
            Log::info('[ForgotPassword] otp email sent', ['email' => $email]);
        } catch (\Throwable $e) {
            Log::error('Failed to queue password reset OTP email.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $record = $this->latestValidRecord($email);

        if (! $record) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }

        if ((int) $record->attempts >= 5) {
            return ['success' => false, 'message' => 'Too many attempts. Please request a new OTP.'];
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            DB::table('password_reset_otps')->where('id', $record->id)->increment('attempts');
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        DB::table('password_reset_otps')->where('id', $record->id)->update([
            'verified_at' => now(),
            'updated_at' => now(),
        ]);

        return ['success' => true, 'message' => 'OTP verified successfully.'];
    }

    public function resetPassword(string $email, string $otp, string $password): array
    {
        $record = $this->latestValidRecord($email);

        if (! $record || ! $record->verified_at || ! Hash::check($otp, $record->otp_hash)) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return ['success' => false, 'message' => 'Unable to reset password. Please request a new OTP.'];
        }

        $updated = DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($password),
            'updated_at' => now(),
        ]);

        if ($updated) {
            DB::table('password_reset_otps')->where('id', $record->id)->delete();
            if (Schema::hasTable('personal_access_tokens') && method_exists($user, 'tokens')) {
                try {
                    $user->tokens()->delete();
                } catch (\Throwable $e) {
                    Log::warning('Failed to revoke personal access tokens after password reset.', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'success' => (bool) $updated,
            'message' => (bool) $updated
                ? 'Password reset successfully.'
                : 'Unable to reset password. Please request a new OTP.',
        ];
    }

    private function latestValidRecord(string $email)
    {
        return DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }

    private function latestRecord(string $email)
    {
        return DB::table('password_reset_otps')
            ->where('email', $email)
            ->orderByDesc('id')
            ->first();
    }
}
