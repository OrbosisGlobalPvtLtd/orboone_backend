<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfilesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $profile = auth()->user()->load([
            'role',
            'primaryRole',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'employee.profile',
        ]);

        return view('settings.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            $userUpdate = [
                'name' => $data['name'],
                'email' => $data['email'],
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'phone')) {
                $userUpdate['phone'] = $data['phone'] ?? null;
            }

            DB::table('users')->where('id', $user->id)->update($userUpdate);

            $employee = DB::table('employees_new')->where('user_id', $user->id)->first();

            if ($employee) {
                $profileData = [
                    'employee_id' => $employee->id,
                    'address' => $data['address'] ?? null,
                    'updated_at' => now(),
                ];

                if ($request->hasFile('profile_image')) {
                    $profileData['profile_image'] = $request->file('profile_image')->store('employee-profiles', 'public');
                }

                $exists = DB::table('employee_profiles')->where('employee_id', $employee->id)->exists();

                if (! $exists) {
                    $profileData['profile_status'] = 'pending';
                    $profileData['is_profile_completed'] = 0;
                    $profileData['created_at'] = now();
                }

                DB::table('employee_profiles')->updateOrInsert(
                    ['employee_id' => $employee->id],
                    $profileData
                );
            }
        });

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password updated successfully.');
    }
}
