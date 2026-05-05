<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Request $request)
    {
        return $this->view($request);
    }

    public function view(Request $request)
{
    $path = $request->query('path');

    if (!$path) {
        return response()->json([
            'success' => false,
            'message' => 'File path required',
        ], 400);
    }

    $user = auth()->user();
    $employee = $user->employee ?? null;

    if (!$employee) {
        abort(403, 'Employee not found');
    }

    $employee->loadMissing('profile', 'documents');

    $profile = $employee->profile;

    $allowedPaths = [];

    if ($profile) {
        if (!empty($profile->profile_image)) {
            $allowedPaths[] = $profile->profile_image;
        }

        if (!empty($profile->resume_file)) {
            $allowedPaths[] = $profile->resume_file;
        }
    }

    foreach ($employee->documents ?? [] as $doc) {
        if (!empty($doc->file_path)) {
            $allowedPaths[] = $doc->file_path;
        }
    }

    if (!in_array($path, $allowedPaths, true)) {
        abort(403, 'Unauthorized access');
    }

    if (!Storage::disk('private')->exists($path)) {
        abort(404);
    }

    return response()->file(Storage::disk('private')->path($path));
}
}