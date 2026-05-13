<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rutes = App\Models\Rute::all();
foreach($rutes as $r) {
    $t = $r->details()->count();
    $f = $r->details()->where('status_kunjungan', 'Visited')->count();
    if($t > 0 && $t === $f) {
        $r->update(['status' => 'Completed']);
    }
}
echo 'Synced';
