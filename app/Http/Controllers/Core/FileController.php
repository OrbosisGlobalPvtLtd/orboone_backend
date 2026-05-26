<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    public function __construct(private HrmsFileResolverS $resolver)
    {
    }

    public function show(Request $request)
    {
        return $this->view($request);
    }

    public function view(Request $request)
{
    $path = $this->resolver->normalizeDbPath($request->query('path'));

    if (!$path) {
        return response()->json([
            'success' => false,
            'message' => 'File path required',
        ], 400);
    }

    $user = auth()->user();
    if (! $user) {
        abort(401);
    }

    $isAdmin = method_exists($user, 'hasPermission') && (
        $user->hasPermission('employee_documents.view') ||
        $user->hasPermission('documents.verification.view') ||
        $user->hasPermission('payroll.payslips.view_all') ||
        $user->hasPermission('enterprise_reimbursement.manage') ||
        $user->hasPermission('company_documents.manage')
    );

    if (! $isAdmin) {
        $employee = $user->employee ?? null;
        if (! $employee) {
            abort(403, 'Employee not found');
        }

        $isOwnProfile = DB::table('employee_profiles')
            ->where('employee_id', $employee->id)
            ->where(function ($q) use ($path) {
                $q->where('profile_image', $path)
                    ->orWhere('resume_file', $path);
            })
            ->exists();

        $isOwnDoc = DB::table('employee_documents_new')
            ->where('employee_id', $employee->id)
            ->where('file_path', $path)
            ->exists();

        $isOwnPayslip = DB::table('payslips')
            ->where('employee_id', $employee->id)
            ->where('file_path', $path)
            ->exists();

        if (! ($isOwnProfile || $isOwnDoc || $isOwnPayslip)) {
            abort(403, 'Unauthorized access');
        }
    }

    $resolved = $this->resolver->resolve($path);
    if (! $resolved) {
        abort(404);
    }

    return response()->file($resolved['absolute']);
}
}
