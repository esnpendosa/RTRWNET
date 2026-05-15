<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filename = 'all data pelanggan terbaru.xlsx';
$spreadsheet = IOFactory::load($filename);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

echo "Headers:\n";
print_r($rows[0]);

echo "\nFirst 5 Rows:\n";
for ($i = 1; $i <= 5; $i++) {
    if (isset($rows[$i])) {
        print_r($rows[$i]);
    }
}
