<?php

namespace App\Services\Auth;

use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordOtpService
{
    public function sendOtp(string $email): void
    {
        $userExists = DB::table('users')->where('email', $email)->exists();

        if (! $userExists) {
            return;
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to($email)->send(new PasswordResetOtpMail($otp));
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $record = $this->latestValidRecord($email);

        if (! $record || $record->attempts >= 5) {
            return false;
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            DB::table('password_reset_otps')->where('id', $record->id)->increment('attempts');
            return false;
        }

        DB::table('password_reset_otps')->where('id', $record->id)->update([
            'verified_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    public function resetPassword(string $email, string $otp, string $password): bool
    {
        $record = $this->latestValidRecord($email);

        if (! $record || ! $record->verified_at || ! Hash::check($otp, $record->otp_hash)) {
            return false;
        }

        $updated = DB::table('users')->where('email', $email)->update([
            'password' => Hash::make($password),
            'updated_at' => now(),
        ]);

        if ($updated) {
            DB::table('password_reset_otps')->where('id', $record->id)->delete();
        }

        return (bool) $updated;
    }

    private function latestValidRecord(string $email)
    {
        return DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
    }
}
