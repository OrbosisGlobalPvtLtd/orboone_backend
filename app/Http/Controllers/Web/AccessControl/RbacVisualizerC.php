<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Services\AccessControl\RbacVisualizerS;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RbacVisualizerC extends Controller
{
    public function __construct(private RbacVisualizerS $visualizerService)
    {
    }

    /**
     * Display the dynamic RBAC Visualizer & Matrix Explorer page.
     */
    public function index(Request $request)
    {
        $data = $this->visualizerService->getVisualizerData();

        $selectedRoleIds = (array) $request->input('roles', $data['roles']->pluck('id')->all());
        $selectedModule = (string) $request->input('module', '');
        $selectedCrud = (string) $request->input('crud', 'all');
        $viewMode = (string) $request->input('view_mode', 'matrix');
        $search = (string) $request->input('search', '');
        $selectedTargetRoleId = (int) $request->input('target_role_id', $data['roles']->first()->id ?? 1);

        return view('access_control.visualizer.index', array_merge($data, [
            'selectedRoleIds' => array_map('intval', $selectedRoleIds),
            'selectedModule' => $selectedModule,
            'selectedCrud' => $selectedCrud,
            'viewMode' => $viewMode,
            'search' => $search,
            'selectedTargetRoleId' => $selectedTargetRoleId,
        ]));
    }

    /**
     * Live authorization simulator & diagnostic trace API endpoint.
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:role,user',
            'target_id' => 'required|integer',
            'resource_type' => 'required|in:permission,menu,route',
            'resource_key' => 'required|string',
        ]);

        $targetType = (string) $request->input('target_type');
        $targetId = (int) $request->input('target_id');
        $resourceType = (string) $request->input('resource_type');
        $resourceKey = (string) $request->input('resource_key');

        $result = $this->visualizerService->simulateAccess($targetType, $targetId, $resourceType, $resourceKey);

        return response()->json($result);
    }

    /**
     * Export complete RBAC access matrix to CSV.
     */
    public function exportCsv(Request $request)
    {
        $csvContent = $this->visualizerService->generateCsvData();
        $filename = 'rbac_access_matrix_report_' . date('Y-m-d_His') . '.csv';

        return response($csvContent, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
