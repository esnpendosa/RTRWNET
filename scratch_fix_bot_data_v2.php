<?php

use App\Models\BotResponse;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear existing responses
BotResponse::truncate();

// 1. Menu Utama
$mainMenu = BotResponse::create([
    'keyword' => 'menu,help,bantuan',
    'menu_label' => '🏠 MENU UTAMA',
    'response' => 'Selamat datang di R-Care AI. Kami siap membantu Anda dengan layanan internet terbaik.',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 1
]);

// 2. Sub-menu 1: Cek Tagihan
BotResponse::create([
    'keyword' => '1',
    'menu_label' => '💰 Cek Tagihan AD20',
    'parent_id' => $mainMenu->id,
    'response' => '{cek_tagihan}',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

// 3. Sub-menu 2: Info Paket
$paketMenu = BotResponse::create([
    'keyword' => '2',
    'menu_label' => '🚀 Daftar Paket Internet',
    'parent_id' => $mainMenu->id,
    'response' => 'Pilih paket yang sesuai dengan kebutuhan Anda:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 2
]);

// 4. Detail Paket (Sub of Info Paket)
BotResponse::create([
    'keyword' => 'paket hemat',
    'menu_label' => '📶 Paket Hemat (2 Mbps)',
    'parent_id' => $paketMenu->id,
    'response' => '🚀 *Paket Hemat*\nKecepatan: 2 Mbps\nBiaya: *Rp 100.000/bulan*\nCocok untuk penggunaan ringan & browsing.',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'paket gaming',
    'menu_label' => '🎮 Paket Gaming (10 Mbps)',
    'parent_id' => $paketMenu->id,
    'response' => '👾 *Paket Gaming*\nKecepatan: 10 Mbps\nBiaya: *Rp 250.000/bulan*\nCocok untuk gamer & streaming 4K tanpa lag!',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

// 5. Sub-menu 3: Hubungi Admin
BotResponse::create([
    'keyword' => '3',
    'menu_label' => '👩‍💼 Bicara dengan Admin',
    'parent_id' => $mainMenu->id,
    'response' => 'Klik link berikut untuk terhubung langsung dengan Customer Service kami: https://wa.me/6282187827382',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 3
]);

// 6. Default Fallback
BotResponse::create([
    'keyword' => 'default',
    'menu_label' => null,
    'response' => 'Maaf, saya tidak mengerti. Ketik *menu* untuk melihat daftar layanan kami.',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => false,
    'sort_order' => 99
]);

echo "Bot responses data with NICE LABELS fixed successfully!\n";
