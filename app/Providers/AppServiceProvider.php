<?php

namespace App\Providers;

use App\Charts\AttendancesChart;
use App\Charts\PerformanceChart;
use App\Models\Core\AccessM as Access;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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

        \Illuminate\Support\Facades\Blade::directive('checked', function ($expression) {
            return "<?php echo ({$expression}) ? 'checked' : ''; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('selected', function ($expression) {
            return "<?php echo ({$expression}) ? 'selected' : ''; ?>";
        });

        View::composer('*', function ($view) {
            $view->with('branding', \App\Services\Core\Branding\BrandingSettingsS::get());

            if (auth()->check()) {
                $accesses = resolve(Access::class)->get(true);
                $view->with('accesses', $accesses);

                $userId = auth()->id();
                $employee = null;
                $isEmployeeUser = false;
                $authEmployeeId = null;

                try {
                    // Try using EmployeeM if it exists
                    if (class_exists(\App\Models\HRMS\Employee\EmployeeM::class)) {
                        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $userId)->first();
                    } else {
                        // Fallback to DB
                        $employee = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $userId)->first();
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
