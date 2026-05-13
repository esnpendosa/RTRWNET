<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\WhatsappController;
use Illuminate\Http\Request;

$controller = app(WhatsappController::class);

// Mock request for 'lok' command
$request = new Request([
    'remoteJid' => '62812345678@s.whatsapp.net',
    'message' => 'lok AD20',
    'sender' => '62812345678@s.whatsapp.net',
    'type' => 'chat',
    'isGroup' => false
]);

$response = $controller->webhook($request);
echo "Response for 'lok AD20':\n";
print_r($response->getData());

$request2 = new Request([
    'remoteJid' => '62812345678@s.whatsapp.net',
    'message' => 'lok budi',
    'sender' => '62812345678@s.whatsapp.net',
    'type' => 'chat',
    'isGroup' => false
]);

$response2 = $controller->webhook($request2);
echo "\nResponse for 'lok budi':\n";
print_r($response2->getData());
