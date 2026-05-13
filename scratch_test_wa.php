<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wa = new \App\Services\WhatsappClient();
$res = $wa->sendMessage('6285730302827', 'TEST NOTIF DARI ANTIGRAVITY - SCRIPT');
echo "Result: " . ($res ? 'SUCCESS' : 'FAILED') . "\n";
