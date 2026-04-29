<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // GET PROFILE
    public function getProfile()
    {
        $user = auth()->user()->load('employee');

        return response()->json([
            'name'  => $user->name,
            'email' => $user->email,
            'image' => $user->employee->image ?? null
        ]);
    }

    // UPDATE PROFILE
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'nullable|string',
            'email' => 'nullable|email',
            'image' => 'nullable|image'
        ]);

        if ($request->has('name'))  $user->name  = $request->name;
        if ($request->has('email')) $user->email = $request->email;

        // If image exists
        if ($request->hasFile('image')) {
            $image = $request->image->store('profile', 'public');
            $user->employee->update(['image' => $image]);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile Updated Successfully',
            'user'    => $user
        ]);
    }
}
