<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\HRMS\Dashboard\DashboardResolverS;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DashboardC extends Controller
{
    private DashboardResolverS $dashboardResolver;

    public function __construct(DashboardResolverS $dashboardResolver)
    {
        $this->middleware('auth');
        $this->dashboardResolver = $dashboardResolver;
    }

    public function redirectDashboard()
    {
        $role = $this->dashboardResolver->resolveRole(auth()->user());

        return redirect()->route($this->dashboardResolver->routeNameFor($role));
    }

    public function superAdmin()
    {
        return $this->renderRoleDashboard('super_admin');
    }

    public function hrAdmin()
    {
        return $this->renderRoleDashboard('hr_admin');
    }

    public function financeAdmin()
    {
        return $this->renderRoleDashboard('finance_admin');
    }

    public function projectAdmin()
    {
        return $this->renderRoleDashboard('project_admin');
    }

    public function operationsAdmin()
    {
        return $this->renderRoleDashboard('operations_admin');
    }

    public function customAdmin()
    {
        return $this->renderRoleDashboard('custom_admin');
    }

    public function employee()
    {
        return $this->renderRoleDashboard('employee');
    }

    public function adminIndex()
    {
        return $this->redirectDashboard();
    }

    public function employeeIndex()
    {
        return $this->redirectDashboard();
    }

    public function generateStorageLink()
    {
        $link = public_path('storage');

        if (File::exists($link)) {
            return response()->json([
                'status' => true,
                'message' => 'Storage link already exists',
            ]);
        }

        try {
            Artisan::call('storage:link');

            return response()->json([
                'status' => true,
                'message' => 'Storage link created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create storage link',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renderRoleDashboard(string $requestedRole)
    {
        $user = auth()->user();

        if (! $this->dashboardResolver->canViewRole($user, $requestedRole)) {
            return redirect()->route('dashboard');
        }

        $role = $requestedRole === 'employee'
            ? $this->dashboardResolver->resolveRole($user)
            : $requestedRole;

        if ($requestedRole === 'employee' && $role !== 'employee') {
            return redirect()->route($this->dashboardResolver->routeNameFor($role));
        }

        $dashboard = $this->dashboardResolver->dashboardData($role, $user);

        return view($this->dashboardResolver->viewFor($role), compact('dashboard'));
    }
}
