<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Pelanggan::where('kode_pelanggan', 'AD20')->first();
if ($p) {
    echo "Nama: " . $p->nama_pelanggan . "\n";
    echo "Alamat: " . $p->alamat . "\n";
    echo "Latitude: " . $p->latitude . "\n";
    echo "Longitude: " . $p->longitude . "\n";
    if ($p->latitude && $p->longitude) {
        echo "Google Maps: https://www.google.com/maps?q=" . $p->latitude . "," . $p->longitude . "\n";
    } else {
        echo "Koordinat belum diisi.\n";
    }
} else {
    echo "Pelanggan AD20 tidak ditemukan.\n";
}
