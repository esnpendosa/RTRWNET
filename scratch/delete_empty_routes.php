<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rutes = App\Models\Rute::all();
$deleted = 0;
foreach($rutes as $r) {
    if($r->details()->count() == 0) {
        $r->delete();
        $deleted++;
    }
}
echo "Deleted $deleted empty routes.";
