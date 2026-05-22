<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\Pelanggan;
use App\Services\WhatsappClient;

class SendBillingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp reminder for unpaid bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enabled = Setting::get('billing_reminder_enabled', '1');
        if ($enabled != '1') {
            $this->info('Billing reminder is disabled in settings.');
            return;
        }

        $reminderDate = (int) Setting::get('billing_reminder_date', '5');
        
        if (now()->day != $reminderDate) {
            $this->info("Today is " . now()->day . ". Reminder is scheduled for day {$reminderDate}. Skipping...");
            return;
        }

        $this->info('Starting billing reminders...');
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get active customers with unpaid bills for the current month
        $unpaidPelanggan = Pelanggan::where('is_active', true)
            ->whereHas('tagihan', function ($query) use ($currentMonth, $currentYear) {
                $query->where('status', 'unpaid')
                      ->where('bulan', $currentMonth)
                      ->where('tahun', $currentYear);
            })->with(['tagihan' => function($q) use ($currentMonth, $currentYear) {
                $q->where('status', 'unpaid')
                  ->where('bulan', $currentMonth)
                  ->where('tahun', $currentYear);
            }])->get();

        $waClient = new WhatsappClient();
        $sentCount = 0;

        foreach ($unpaidPelanggan as $p) {
            if ($p->no_wa && $p->tagihan->count() > 0) {
                $tagihan = $p->tagihan->first();
                $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));
                
                $message = "🔔 *PENGINGAT TAGIHAN*\n\n";
                $message .= "Halo *" . $p->kode_pelanggan . "* " . $p->nama_pelanggan . ",\n\n";
                $message .= "Kami mengingatkan bahwa tagihan internet Anda untuk periode *" . $monthName . " " . $currentYear . "* sebesar *Rp " . number_format($tagihan->jumlah) . "* masih berstatus *BELUM BAYAR*.\n\n";
                $message .= "Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo agar layanan internet Anda tidak terputus.\n\n";
                $message .= "Ketik *Cek Tagihan* untuk melihat detail pembayaran.\n\n";
                $message .= "Abaikan pesan ini jika Anda sudah melakukan pembayaran. Terima kasih.";

                try {
                    $waClient->sendMessage($p->no_wa, ['text' => $message]);
                    $sentCount++;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim reminder tagihan: ' . $e->getMessage());
                }
            }
        }

        $this->info("Successfully sent $sentCount reminders for $currentMonth/$currentYear.");
    }
}
