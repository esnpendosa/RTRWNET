<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Pelanggan::where('nama_pelanggan', 'like', '%Lantai%')->first();
if ($p) {
    echo "ID: " . $p->id_pelanggan . "\n";
    echo "Nama: " . $p->nama_pelanggan . "\n";
    echo "No WA: " . $p->no_wa . "\n";
} else {
    echo "Lantai not found\n";
}
