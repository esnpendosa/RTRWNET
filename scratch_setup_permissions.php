<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;
use App\Models\Role;

echo "Setting up billing permissions...\n";

$permission = Permission::firstOrCreate(
    ['code' => 'billing_view'],
    ['name' => 'View Billing', 'module' => 'Billing']
);

// Admin
$admin = Role::where('id_role', 1)->first();
if ($admin) {
    $admin->permissions()->syncWithoutDetaching([$permission->id_permission]);
    echo "Assigned to Admin.\n";
}

// Pelanggan
$customer = Role::where('id_role', 4)->first();
if ($customer) {
    $customer->permissions()->syncWithoutDetaching([$permission->id_permission]);
    echo "Assigned to Pelanggan.\n";
}

echo "Done.\n";
