<?php

use App\Models\BotResponse;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear existing responses to start fresh (optional, but requested to "fix all data")
BotResponse::truncate();

// 1. Menu Utama
$mainMenu = BotResponse::create([
    'keyword' => 'menu,help,bantuan',
    'response' => 'Selamat datang di R-Care AI. Silakan pilih layanan kami:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 1
]);

// 2. Sub-menu 1: Cek Tagihan
BotResponse::create([
    'keyword' => '1',
    'parent_id' => $mainMenu->id,
    'response' => '{cek_tagihan}', // Ini akan mentrigger shortcode di controller
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

// 3. Sub-menu 2: Info Paket
$paketMenu = BotResponse::create([
    'keyword' => '2',
    'parent_id' => $mainMenu->id,
    'response' => 'Berikut daftar paket internet Rozitech:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 2
]);

// 4. Detail Paket (Sub of Info Paket)
BotResponse::create([
    'keyword' => 'paket hemat',
    'parent_id' => $paketMenu->id,
    'response' => '🚀 *Paket Hemat* (2 Mbps): Rp 100.000/bulan',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'paket gaming',
    'parent_id' => $paketMenu->id,
    'response' => '🎮 *Paket Gaming* (10 Mbps): Rp 250.000/bulan',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

// 5. Sub-menu 3: Hubungi Admin
BotResponse::create([
    'keyword' => '3',
    'parent_id' => $mainMenu->id,
    'response' => 'Anda bisa menghubungi admin kami di wa.me/6282187827382 untuk bantuan lebih lanjut.',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 3
]);

// 6. Default Fallback
BotResponse::create([
    'keyword' => 'default',
    'response' => 'Maaf, saya tidak mengerti. Ketik *menu* untuk melihat daftar layanan kami.',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => false,
    'sort_order' => 99
]);

echo "Bot responses data fixed successfully!\n";
