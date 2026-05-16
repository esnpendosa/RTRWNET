<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnablePaidServices extends Command
{
    protected $signature = 'billing:enable-paid';
    protected $description = 'Enable WiFi services for customers who have paid their bill';

    public function handle(\App\Services\MikrotikService $mikrotikService)
    {
        $this->info('Memeriksa tagihan yang sudah dibayar untuk mengaktifkan layanan...');

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Cari pelanggan yang tidak aktif tapi sudah tidak punya tunggakan lagi (bulan ini atau sebelumnya)
        $paidPelanggan = \App\Models\Pelanggan::where('is_active', false)
            ->whereDoesntHave('tagihan', function ($query) use ($currentMonth, $currentYear) {
                $query->where('status', 'unpaid')
                      ->where(function($q) use ($currentMonth, $currentYear) {
                          $q->where('tahun', '<', $currentYear)
                            ->orWhere(function($sq) use ($currentMonth, $currentYear) {
                                $sq->where('tahun', $currentYear)
                                   ->where('bulan', '<=', $currentMonth);
                            });
                      });
            })->get();

        if ($paidPelanggan->isEmpty()) {
            $this->info('Tidak ada pelanggan yang perlu diaktifkan.');
            return;
        }

        $waClient = new \App\Services\WhatsappClient();

        foreach ($paidPelanggan as $p) {
            $username = $p->mikrotik_username ?: $p->kode_pelanggan;
            if ($p->router && $username) {
                $this->info("Mengaktifkan layanan: {$p->nama_pelanggan} ({$username})");
                $success = $mikrotikService->setSecretStatus($p->router, $username, $p->mikrotik_type, false, $p->ip_address);

                if ($success) {
                    $p->update(['is_active' => true]);
                    $this->info("Berhasil diaktifkan.");

                    // Kirim notifikasi WA
                    if ($p->no_wa) {
                        try {
                            $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));
                            $message = "✅ *INTERNET AKTIF KEMBALI*\n\n";
                            $message .= "Halo *{$p->nama_pelanggan}*,\n";
                            $message .= "Pembayaran tagihan bulan *{$monthName} {$currentYear}* Anda telah dikonfirmasi.\n\n";
                            $message .= "Internet Anda sekarang sudah *aktif kembali*. 🎉\n\n";
                            $message .= "Terima kasih telah berlangganan layanan kami!";
                            $waClient->sendMessage($p->no_wa, ['text' => $message]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Gagal kirim notif aktivasi: ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->error("Gagal mengaktifkan Mikrotik untuk {$p->nama_pelanggan}");
                }
            }
        }

        $this->info('Proses aktivasi selesai. Total: ' . $paidPelanggan->count() . ' pelanggan.');
    }
}
