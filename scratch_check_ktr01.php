<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pelanggan;
use App\Models\Tagihan;

$customers = Pelanggan::where('ip_address', '192.168.90.254')->get();

$data = [];
foreach ($customers as $p) {
    $bills = $p->tagihan()->get();
    $data[] = [
        'pelanggan' => [
            'id' => $p->id_pelanggan,
            'kode' => $p->kode_pelanggan,
            'nama' => $p->nama_pelanggan,
            'is_active' => $p->is_active,
            'ip_address' => $p->ip_address,
        ],
        'bills' => $bills->toArray(),
    ];
}

echo json_encode($data, JSON_PRETTY_PRINT);
