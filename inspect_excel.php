<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('all data pelanggan.xlsx');
echo "Sheet Names:\n";
print_r($spreadsheet->getSheetNames());
