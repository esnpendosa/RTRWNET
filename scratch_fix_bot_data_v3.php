<?php

use App\Models\BotResponse;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear existing responses
BotResponse::truncate();

// 1. MENU UTAMA (Root)
$mainMenu = BotResponse::create([
    'keyword' => 'menu, help, bantuan, hi, halo',
    'menu_label' => '🏠 MENU UTAMA ROZITECH',
    'response' => 'Halo, Saya R-Care, Customer Service AI resmi dari Rozitech. Silakan pilih layanan kami di bawah ini:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 1
]);

// --- KATEGORI 1: LAYANAN WIFI ---
$wifiCategory = BotResponse::create([
    'keyword' => '1',
    'menu_label' => '📶 LAYANAN WIFI (INTERNET)',
    'parent_id' => $mainMenu->id,
    'response' => 'Pilih layanan WiFi yang Anda butuhkan:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'cek tagihan',
    'menu_label' => '💰 Cek Tagihan WiFi',
    'parent_id' => $wifiCategory->id,
    'response' => '{cek_tagihan}',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'paket wifi',
    'menu_label' => '📋 Daftar Paket & Harga',
    'parent_id' => $wifiCategory->id,
    'response' => "Halo! Berikut pilihan paket Rozitech WiFi (Tanpa FUP):\n1. Paket 10 Mbps : Rp 150.000 / bulan\n2. Paket 20 Mbps : Rp 250.000 / bulan\n3. Paket 30 Mbps : Rp 350.000 / bulan\n\nBiaya Pasang: Rp 250.000 (Sudah termasuk 2 bulan internet 10Mbps).",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

BotResponse::create([
    'keyword' => 'pasang wifi',
    'menu_label' => '📝 Formulir Pasang Baru',
    'parent_id' => $wifiCategory->id,
    'response' => "FORMULIR PASANG BARU WIFI (PSB WIFI)\nNAMA :\nALAMAT :\nNO HP :\nSHARE LOKASI :\nFOTO RUMAH :\n\nSilakan isi dan kirimkan kembali nggih Kak.",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 3
]);

BotResponse::create([
    'keyword' => 'lapor',
    'menu_label' => '🛠️ Lapor Gangguan (Mati/Lemot)',
    'parent_id' => $wifiCategory->id,
    'response' => 'Halo! Kami mohon maaf atas ketidaknyamanan Anda. Pastikan lampu modem tidak merah (LOS). Silakan restart modem dengan cabut adaptor 1 menit. Jika tetap mati, hubungi admin untuk pengecekan teknisi.',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 4
]);

// --- KATEGORI 2: JASA IT ---
$itCategory = BotResponse::create([
    'keyword' => '2',
    'menu_label' => '💻 JASA IT (WEB & APP)',
    'parent_id' => $mainMenu->id,
    'response' => 'Rozitech melayani pembuatan Website dan Aplikasi Custom:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 2
]);

BotResponse::create([
    'keyword' => 'web',
    'menu_label' => '🌐 Harga Pembuatan Website',
    'parent_id' => $itCategory->id,
    'response' => "💰 DAFTAR HARGA WEBSITE ROZITECH 💰\n1. Website Profil Bisnis: Mulai Rp 1.5jt\n2. Toko Online: Mulai Rp 3jt\n3. Custom Web System: Hubungi Admin.",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'app',
    'menu_label' => '📱 Harga Pembuatan Aplikasi',
    'parent_id' => $itCategory->id,
    'response' => "📱 CUSTOM APP DEVELOPMENT PRICE LIST 📱\nAndroid/iOS Apps: Mulai Rp 5jt (tergantung tingkat kerumitan fitur).",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

// --- KATEGORI 3: LAYANAN CASIR ---
$casirCategory = BotResponse::create([
    'keyword' => '3',
    'menu_label' => '📖 LAYANAN APLIKASI CASIR',
    'parent_id' => $mainMenu->id,
    'response' => 'Layanan support untuk pengguna aplikasi Casir:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 3
]);

BotResponse::create([
    'keyword' => 'download',
    'menu_label' => '🚀 Download Aplikasi Casir',
    'parent_id' => $casirCategory->id,
    'response' => '🚀 DOWNLOAD & INSTALL APLIKASI CASIR\nLink: https://bit.ly/download-casir-app',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'tutorial',
    'menu_label' => '📘 Panduan Penggunaan',
    'parent_id' => $casirCategory->id,
    'response' => '📖 PANDUAN PENGGUNAAN APLIKASI CASIR\nHalo! Untuk panduan lengkap silakan akses video tutorial kami di: https://rozitech.co.id/panduan-casir',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

// --- KATEGORI 4: INFO UMUM ---
$infoCategory = BotResponse::create([
    'keyword' => '4',
    'menu_label' => '📍 INFO KANTOR & PEMBAYARAN',
    'parent_id' => $mainMenu->id,
    'response' => 'Informasi umum mengenai Rozitech:',
    'is_exact_match' => true,
    'is_active' => true,
    'is_menu' => true,
    'group_enabled' => true,
    'sort_order' => 4
]);

BotResponse::create([
    'keyword' => 'alamat, lokasi',
    'menu_label' => '📍 Alamat Kantor Pusat',
    'parent_id' => $infoCategory->id,
    'response' => "📍 Alamat Kantor Pusat Rozitech:\nJl. Raya Peganden, Leran, Manyar, Gresik, Jawa Timur.\nGoogle Maps: https://maps.app.goo.gl/xxx",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 1
]);

BotResponse::create([
    'keyword' => 'cara bayar, rekening',
    'menu_label' => '💳 Cara Bayar / Rekening',
    'parent_id' => $infoCategory->id,
    'response' => "Untuk pembayaran layanan Rozitech:\n- BCA: 7415234155 a.n Fachrur Rozi\n- BRI: 621001017663537 a.n Fachrur Rozi\n\n*Wajib kirim bukti transfer nggih Kak.*",
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => true,
    'sort_order' => 2
]);

// 5. Default Fallback
BotResponse::create([
    'keyword' => 'default',
    'menu_label' => null,
    'response' => 'Halo, Saya R-Care. Maaf, saya tidak mengerti maksud Anda. Ketik *menu* untuk melihat layanan resmi Rozitech.',
    'is_exact_match' => false,
    'is_active' => true,
    'is_menu' => false,
    'group_enabled' => false,
    'sort_order' => 99
]);

echo "Bot responses data updated with CUSTOMER DB DATA successfully!\n";
