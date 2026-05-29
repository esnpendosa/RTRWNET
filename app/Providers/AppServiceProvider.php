<?php

namespace App\Providers;

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

    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasPermission($ability) ?: null;
        });

        config(['app.locale' => 'id']);
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id');

        // Safe mock for global_units to prevent querying the non-existent units table
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('global_units', collect([]));
        });
    }
}