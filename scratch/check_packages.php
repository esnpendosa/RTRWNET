<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pelanggan;

$packages = Pelanggan::select('harga_layanan', 'paket')
    ->distinct()
    ->orderBy('harga_layanan')
    ->get();

foreach ($packages as $pkg) {
    echo "Price: " . $pkg->harga_layanan . " | Package: " . ($pkg->paket ?? 'NULL') . "\n";
}
