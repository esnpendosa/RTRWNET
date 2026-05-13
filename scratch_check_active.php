<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = \App\Models\Pelanggan::all();
foreach($ps as $p) {
    echo $p->nama_pelanggan . " | " . ($p->is_active ? 'Active' : 'Inactive') . " | " . $p->no_wa . "\n";
}
