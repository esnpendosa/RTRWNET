<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Router;
use App\Services\MikrotikService;

$router = Router::first();
if (!$router) {
    echo "Router tidak ditemukan di DB.\n";
    exit;
}

$service = new MikrotikService();
$client = $service->getConnection($router);

if (!$client) {
    echo "Gagal konek ke Mikrotik: " . $router->ip_host . "\n";
    exit;
}

echo "--- DAFTAR USER PPPOE AKTIF ---\n";
$active = $client->query('/ppp/active/print')->read();
foreach ($active as $a) {
    echo "User: " . $a['name'] . " | IP: " . $a['address'] . "\n";
}

echo "\n--- DAFTAR USER HOTSPOT AKTIF ---\n";
$hotspot = $client->query('/ip/hotspot/active/print')->read();
foreach ($hotspot as $h) {
    echo "User: " . $h['user'] . " | IP: " . $h['address'] . "\n";
}
