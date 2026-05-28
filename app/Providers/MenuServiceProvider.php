<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuData = json_decode($verticalMenuJson);

    try {
      if (\Illuminate\Support\Facades\Schema::hasTable('pelanggan')) {
        $pendingCount = \App\Models\Pelanggan::where('kode_pelanggan', 'like', 'REG%')
          ->where('is_active', 0)
          ->count();
        if ($pendingCount > 0) {
          foreach ($verticalMenuData->menu as $item) {
            if (isset($item->slug) && $item->slug === 'registrasi') {
              $item->badge = ['danger', (string)$pendingCount];
            }
          }
        }
      }
    } catch (\Exception $e) {
      // Safe fallback
    }

    // Share all menuData to all the views
    $this->app->make('view')->share('menuData', [$verticalMenuData]);
  }
}
