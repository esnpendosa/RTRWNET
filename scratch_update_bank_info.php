<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Setting;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bankInfo = "Bank: BCA\nNo. Rekening: 7415941111\nAtas Nama: Rozitech Multimedia Indonesia";

Setting::set('manual_bank_info', $bankInfo, 'payment');
Setting::set('manual_payment_methods', 'Transfer Bank,Cash,Titip Teknisi', 'payment');

echo "Informasi Rekening berhasil diperbarui di database!";
