<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating user accounts for pelanggan...\n";

$pelanggans = Pelanggan::whereNull('id_user')->get();
$createdCount = 0;

foreach ($pelanggans as $p) {
    // Check if user already exists with this kode_pelanggan
    $username = strtolower($p->kode_pelanggan);
    $email = $username . '@rozitech.net';
    
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        $user = User::create([
            'name' => $p->nama_pelanggan,
            'email' => $email,
            'username_email' => $username,
            'password' => Hash::make('password123'),
            'id_role' => 4, // Pelanggan
            'is_active' => true,
        ]);
        $createdCount++;
    }
    
    $p->update(['id_user' => $user->id]);
}

echo "Successfully created $createdCount new user accounts and linked all pelanggan.\n";
