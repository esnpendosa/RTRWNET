<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    $columns = Schema::getColumnListing($tableName);
    if (in_array('customer_id', $columns)) {
        echo "Found customer_id in table: $tableName\n";
    }
}
echo "Check finished.\n";
