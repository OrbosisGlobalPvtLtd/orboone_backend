<?php

namespace App\Http\Controllers\Api\V1\HRMS\Employee;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\HRMS\Employee\AssetAllocationM as AssetAllocation;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Http\Request;

class MyAssetController extends ApiController
{
    /**
     * Get allocated assets for the currently authenticated employee.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myAssets()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
                'data' => []
            ], 404);
        }

        $assets = AssetAllocation::where('employee_id', $employee->id)->get();

        $mappedAssets = $assets->map(function ($asset) {
            $brandModel = $asset->brand_model;
            $parts = explode(' ', trim($brandModel ?? ''), 2);
            $brand = $parts[0] ?? '';
            $model = $parts[1] ?? $brand;

            return [
                'id' => $asset->id,
                'asset_name' => $asset->brand_model ?? 'Asset',
                'asset_code' => $asset->asset_id_sn ?? null,
                'category' => $asset->asset_type ?? null,
                'serial_number' => $asset->asset_id_sn ?? null,
                'brand' => $brand,
                'model' => $model,
                'assigned_date' => $asset->issue_date ?? ($asset->created_at ? $asset->created_at->toDateString() : null),
                'status' => strtolower($asset->status ?? 'active'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $mappedAssets
        ]);
    }
}
