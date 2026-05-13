<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncRouterStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync stats from all MikroTik routers';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\MikrotikService $mikrotikService)
    {
        $this->info('Starting router sync...');
        $routers = \App\Models\Router::all();
        
        foreach ($routers as $router) {
            $this->info("Syncing stats for: {$router->nama_router}");
            $mikrotikService->syncStats($router);
        }

        $this->info('All routers synced.');
    }
}
