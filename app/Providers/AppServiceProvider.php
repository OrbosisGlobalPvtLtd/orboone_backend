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
            static $memoized = null;
            static $lastUserId = null;

            $userId = Auth::id();

            if ($memoized === null || $lastUserId !== $userId) {
                $lastUserId = $userId;
                $branding = BrandingSettingsS::get();

                if ($userId) {
                    $accesses = resolve(Access::class)->get(true);

                    $employee = null;
                    try {
                        if (class_exists(EmployeeM::class)) {
                            $user = Auth::user();
                            $employee = ($user && $user->relationLoaded('employee'))
                                ? $user->employee
                                : EmployeeM::where('user_id', $userId)->first();
                        } else {
                            $employee = DB::table('employees_new')->where('user_id', $userId)->first();
                        }
                    } catch (\Exception $e) {
                        // Ignore, fallback to null
                    }

                    $memoized = [
                        'branding' => $branding,
                        'accesses' => $accesses,
                        'isEmployeeUser' => (bool) $employee,
                        'authEmployee' => $employee,
                        'authEmployeeId' => $employee->id ?? null,
                    ];
                } else {
                    $memoized = [
                        'branding' => $branding,
                        'accesses' => collect(),
                        'isEmployeeUser' => false,
                        'authEmployee' => null,
                        'authEmployeeId' => null,
                    ];
                }
            }

            $view->with($memoized);
        });
    }
}

