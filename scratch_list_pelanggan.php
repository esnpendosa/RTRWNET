<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = \App\Models\Pelanggan::all();
foreach($ps as $p) {
    echo $p->id_pelanggan . " | " . $p->kode_pelanggan . " | " . $p->nama_pelanggan . " | " . $p->no_wa . "\n";
}
