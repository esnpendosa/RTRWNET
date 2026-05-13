<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$triggers = DB::select('SHOW TRIGGERS');
if (empty($triggers)) {
    echo "No triggers found.\n";
} else {
    foreach ($triggers as $t) {
        echo "Trigger: " . $t->Trigger . " on Table: " . $t->Table . "\n";
        echo "Statement: " . $t->Statement . "\n\n";
    }
}
