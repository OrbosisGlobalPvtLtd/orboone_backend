<?php

namespace App\Http\Controllers\Api\V1\Birthday;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\BirthdayShareService;
use Illuminate\Http\Request;

class BirthdayShareApiController extends Controller
{
    protected $birthdayShareService;

    public function __construct(BirthdayShareService $birthdayShareService)
    {
        $this->birthdayShareService = $birthdayShareService;
    }

    /**
     * Get birthday share URL and token for the logged-in employee.
     */
    public function shareInfo(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $employee = EmployeeM::where('user_id', $user->id)->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee context not found',
            ], 404);
        }

        $token = $this->birthdayShareService->generateToken($employee);
        $shareUrl = route('public.birthday.share', ['token' => $token]);

        return response()->json([
            'success' => true,
            'message' => 'Birthday share URL generated successfully',
            'data' => [
                'token' => $token,
                'share_url' => $shareUrl,
                'employee_name' => $user->name,
                'employee_code' => $employee->employee_code,
            ]
        ]);
    }
}
