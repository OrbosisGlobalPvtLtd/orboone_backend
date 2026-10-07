<?php

namespace App\Providers;

use App\Charts\AttendancesChart;
use App\Charts\PerformanceChart;
use App\Models\Core\AccessM as Access;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\Core\Branding\BrandingSettingsS;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Paginator::defaultView('vendor.pagination.orbo');
        Paginator::defaultSimpleView('vendor.pagination.orbo-simple');

        Blade::directive('checked', function ($expression) {
            return "<?php echo ({$expression}) ? 'checked' : ''; ?>";
        });

        Blade::directive('selected', function ($expression) {
            return "<?php echo ({$expression}) ? 'selected' : ''; ?>";
        });

        View::composer('*', function ($view) {
            $view->with('branding', BrandingSettingsS::get());

            if (Auth::check()) {
                $accesses = resolve(Access::class)->get(true);
                $view->with('accesses', $accesses);

                $userId = Auth::id();
                $employee = null;
                $isEmployeeUser = false;
                $authEmployeeId = null;

                try {
                    // Try using EmployeeM if it exists
                    if (class_exists(EmployeeM::class)) {
                        $employee = EmployeeM::where('user_id', $userId)->first();
                    } else {
                        // Fallback to DB
                        $employee = DB::table('employees_new')->where('user_id', $userId)->first();
                    }
                } catch (\Exception $e) {
                    // Ignore, maybe table doesn't exist yet
                }

                if ($employee) {
                    $isEmployeeUser = true;
                    $authEmployeeId = $employee->id ?? null;
                }

                $view->with('isEmployeeUser', $isEmployeeUser);
                $view->with('authEmployee', $employee);
                $view->with('authEmployeeId', $authEmployeeId);
            } else {
                $view->with('isEmployeeUser', false);
                $view->with('authEmployee', null);
                $view->with('authEmployeeId', null);
            }
        });
    }
}
