<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DisableUnpaidServices extends Command
{
    protected $signature = 'billing:disable-unpaid';
    protected $description = 'Disable WiFi services for customers who have not paid by the isolir date';

    public function handle(\App\Services\MikrotikService $mikrotikService)
    {
        $enabled = \App\Models\Setting::get('billing_auto_isolir_enabled', '1');
        $isolirDate = (int) \App\Models\Setting::get('billing_isolir_date', '10');
        $isolirHour = (int) \App\Models\Setting::get('billing_isolir_hour', '12');

        if ($enabled != '1') {
            $this->info('Auto-isolation is disabled in settings.');
            return;
        }

        // Hanya jalankan jika tanggal hari ini >= tanggal isolir
        if (now()->day < $isolirDate) {
            $this->info("Hari ini tgl " . now()->day . ". Isolir dijadwalkan tgl {$isolirDate}. Dilewati.");
            return;
        }

        // Jika tepat di tanggal isolir, tunggu sampai jam yang ditentukan
        if (now()->day == $isolirDate && now()->hour < $isolirHour) {
            $this->info("Hari ini tgl isolir, tapi baru " . now()->format('H:i') . ". Menunggu jam {$isolirHour}:00.");
            return;
        }

        $this->info('Memeriksa tagihan yang belum dibayar...');

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Cari pelanggan yang punya tagihan UNPAID bulan ini dan masih aktif
        $unpaidPelanggan = \App\Models\Pelanggan::where('is_active', true)
            ->whereHas('tagihan', function ($query) use ($currentMonth, $currentYear) {
                $query->where('bulan', $currentMonth)
                      ->where('tahun', $currentYear)
                      ->where('status', 'unpaid');
            })->get();

        $waClient = new \App\Services\WhatsappClient();

        foreach ($unpaidPelanggan as $p) {
            $username = $p->mikrotik_username ?: $p->kode_pelanggan;
            if ($p->router && $username) {
                $this->info("Menonaktifkan layanan: {$p->nama_pelanggan} ({$username})");
                $success = $mikrotikService->setSecretStatus($p->router, $username, $p->mikrotik_type, true, $p->ip_address);

                if ($success) {
                    $p->update(['is_active' => false]);
                    $this->info("Berhasil dinonaktifkan.");

                    // Kirim notifikasi WA
                    if ($p->no_wa) {
                        try {
                            $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));
                            $message = "⚠️ *NOTIFIKASI ISOLIR*\n\n";
                            $message .= "Halo *{$p->nama_pelanggan}*,\n";
                            $message .= "Internet Anda telah *dinonaktifkan* karena tagihan bulan *{$monthName} {$currentYear}* sebesar *Rp " . number_format($p->harga_layanan) . "* belum dibayar.\n\n";
                            $message .= "Silakan lakukan pembayaran dan internet Anda akan *otomatis aktif kembali* setelah konfirmasi.\n\n";
                            $message .= "Ketik *Cek Tagihan* untuk melihat detail tagihan Anda.";
                            $waClient->sendMessage($p->no_wa, ['text' => $message]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Gagal kirim notif isolir: ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->error("Gagal menonaktifkan Mikrotik untuk {$p->nama_pelanggan}");
                }
            }
        }

        $this->info('Proses isolir selesai. Total: ' . $unpaidPelanggan->count() . ' pelanggan.');
    }
}
