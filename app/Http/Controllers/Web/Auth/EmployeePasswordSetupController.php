<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class EmployeePasswordSetupController extends Controller
{
    public function show(string $token)
    {
        $setupToken = $this->validToken($token);

        if (! $setupToken) {
            return redirect()->route('login')->with('fail', 'Password setup link is invalid or expired.');
        }

        $user = DB::table('users')->where('id', $setupToken->user_id)->first();

        if (! $user) {
            return redirect()->route('login')->with('fail', 'Password setup link is invalid.');
        }

        return view('auth.employee-password-setup', compact('token', 'user'));
    }

    public function update(Request $request, string $token)
    {
        $setupToken = $this->validToken($token);

        if (! $setupToken) {
            return redirect()->route('login')->with('fail', 'Password setup link is invalid or expired.');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userUpdate = [
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $userUpdate['must_change_password'] = false;
        }

        DB::transaction(function () use ($setupToken, $userUpdate) {
            DB::table('users')->where('id', $setupToken->user_id)->update($userUpdate);
            DB::table('employee_password_setup_tokens')->where('id', $setupToken->id)->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('login')
            ->with('success', 'Password set successfully. Please login with your new password.');
    }

    private function validToken(string $token)
    {
        return DB::table('employee_password_setup_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
