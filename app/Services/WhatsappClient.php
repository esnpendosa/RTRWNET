<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappClient
{
    protected $baseUrl;

    public function __construct()
    {
        $port = env('BOT_PORT', 3000);
        $this->baseUrl = "http://127.0.0.1:$port";
    }

    protected function secret()
    {
        return env('BOT_SECRET', 'rozitech-bot-secret-2024');
    }

    public function sendMessage($phone, $data)
    {
        Log::info("WhatsappClient: Attempting to send message to $phone via " . $this->baseUrl);
        try {
            $message = is_array($data) ? ($data['text'] ?? '') : $data;
            
            $response = Http::timeout(15)
                ->withHeaders(['X-Bot-Secret' => $this->secret()])
                ->post($this->baseUrl . '/send-message', [
                    'phone'   => $phone,
                    'message' => $message
                ]);

            if (!$response->successful()) {
                Log::error("WhatsappClient Error Response: " . $response->status() . " - " . $response->body());
                return false;
            }

            Log::info("WhatsappClient: Message sent successfully to $phone");
            return true;
        } catch (\Exception $e) {
            Log::error("WhatsappClient Connection Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendFile($phone, $fileContent, $filename, $mimetype = 'application/pdf', $caption = '')
    {
        Log::info("WhatsappClient: Attempting to send file $filename to $phone");
        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-Bot-Secret' => $this->secret()])
                ->post($this->baseUrl . '/send-message', [
                    'phone'    => $phone,
                    'media'    => base64_encode($fileContent),
                    'filename' => $filename,
                    'mimetype' => $mimetype,
                    'caption'  => $caption
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsappClient File Send Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendFileUrl($phone, $url, $filename, $mimetype = 'application/pdf', $caption = '')
    {
        Log::info("WhatsappClient: Attempting to send file via URL $url to $phone");
        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-Bot-Secret' => $this->secret()])
                ->post($this->baseUrl . '/send-message', [
                    'phone'    => $phone,
                    'url'      => $url,
                    'filename' => $filename,
                    'mimetype' => $mimetype,
                    'caption'  => $caption
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsappClient File URL Send Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendReceipt(\App\Models\Tagihan $tagihan, $isManual = false)
    {
        $pelanggan = $tagihan->pelanggan;
        if (!$pelanggan || !$pelanggan->no_wa) return false;

        $monthName = date('F', mktime(0, 0, 0, $tagihan->bulan, 10));
        $amount = number_format($tagihan->jumlah, 0, ',', '.');
        $paidAt = $tagihan->paid_at ? $tagihan->paid_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i');
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('content.billing.receipt-pdf', compact('tagihan'))
            ->setOption('isRemoteEnabled', true);
        $pdfContent = $pdf->output();
        $filename = 'Nota-' . $pelanggan->kode_pelanggan . '-' . $tagihan->bulan . '-' . $tagihan->tahun . '.pdf';

        // Save to public storage for URL access (Manual save to avoid finfo error)
        $storagePath = 'bukti_bayar/' . $filename;
        $fullPath = storage_path('app/public/' . $storagePath);
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        file_put_contents($fullPath, $pdfContent);
        
        $fileUrl = asset('storage/' . $storagePath);

        if ($isManual) {
            $caption = "✅ *PEMBAYARAN DIVERIFIKASI ADMIN*\n\n";
            $caption .= "Halo *{$pelanggan->nama_pelanggan}*,\n";
            $caption .= "Tagihan Anda untuk periode *{$monthName} {$tagihan->tahun}* sebesar *Rp {$amount}* telah berhasil diverifikasi oleh Admin.\n\n";
        } else {
            $caption = "✅ *VERIFIKASI OTOMATIS BERHASIL*\n\n";
            $caption .= "Halo *{$pelanggan->nama_pelanggan}*,\n";
            $caption .= "Pembayaran Anda untuk periode *{$monthName} {$tagihan->tahun}* sebesar *Rp {$amount}* telah berhasil diverifikasi secara otomatis.\n\n";
        }

        $caption .= "*Status:* LUNAS\n";
        $caption .= "*Waktu Bayar:* {$paidAt}\n\n";
        $caption .= "Layanan internet Anda telah aktif sepenuhnya. Berikut kami lampirkan *Nota Pembayaran Digital (PDF)* sebagai bukti pembayaran yang sah.\n\n";
        $caption .= "Terima kasih telah berlangganan! 🙏";

        // Try sending via URL first
        return $this->sendFileUrl($pelanggan->no_wa, $fileUrl, $filename, 'application/pdf', $caption);
    }

    public function getSessions()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/sessions');
            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function startSession($id)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Bot-Secret' => env('BOT_SECRET', 'rozitech-bot-secret-2024')])
                ->post($this->baseUrl . '/session/start', ['id' => $id]);
            return $response->json();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getPairingCode($id, $phone)
    {
        try {
            $response = Http::timeout(35) // bot butuh ~18 detik
                ->withHeaders(['X-Bot-Secret' => $this->secret()])
                ->post($this->baseUrl . '/session/pairing', [
                    'id'    => $id,
                    'phone' => $phone
                ]);
            return $response->json();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function stopSession($id)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Bot-Secret' => $this->secret()])
                ->post($this->baseUrl . '/session/stop', ['id' => $id]);
            return $response->json();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
