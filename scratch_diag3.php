<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$router = \App\Models\Router::first();
try {
    $client = new \RouterOS\Client([
        'host' => $router->ip_host,
        'user' => $router->username,
        'pass' => decrypt($router->password_encrypted),
        'port' => (int) ($router->api_port ?? 8728),
        'timeout' => 5
    ]);
    
    echo "Connected directly.\n";
    
    // Test findSimpleQueue directly
    echo "Testing findSimpleQueue ktr01...\n";
    $query = new \RouterOS\Query('/queue/simple/print');
    $query->equal('.proplist', '.id,name,target,comment,max-limit');
    $query->equal('name', 'ktr01');
    try {
        $res = $client->query($query)->read();
        var_dump($res);
    } catch (\Exception $e) {
        echo "findSimpleQueue EXCEPTION: " . $e->getMessage() . "\n";
    }

    echo "Testing Ping directly...\n";
    $q = new \RouterOS\Query('/ping');
    $q->equal('address', '192.168.90.254');
    $q->equal('count', 2);
    try {
        $res = $client->query($q)->read();
        var_dump($res);
    } catch (\Exception $e) {
        echo "Ping EXCEPTION: " . $e->getMessage() . "\n";
    }

} catch (\Exception $e) {
    echo "Connection EXCEPTION: " . $e->getMessage() . "\n";
}
