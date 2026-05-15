<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filename = 'all data pelanggan terbaru.xlsx';
$spreadsheet = IOFactory::load($filename);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

$sql = "";
$count = 0;

foreach ($rows as $index => $row) {
    if ($index == 0) continue; // Skip header
    if (empty($row[2])) continue; // Skip if KODE is empty

    $nama = str_replace("'", "''", $row[1]);
    $kode = str_replace("'", "''", $row[2]);
    $harga = (int)$row[3] * 1000;
    $ip = str_replace("'", "''", $row[4]);
    $wa = str_replace("'", "''", $row[5]);
    $maps = str_replace("'", "''", $row[6]);

    // Format WA: ensure it starts with 62
    if (!empty($wa)) {
        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        } elseif (!str_starts_with($wa, '62')) {
            $wa = '62' . $wa;
        }
    }

    $sql .= "UPDATE pelanggan SET ";
    $sql .= "nama_pelanggan = '$nama', ";
    $sql .= "harga_layanan = $harga, ";
    $sql .= "ip_address = '$ip', ";
    $sql .= "no_wa = '$wa', ";
    $sql .= "maps_url = '$maps' ";
    $sql .= "WHERE kode_pelanggan = '$kode';\n";
    
    $count++;
}

file_put_contents('scratch/update_pelanggan.sql', $sql);
echo "Generated SQL for $count records in scratch/update_pelanggan.sql\n";
