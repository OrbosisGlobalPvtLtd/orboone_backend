<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PayrollAdminC extends Controller
{
    /*
     * Legacy Payroll retired. Enterprise Payroll is the only active payroll engine.
     * This legacy controller is intentionally non-operational.
     */

    public function __call(string $name, array $arguments): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => 'Legacy payroll is retired. Use Enterprise Payroll.',
            'data' => null,
        ], 410);
    }
}
