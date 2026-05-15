<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\KnnController;
use App\Http\Controllers\RuteController;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\OdcOdpController;
use App\Http\Controllers\authentications\LoginBasic;

// Auth Routes (Using template's basic login for now)
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::get('/auth/login-basic', [LoginBasic::class, 'index']);
Route::post('/login', [LoginBasic::class, 'authenticate'])->name('login.post');
Route::get('/auth/register-basic', [\App\Http\Controllers\authentications\RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::post('/auth/register-basic', [\App\Http\Controllers\authentications\RegisterBasic::class, 'store'])->name('auth-register-basic.store');
Route::post('/logout', function() {
    auth()->logout();
    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pelanggan
    Route::middleware('can:pelanggan_manage')->group(function() {
        Route::get('/pelanggan/card-massal', [PelangganController::class, 'cardMassal'])->name('pelanggan.card-massal');
        Route::get('/pelanggan/export', [PelangganController::class, 'export'])->name('pelanggan.export');
        Route::get('/pelanggan/{pelanggan}/card', [PelangganController::class, 'card'])->name('pelanggan.card');
        Route::resource('pelanggan', PelangganController::class);
        Route::resource('odc-odp', OdcOdpController::class);
        Route::post('pelanggan-import', [PelangganController::class, 'import'])->name('pelanggan.import');
        Route::get('map-pelanggan', [PelangganController::class, 'map'])->name('pelanggan.map');
        Route::post('pelanggan/{pelanggan}/toggle-status', [PelangganController::class, 'toggleStatus'])->name('pelanggan.toggle-status');
        Route::get('pelanggan/{pelanggan}/traffic', [PelangganController::class, 'traffic'])->name('pelanggan.traffic');

    });

    // Customer Routes
    Route::get('my-connection', [PelangganController::class, 'myConnection'])->name('pelanggan.my-connection');

    // KNN
    Route::middleware('can:knn_process')->group(function() {
        Route::get('knn', [KnnController::class, 'index'])->name('knn.index');
        Route::post('knn/process', [KnnController::class, 'process'])->name('knn.process');
        Route::post('knn/batch', [KnnController::class, 'batchProcess'])->name('knn.batch');
    });

    // Rute
    Route::middleware('can:rute_manage')->group(function() {
        Route::get('rute', [RuteController::class, 'index'])->name('rute.index');
        Route::post('rute/generate', [RuteController::class, 'generate'])->name('rute.generate');
        Route::get('rute/{rute}', [RuteController::class, 'show'])->name('rute.show');
        Route::post('rute-detail/{detail}/status', [RuteController::class, 'updateDetailStatus'])->name('rute.detail.status');
    });



    // Tiket
    Route::middleware('can:tiket_manage')->group(function() {
        Route::resource('tiket', TiketController::class);
        Route::post('tiket/{tiket}/status', [TiketController::class, 'updateStatus'])->name('tiket.status');
    });

    // Mikrotik
    Route::middleware('can:mikrotik_monitor')->group(function() {
        Route::get('mikrotik', [MikrotikController::class, 'index'])->name('mikrotik.index');
        Route::post('mikrotik', [MikrotikController::class, 'store'])->name('mikrotik.store');
        Route::get('mikrotik/{router}/edit', [MikrotikController::class, 'edit'])->name('mikrotik.edit');
        Route::put('mikrotik/{router}', [MikrotikController::class, 'update'])->name('mikrotik.update');
        Route::delete('mikrotik/{router}', [MikrotikController::class, 'destroy'])->name('mikrotik.destroy');
        Route::get('mikrotik/{router}/sync', [MikrotikController::class, 'sync'])->name('mikrotik.sync');
        Route::get('mikrotik/{router}/stats', [MikrotikController::class, 'stats'])->name('mikrotik.stats');
    });

    // Management Pengguna
    Route::middleware('can:user_manage')->group(function() {
        Route::post('users/reset-customer-passwords', [UserController::class, 'resetCustomerPasswords'])->name('users.reset-customer-passwords');
        Route::resource('users', UserController::class);
    });

    // Laporan
    Route::middleware('can:report_view')->group(function() {
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/tagihan', [LaporanController::class, 'tagihan'])->name('laporan.tagihan');
        Route::get('laporan/tagihan/export', [LaporanController::class, 'exportPdf'])->name('laporan.tagihan.export');
    });

    // Profile (All can view)
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Billing
    Route::get('billing', [\App\Http\Controllers\TagihanController::class, 'index'])->name('billing.index');
    Route::get('billing/{tagihan}/pay', [\App\Http\Controllers\PaymentController::class, 'getSnapToken'])->name('billing.pay');
    Route::post('billing/{tagihan}/confirm', [\App\Http\Controllers\TagihanController::class, 'confirmPayment'])->name('billing.confirm');
    Route::post('billing/{tagihan}/verify', [\App\Http\Controllers\TagihanController::class, 'verifikasi'])->name('billing.verify');
    Route::get('billing/sync', [\App\Http\Controllers\TagihanController::class, 'generateMonthlyBills'])->name('billing.sync');
    Route::put('billing/{tagihan}/amount', [\App\Http\Controllers\TagihanController::class, 'updateAmount'])->name('billing.amount.update');
    Route::put('billing/{tagihan}', [\App\Http\Controllers\TagihanController::class, 'update'])->name('billing.update');
    Route::delete('billing/delete-all', [\App\Http\Controllers\TagihanController::class, 'deleteAll'])->name('billing.delete-all');
    Route::delete('billing/{tagihan}', [\App\Http\Controllers\TagihanController::class, 'destroy'])->name('billing.destroy');
    Route::get('billing/{tagihan}/receipt', [\App\Http\Controllers\TagihanController::class, 'downloadReceipt'])->name('billing.receipt.pdf');
    
    // Settings
    Route::get('settings/payment', [\App\Http\Controllers\TagihanController::class, 'settings'])->name('settings.payment');
    Route::post('settings/payment', [\App\Http\Controllers\TagihanController::class, 'updateSettings'])->name('settings.payment.update');

    // Inventory
    Route::middleware('can:inventory_manage')->group(function() {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
        Route::put('inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::post('inventory/{inventory}/assign', [InventoryController::class, 'assign'])->name('inventory.assign');
    });

    // Scanner
    Route::get('scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('scan/process', [ScanController::class, 'process'])->name('scan.process');

    // Whatsapp Multi-Device Management
    Route::middleware('can:user_manage')->group(function() {
        Route::get('whatsapp/manager', [\App\Http\Controllers\WhatsappManagementController::class, 'index'])->name('whatsapp.index');
        Route::post('whatsapp/session/start', [\App\Http\Controllers\WhatsappManagementController::class, 'start'])->name('whatsapp.session.start');
        Route::post('whatsapp/session/pairing', [\App\Http\Controllers\WhatsappManagementController::class, 'pairing'])->name('whatsapp.session.pairing');
        Route::post('whatsapp/session/stop', [\App\Http\Controllers\WhatsappManagementController::class, 'stop'])->name('whatsapp.session.stop');
        Route::post('whatsapp/bot/start', [\App\Http\Controllers\WhatsappManagementController::class, 'startBotProcess'])->name('whatsapp.bot.start');
    });

    // Chatbot Management
    Route::get('bot/responses', [\App\Http\Controllers\BotResponseController::class, 'index'])->name('bot.index');
    Route::post('bot/responses', [\App\Http\Controllers\BotResponseController::class, 'store'])->name('bot.store');
    Route::put('bot/responses/{bot}', [\App\Http\Controllers\BotResponseController::class, 'update'])->name('bot.update');
    Route::delete('bot/responses/{bot}', [\App\Http\Controllers\BotResponseController::class, 'destroy'])->name('bot.destroy');

});

// Whatsapp Hook (Outside auth because it's called by the Node.js bot)
Route::post('whatsapp/webhook', [\App\Http\Controllers\WhatsappController::class, 'webhook']);
Route::post('whatsapp/status', [\App\Http\Controllers\WhatsappController::class, 'status']);
Route::post('whatsapp/train', [\App\Http\Controllers\WhatsappController::class, 'train']);

// Midtrans Callback (Outside auth)
Route::post('payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');

// Quick Scan Pay
Route::get('pay/{kode_pelanggan}', [\App\Http\Controllers\PaymentController::class, 'payById'])->name('payment.by-id');