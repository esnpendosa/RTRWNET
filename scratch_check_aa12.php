<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pelanggan;

$p = Pelanggan::where('kode_pelanggan', 'AA12')->first();
if ($p) {
    echo "Kode: " . $p->kode_pelanggan . "\n";
    echo "Nama: " . $p->nama_pelanggan . "\n";
    echo "Mikrotik User: '" . $p->mikrotik_username . "'\n";
    echo "Mikrotik Type: " . $p->mikrotik_type . "\n";
} else {
    echo "Pelanggan AA12 tidak ditemukan.\n";
}
