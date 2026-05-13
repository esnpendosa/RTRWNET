<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tagihan;
use App\Http\Controllers\PaymentController;

$tagihan = Tagihan::first();
if (!$tagihan) {
    die("No tagihan found\n");
}

echo "Testing getSnapToken for Tagihan ID: {$tagihan->id_tagihan}\n";
try {
    $controller = app(PaymentController::class);
    $response = $controller->getSnapToken($tagihan);
    echo "Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
