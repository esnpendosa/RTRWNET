<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filename = __DIR__ . '/../perhitungan-knn-fix new.xlsx';
$spreadsheet = IOFactory::load($filename);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

echo "Row 0 (Header 1):\n";
print_r($rows[0]);

echo "\nRow 1 (Header 2):\n";
print_r($rows[1]);

echo "\nRow 2 (Header 3):\n";
print_r($rows[2]);

echo "\nRow 3 (Data 1):\n";
print_r($rows[3]);

echo "\nRow 107 (Test Data):\n";
print_r($rows[107]);
