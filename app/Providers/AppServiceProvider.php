<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\WorkPhaseAssignment;
use App\Models\Warehouse;
use App\Policies\WorkPhasePolicy;
use App\Policies\WarehousePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WorkPhaseAssignment::class => WorkPhasePolicy::class,
        Warehouse::class => WarehousePolicy::class,
    ];

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
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Define gates for work phase operations
        Gate::define('assign-work-phases', function ($user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('view-work-phases', function ($user) {
            return $user->hasAnyRole(['admin', 'manager', 'operator']);
        });

        Gate::define('manage-warehouse', function ($user) {
            return $user->hasAnyRole(['admin', 'manager', 'warehouse_operator']);
        });
    }
}
