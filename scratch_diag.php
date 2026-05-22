<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$router = \App\Models\Router::first();
echo "Router IP: {$router->ip_host}\n";

try {
    $client = new \RouterOS\Client([
        'host' => $router->ip_host,
        'user' => $router->username,
        'pass' => decrypt($router->password_encrypted),
        'port' => (int) ($router->api_port ?? 8728),
        'timeout' => 5
    ]);
    
    echo "Connected.\n";
    $q = new \RouterOS\Query('/ping');
    $q->equal('address', '192.168.90.254'); // test pinging the user
    $q->equal('count', 2);
    $res = $client->query($q)->read();
    
    echo "Ping Result:\n";
    var_dump($res);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
