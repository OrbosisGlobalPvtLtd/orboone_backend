<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollGenerateC extends Controller
{
    /*
     * Legacy Payroll retired. Enterprise Payroll is the only active payroll engine.
     * This controller intentionally returns 410 for any legacy web access.
     */

    public function index(Request $request): JsonResponse
    {
        return $this->retiredResponse();
    }

    public function process(Request $request): JsonResponse
    {
        return $this->retiredResponse();
    }

    private function retiredResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => 'Legacy payroll is retired. Use Enterprise Payroll.',
            'data' => null,
        ], 410);
    }
}
