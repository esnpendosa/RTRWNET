<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pelanggan;
use App\Models\Tagihan;

echo "Setting sample prices...\n";
Pelanggan::where('harga_layanan', 0)->update(['harga_layanan' => 150000]);

$currentMonth = now()->month;
$currentYear = now()->year;

echo "Generating bills for Month: $currentMonth, Year: $currentYear...\n";
$pelanggans = Pelanggan::where('is_active', true)->get();
$generatedCount = 0;

foreach ($pelanggans as $p) {
    $exists = Tagihan::where('id_pelanggan', $p->id_pelanggan)
        ->where('bulan', $currentMonth)
        ->where('tahun', $currentYear)
        ->exists();

    if (!$exists && $p->harga_layanan > 0) {
        Tagihan::create([
            'id_pelanggan' => $p->id_pelanggan,
            'bulan' => $currentMonth,
            'tahun' => $currentYear,
            'jumlah' => $p->harga_layanan,
            'status' => 'unpaid',
        ]);
        $generatedCount++;
    }
}

echo "Successfully generated $generatedCount bills.\n";
