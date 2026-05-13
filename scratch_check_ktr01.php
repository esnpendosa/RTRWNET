<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Pelanggan::where('kode_pelanggan', 'KTR01')->first();
if ($p) {
    echo "Pelanggan: " . $p->nama_pelanggan . "\n";
    echo "No WA: " . $p->no_wa . "\n";
    echo "ID Router: " . $p->id_router . "\n";
    echo "Is Active: " . $p->is_active . "\n";
} else {
    echo "Pelanggan KTR01 not found.\n";
}
