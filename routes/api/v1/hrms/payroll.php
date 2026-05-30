<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy Payroll API (Retired)
|--------------------------------------------------------------------------
| Legacy Payroll retired. Enterprise Payroll is the only active payroll engine.
| All previous /api/v1/payroll/* endpoints now return 410 Gone.
*/

Route::middleware('auth:sanctum')->group(function () {
    $legacyRetired = function (Request $request) {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => 'Legacy payroll is retired. Use Enterprise Payroll APIs.',
            'data' => null,
        ], 410);
    };

    Route::get('/payroll/dashboard', $legacyRetired);
    Route::get('/payroll/salary-structure', $legacyRetired);
    Route::get('/payroll/monthly-summary', $legacyRetired);
    Route::post('/payroll/claims', $legacyRetired);
    Route::get('/payroll/claims-history', $legacyRetired);
    Route::get('/payroll/payslip', $legacyRetired);
    Route::get('/payroll/payslip-history', $legacyRetired);
});
