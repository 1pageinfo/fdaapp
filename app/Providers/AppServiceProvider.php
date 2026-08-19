<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Displays a year as a financial-year range, e.g. 2025 -> "2025-2024".
        Blade::directive('fy', function ($expression) {
            return "<?php echo \\App\\Providers\\AppServiceProvider::financialYear($expression); ?>";
        });

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
                return true;
            }

            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });
    }

    /**
     * Format a year as a financial-year range: 2025 -> "2025-2024".
     */
    public static function financialYear($year): string
    {
        if ($year === null || $year === '') {
            return '';
        }

        $year = (int) $year;

        return $year . '-' . ($year - 1);
    }
}
