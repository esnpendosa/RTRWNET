<?php
use App\Models\Pelanggan;

// Koordinat Presisi dari User
$lat_user = -7.123778;
$lng_user = 112.592629;

$customers = Pelanggan::all();
echo "Memindahkan " . $customers->count() . " pelanggan ke koordinat presisi user...\n";

foreach($customers as $c) {
    // Sebaran rapat di sekitar titik yang diberikan user (+/- 500m)
    $c->latitude = $lat_user + (rand(-5000, 5000) / 1000000);
    $c->longitude = $lng_user + (rand(-5000, 5000) / 1000000);
    $c->save();
}

$sample = Pelanggan::first();
echo "Update Selesai. Lokasi sekarang: " . $sample->latitude . ", " . $sample->longitude . "\n";
