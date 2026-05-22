<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$p = \App\Models\Pelanggan::where('kode_pelanggan', 'ktr01')->first();
echo "Type: " . $p->mikrotik_type . "\n";
$router = $p->router;

$mikrotik = app(\App\Services\MikrotikService::class);
echo "Getting active IP...\n";
$activeIp = $mikrotik->getPelangganActiveIp($router, 'ktr01', $p->mikrotik_type);
var_dump($activeIp);

echo "Testing Ping...\n";
$results = $mikrotik->ping($router, '192.168.90.254');
var_dump($results);
