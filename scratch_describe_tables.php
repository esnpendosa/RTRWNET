<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['pelanggan', 'tagihan', 'mikrotik_router', 'mikrotik_stat'] as $table) {
    echo "Table: $table\n";
    $cols = DB::select("DESCRIBE $table");
    print_r($cols);
    echo "\n";
}
