<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

$filename = 'all data pelanggan terbaru.xlsx';
if (!file_exists($filename)) {
    die("File not found: $filename\n");
}

$spreadsheet = IOFactory::load($filename);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

echo "Starting update for " . (count($rows) - 1) . " records...\n";

$updated = 0;
$notFound = 0;

foreach ($rows as $index => $row) {
    if ($index == 0) continue; // Skip header
    if (empty($row[2])) continue; // Skip if KODE is empty

    $kode = trim($row[2]);
    $nama = trim($row[1]);
    $harga = (int)$row[3] * 1000;
    $ip = trim($row[4]);
    $wa = trim($row[5]);
    $maps = trim($row[6]);

    // Format WA: ensure it starts with 62
    if (!empty($wa)) {
        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        } elseif (!str_starts_with($wa, '62') && !str_starts_with($wa, '+')) {
            $wa = '62' . $wa;
        }
    }

    $pelanggan = Pelanggan::where('kode_pelanggan', $kode)->first();

    if ($pelanggan) {
        $pelanggan->update([
            'nama_pelanggan' => $nama,
            'harga_layanan' => $harga,
            'ip_address' => $ip,
            'no_wa' => $wa,
            'maps_url' => $maps,
        ]);
        $updated++;
        if ($updated % 50 == 0) echo "Updated $updated records...\n";
    } else {
        $notFound++;
    }
}

echo "\nUpdate Finished!\n";
echo "Total Updated: $updated\n";
echo "Total Not Found: $notFound (Skipped)\n";
