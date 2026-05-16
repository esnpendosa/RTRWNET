<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pelanggan;

$customers = Pelanggan::with('tagihan')->get();
$data = [];
foreach ($customers as $p) {
    $unpaid = $p->tagihan->whereIn('status', ['unpaid', 'belum_bayar', 'BELUM BAYAR']);
    $data[] = [
        'id' => $p->id_pelanggan,
        'kode' => $p->kode_pelanggan,
        'nama' => $p->nama_pelanggan,
        'ip' => $p->ip_address,
        'active' => $p->is_active,
        'unpaid_count' => $unpaid->count(),
        'unpaid_details' => $unpaid->map(fn($b) => $b->bulan . '/' . $b->tahun . ' (' . $b->status . ')')->values()->toArray(),
    ];
}

echo json_encode($data, JSON_PRETTY_PRINT);
