<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ts = \App\Models\Tagihan::with('pelanggan')->where('bulan', 5)->where('tahun', 2026)->get();
foreach($ts as $t) {
    echo $t->id_tagihan . " | " . $t->pelanggan->nama_pelanggan . " | " . $t->jumlah . " | " . $t->status . "\n";
}
