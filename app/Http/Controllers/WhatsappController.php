<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\WhatsappTraining;
use App\Models\BotResponse;
use Illuminate\Support\Str;


class WhatsappController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Webhook for receiving messages from Baileys/Node.js Bot
     */
    public function webhook(Request $request)
    {
        Log::info('WhatsApp Webhook v3 (Group & Keyword Focus) - Time: ' . now());
        
        $remoteJid = $request->remoteJid;
        $isGroup = $request->isGroup ?? (str_contains($remoteJid, '@g.us'));
        $message = trim(strtolower($request->message ?? ''));
        $type = $request->type; // chat, image, etc.
        $sender = $request->sender; // e.g. 62812345678@s.whatsapp.net
        $phoneNumber = explode('@', $sender)[0];
        $cleanPhone = substr($phoneNumber, -10);

        // 0. Handle Admin Training Command (!train)
        if ($this->handleTrainingCommand($request->message, $remoteJid, $request->pushName, true)) {
            return response()->json(['status' => 'trained']);
        }

        // 1. Logic for Ping/Cek/Lokasi (Hardcoded Commands)
        $lowerMsg = strtolower($message);
        
        // Cek PING/CEK
        if (str_starts_with($lowerMsg, 'ping') || str_starts_with($lowerMsg, 'cek ')) {
            $targetStr = trim(str_ireplace(['ping', 'cek id ', 'cek '], '', $message));
            if (!empty($targetStr) && strlen($targetStr) <= 25) {
                return $this->handlePingCommand($targetStr, $remoteJid, $cleanPhone);
            }
        }

        // Cek LOKASI (GIS)
        if (str_starts_with($lowerMsg, 'lokasi ')) {
            $query = trim(str_ireplace('lokasi ', '', $message));
            if (!empty($query)) return $this->handleLokasiCommand($query, $remoteJid, 'gis');
        }

        // Cek LOK (Manual URL)
        if (str_starts_with($lowerMsg, 'lok ')) {
            $query = trim(str_ireplace('lok ', '', $message));
            if (!empty($query)) return $this->handleLokasiCommand($query, $remoteJid, 'url');
        }

        // 2. Keyword Matching from Database (Primary Logic)
        $botResponse = $this->findKeywordResponse($message, $isGroup);
        if ($botResponse) {
            $finalReply = $botResponse->response;
            
            // Ganti Placeholder
            $finalReply = str_replace(
                ['{Nama Customer}', '{Nama}', '{PushName}', '{Nomor WA}'], 
                [$request->pushName ?? 'Pelanggan', $request->pushName ?? 'Pelanggan', $request->pushName ?? 'Pelanggan', $cleanPhone], 
                $finalReply
            );

            // Handle Menu Response
            if ($botResponse->is_menu) {
                return $this->showMenu($remoteJid, $botResponse->id);
            }

            // Handle Special Placeholders
            if (str_contains($finalReply, '{cek_tagihan}')) {
                $finalReply = $this->handleCekTagihanPlaceholder($finalReply, $message, $remoteJid);
            }

            $formatted = $this->formatForWhatsapp($finalReply);
            $this->saveBotReply($remoteJid, $formatted);
            return response()->json(['reply' => $formatted]);
        }

        // 3. Logic for Image (Proof of payment)
        if ($type == 'image' && $request->has('media')) {
            return $this->handleImageVerification($request, $remoteJid, $message);
        }

        // 4. Fallback (AI or Default)
        $botNumber = env('WHATSAPP_BOT_NUMBER', '');
        $isMentioned = false;
        if ($request->has('mentionedJid')) {
            foreach ($request->mentionedJid as $jid) {
                if (str_contains($jid, $botNumber)) {
                    $isMentioned = true;
                    break;
                }
            }
        }

        if (!$isGroup || $isMentioned) {
            // AI Fallback
            $aiReply = $this->getAiResponse($message, $remoteJid, $request->pushName ?? 'Pelanggan');
            if ($aiReply) {
                $this->saveBotReply($remoteJid, $aiReply);
                return response()->json(['reply' => $aiReply]);
            }

            // Default Fallback (only for private chats)
            if (!$isGroup) {
                $fallback = \App\Models\BotResponse::where('keyword', 'like', '%default%')->where('is_active', true)->first();
                $reply = $fallback ? $this->formatForWhatsapp($fallback->response) : "🤖 *RT RW NET BOT*\nKetik *menu* untuk melihat bantuan.";
                $this->saveBotReply($remoteJid, $reply);
                return response()->json(['reply' => $reply]);
            }
        }

        // If it's a group and no keyword matched and not mentioned, stay silent
        return response()->json(['status' => 'ignored_no_match']);
    }

    /**
     * Helper to find response in DB
     */
    private function findKeywordResponse($message, $isGroup)
    {
        $botResponses = \App\Models\BotResponse::where('is_active', true)->get()->sort(function($a, $b) {
            if ($a->is_exact_match != $b->is_exact_match) return $b->is_exact_match <=> $a->is_exact_match;
            return strlen($b->keyword) <=> strlen($a->keyword);
        });

        foreach ($botResponses as $bot) {
            $keywords = array_filter(array_map('trim', explode(',', strtolower($bot->keyword))));
            
            foreach ($keywords as $kw) {
                if ($bot->is_exact_match) {
                    if ($message === $kw) return $bot;
                } else {
                    if (strlen($kw) <= 2) {
                        if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $message)) return $bot;
                    } else {
                        if (str_contains($message, $kw)) return $bot;
                    }
                }
            }
        }
        return null;
    }

    private function handlePingCommand($targetStr, $remoteJid, $cleanPhone)
    {
        Log::info("PING_DEBUG: TargetStr='$targetStr', Message='ping'");

        $router = \App\Models\Router::first(); 
        $host = $targetStr;
        $pelangganInfo = "";

        $targetPelanggan = Pelanggan::where(function($q) use ($targetStr) {
                                        $q->where('kode_pelanggan', '=', $targetStr)
                                          ->orWhere('mikrotik_username', '=', $targetStr)
                                          ->orWhere('ip_address', '=', $targetStr);
                                        if (is_numeric($targetStr)) $q->orWhere('id_pelanggan', '=', $targetStr);
                                    })->first();
        
        if ($targetPelanggan && $router) {
            $mUser = $targetPelanggan->mikrotik_username ?: $targetPelanggan->kode_pelanggan;
            
            // 1. Try to get IP from Mikrotik Active List
            $activeIp = $this->mikrotik->getPelangganActiveIp($router, $mUser, $targetPelanggan->mikrotik_type);
            
            if ($activeIp) {
                $host = $activeIp;
            } 
            // 2. Fallback for Static IP or Offline PPPOE (using DB IP)
            elseif (!empty($targetPelanggan->ip_address)) {
                $host = $targetPelanggan->ip_address;
            }

            // Pelanggan Info for the message
            $pelangganInfo = "Pelanggan: " . $targetPelanggan->nama_pelanggan . " (" . $targetPelanggan->kode_pelanggan . ")\n";
        }
        
        try {
            if ($router) {
                $results = $this->mikrotik->ping($router, $host);
                
                if ($results && is_array($results)) {
                    $received = 0;
                    $avgTime = 0;
                    $times = [];

                    foreach ($results as $res) {
                        // Check for various ways Mikrotik indicates success
                        $status = strtolower($res['status'] ?? '');
                        $hasTime = isset($res['time']);
                        
                        if ($status === 'ok' || str_contains($status, 'received') || $hasTime) {
                            $received++;
                            if ($hasTime) {
                                // Extract numeric value from time (e.g. "15ms" -> 15)
                                $timeVal = (int) preg_replace('/[^0-9]/', '', $res['time']);
                                if ($timeVal > 0) $times[] = $timeVal;
                            }
                        }
                    }

                    $isSuccess = ($received > 0);
                    $avgTime = !empty($times) ? round(array_sum($times) / count($times)) : 0;

                    $reply = "📍 *Hasil Ping (Mikrotik)*\n--------------------------\n";
                    if ($pelangganInfo) $reply .= $pelangganInfo;
                    $reply .= "Target: $targetStr ($host)\n";
                    
                    if ($targetPelanggan) {
                        $hasUnpaid = DB::table('tagihan')->where('id_pelanggan', $targetPelanggan->id_pelanggan)->whereIn('status', ['unpaid', 'belum_bayar'])->exists();
                        if ($targetPelanggan->is_active == 0 || $hasUnpaid) {
                            $reply .= "Status: 🔴 *TERISOLIR (BELUM BAYAR)*\n";
                        } else {
                            if ($isSuccess) {
                                $reply .= "Status: ✅ *ONLINE*\n";
                                $reply .= "Latency: {$avgTime}ms\n";
                                $reply .= "Packets: {$received}/4 Received\n";
                            } else {
                                $reply .= "Status: ❌ *OFFLINE (Request Timeout)*\n";
                            }
                        }
                        Cache::put('last_check_' . $remoteJid, $targetPelanggan->id_pelanggan, 3600);
                    } else {
                        if ($isSuccess) {
                            $reply .= "Status: ✅ *REACHABLE*\n";
                            $reply .= "Latency: {$avgTime}ms\n";
                        } else {
                            $reply .= "Status: ❌ *UNREACHABLE*\n";
                        }
                    }

                    $this->saveBotReply($remoteJid, $reply);
                    return response()->json(['reply' => $reply]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Ping Error: " . $e->getMessage());
        }

        return response()->json(['reply' => "⚠️ Maaf Kak, target \"$targetStr\" ($host) tidak merespon.\n\nHal ini bisa disebabkan karena:\n1. Perangkat pelanggan mati/cabut power.\n2. Kabel dropcore putus.\n3. Router sedang sibuk.\n\nSilakan coba lagi beberapa saat lagi."]);
    }

    private function handleLokasiCommand($query, $remoteJid, $mode = 'gis')
    {
        // 1. Prioritaskan pencarian EXACT pada Kode Pelanggan
        $exactMatch = Pelanggan::where('kode_pelanggan', $query)
            ->orWhere('kode_pelanggan', strtoupper($query))
            ->first();

        if ($exactMatch) {
            $p = $exactMatch;
            if ($mode === 'url') {
                if (!$p->maps_url) return response()->json(['reply' => "📍 Pelanggan *{$p->nama_pelanggan}* ({$p->kode_pelanggan}) ditemukan, tapi Link Google Maps belum diinput."]);
                $reply = "📍 *Lokasi Pelanggan (Manual Link)*\n\nNama: *{$p->nama_pelanggan}*\nKode: *{$p->kode_pelanggan}*\nAlamat: {$p->alamat}\n\n🗺️ *Google Maps:*\n{$p->maps_url}";
                return response()->json(['reply' => $reply]);
            } else {
                if (!$p->latitude || !$p->longitude) return response()->json(['reply' => "📍 Pelanggan *{$p->nama_pelanggan}* ({$p->kode_pelanggan}) ditemukan, tapi koordinat GIS belum ada."]);
                $mapsUrl = "https://www.google.com/maps?q={$p->latitude},{$p->longitude}";
                $reply = "📍 *Lokasi Pelanggan (GIS)*\n\nNama: *{$p->nama_pelanggan}*\nKode: *{$p->kode_pelanggan}*\nAlamat: {$p->alamat}\n\n🗺️ *Google Maps:*\n$mapsUrl";
                return response()->json(['reply' => $reply]);
            }
        }

        // 2. Jika tidak ada yang exact, baru cari partial (seperti sebelumnya)
        $results = Pelanggan::where('nama_pelanggan', 'like', "%$query%")
            ->orWhere('kode_pelanggan', 'like', "%$query%")
            ->orWhere('mikrotik_username', 'like', "%$query%")
            ->limit(5)->get();

        if ($results->isEmpty()) return response()->json(['reply' => "❌ Tidak ada pelanggan yang cocok dengan *$query*."]);

        if ($results->count() === 1) {
            $p = $results->first();
            // ... (sama seperti logika exact di atas, bisa di-refactor jika perlu, tapi biarkan dulu agar aman)
            if ($mode === 'url') {
                if (!$p->maps_url) return response()->json(['reply' => "📍 Pelanggan *{$p->nama_pelanggan}* ditemukan, tapi Link Google Maps belum diinput."]);
                $reply = "📍 *Lokasi Pelanggan (Manual Link)*\n\nNama: *{$p->nama_pelanggan}*\nKode: *{$p->kode_pelanggan}*\nAlamat: {$p->alamat}\n\n🗺️ *Google Maps:*\n{$p->maps_url}";
                return response()->json(['reply' => $reply]);
            } else {
                if (!$p->latitude || !$p->longitude) return response()->json(['reply' => "📍 Pelanggan *{$p->nama_pelanggan}* ditemukan, tapi koordinat GIS belum ada."]);
                $mapsUrl = "https://www.google.com/maps?q={$p->latitude},{$p->longitude}";
                $reply = "📍 *Lokasi Pelanggan (GIS)*\n\nNama: *{$p->nama_pelanggan}*\nKode: *{$p->kode_pelanggan}*\nAlamat: {$p->alamat}\n\n🗺️ *Google Maps:*\n$mapsUrl";
                return response()->json(['reply' => $reply]);
            }
        }

        $reply = "🔍 *Hasil Pencarian: \"$query\"*\n\n";
        foreach ($results as $i => $p) {
            $hasLok = ($p->latitude && $p->longitude) ? '✅' : '❌';
            $reply .= ($i + 1) . ". *{$p->nama_pelanggan}* ({$p->kode_pelanggan}) $hasLok\n";
        }
        return response()->json(['reply' => $reply]);
    }

    private function handleImageVerification($request, $remoteJid, $message)
    {
        $lastCheckedId = Cache::get('last_check_' . $remoteJid);
        $tagihan = null;
        if ($lastCheckedId) $tagihan = Tagihan::where('id_pelanggan', $lastCheckedId)->whereIn('status', ['unpaid', 'belum_bayar'])->latest()->first();

        if (!$tagihan) return response()->json(['reply' => "Maaf Kak, saya tidak menemukan tagihan aktif. Ketik *cek tagihan [KODE]* dulu ya!"]);

        $imageData = base64_decode($request->media);
        $fileName = 'bukti_' . $tagihan->id_tagihan . '_' . time() . '.jpg';
        if (!file_exists(storage_path('app/public/bukti_bayar'))) mkdir(storage_path('app/public/bukti_bayar'), 0777, true);
        file_put_contents(storage_path('app/public/bukti_bayar/' . $fileName), $imageData);
        $tagihan->update(['bukti_bayar' => 'bukti_bayar/' . $fileName]);

        // OCR logic simplified for brevity but functional
        $ocrText = $request->ocrText ?? '';
        $targetAmount = (string)((int)$tagihan->jumlah);
        if (str_contains(preg_replace('/[^0-9]/', '', $ocrText), $targetAmount)) {
            $tagihan->update(['status' => 'paid', 'paid_at' => now(), 'metode_pembayaran' => 'otomatis']);
            try {
                $waClient = new \App\Services\WhatsappClient();
                $waClient->sendReceipt($tagihan);
            } catch (\Exception $e) {}
            return response()->json(['reply' => "✅ VERIFIKASI BERHASIL! Layanan Anda sudah aktif kembali. Nota telah dikirim."]);
        }

        return response()->json(['reply' => "⚠️ Bukti diterima. Menunggu verifikasi manual (OCR tidak terbaca jelas)."]);
    }

    private function handleCekTagihanPlaceholder($finalReply, $message, $remoteJid)
    {
        $customerCode = trim(str_ireplace('cek tagihan', '', $message));
        if ($customerCode) {
            $customer = \App\Models\Pelanggan::where('kode_pelanggan', $customerCode)->first();
            if ($customer) {
                Cache::put('last_check_' . $remoteJid, $customer->id_pelanggan, 3600);
                $bills = $customer->tagihan()->where('status', '!=', 'paid')->get();
                if ($bills->count() > 0) {
                    $info = "Halo " . $customer->nama_pelanggan . ",\n\n*Tagihan Anda:*\n";
                    $total = 0;
                    foreach ($bills as $bill) {
                        $info .= "• " . $bill->bulan . " " . $bill->tahun . ": Rp " . number_format($bill->jumlah, 0, ',', '.') . "\n";
                        $total += $bill->jumlah;
                    }
                    $info .= "\n*Total: Rp " . number_format($total, 0, ',', '.') . "*\n\nKirim bukti bayar kesini ya!";
                    return str_replace('{cek_tagihan}', $info, $finalReply);
                }
                return str_replace('{cek_tagihan}', "Lunas! Anda tidak memiliki tagihan aktif.", $finalReply);
            }
            return str_replace('{cek_tagihan}', "Kode *$customerCode* tidak ditemukan.", $finalReply);
        }
        return str_replace('{cek_tagihan}', "Ketik *cek tagihan [KODE]*\nContoh: *cek tagihan AD20*", $finalReply);
    }

    public function train(Request $request)
    {
        $messages = $request->messages;
        if (!$messages || !is_array($messages)) {
            return response()->json(['success' => false, 'message' => 'No messages provided']);
        }

        $successCount = 0;
        foreach ($messages as $m) {
            try {
                // Normalize keys (handle both camelCase and snake_case)
                $remoteJid = $m['remote_jid'] ?? $m['remoteJid'] ?? null;

                // Abaikan pesan grup untuk training data
                if ($remoteJid && str_contains($remoteJid, '@g.us')) {
                    continue;
                }

                $messageContent = $m['message'] ?? null;
                $isFromMe = $m['is_from_me'] ?? $m['isFromMe'] ?? false;
                $pushName = $m['pushName'] ?? $m['sender_name'] ?? 'Unknown';

                if (!$remoteJid || !$messageContent) continue;

                // LOG UNTUK DEBUG
                \Log::info("Training Data Incoming: From $pushName - " . substr($messageContent, 0, 20) . "...");

                // Pastikan timestamp bukan array/objek yang tersisa
                $timestamp = $m['timestamp'] ?? time();
                if (is_array($timestamp) || is_object($timestamp)) {
                    $timestamp = time(); 
                }

                $client = new \App\Services\WhatsappClient();

                // === FITUR PELATIHAN AI (KHUSUS ADMIN) ===
                if ($this->handleTrainingCommand($messageContent, (string)$remoteJid, (string)$pushName, false)) {
                    continue;
                }

                \App\Models\WhatsappTraining::updateOrCreate(
                    [
                        'remote_jid' => (string)$remoteJid,
                        'timestamp' => \Carbon\Carbon::createFromTimestamp((int)$timestamp)->toDateTimeString(),
                        'message' => (string)$messageContent
                    ],
                    [
                        'type' => (string)($m['type'] ?? 'chat'),
                        'is_from_me' => (bool)$isFromMe,
                        'sender_name' => (string)$pushName,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Gagal simpan training: " . $e->getMessage());
                continue;
            }
        }

        return response()->json(['success' => true, 'count' => $successCount]);
    }

    private function getAiResponse($message, $remoteJid, $pushName = 'Pelanggan')
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $envModel = env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-lite-preview-02-05:free');
        $models = array_unique(array_merge([$envModel], [
            'google/gemini-2.0-flash-exp:free',
            'qwen/qwen-2.5-72b-instruct:free',
            'deepseek/deepseek-chat:free',
            'meta-llama/llama-3.1-8b-instruct:free'
        ]));

        if (!$apiKey) return null;

        // Load SOP Knowledge
        $sopPath = storage_path('app/sop_knowledge.txt');
        $sopContent = file_exists($sopPath) ? file_get_contents($sopPath) : 'Bantu pelanggan Rozitech dengan ramah.';

        // Load Dynamic Data (Bot Responses from DB)
        $dynamicBotData = BotResponse::where('is_active', true)->get()->map(function($item) {
            return "[Keyword: {$item->keyword}] JAWABAN: {$item->response}";
        })->implode("\n");

        // Load Context from Training Data (History)
        $history = WhatsappTraining::where('remote_jid', $remoteJid)
            ->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        $adminNum = env('WHATSAPP_ADMIN_NUMBER', '6282187827382');
        $systemInstruction = "Anda adalah R-Care, Customer Service AI resmi dari Rozitech (https://rozitech.co.id).
        Tugas Anda adalah melayani pelanggan Rozitech Network (Layanan Internet/WiFi).

        PENTING:
        - FOKUS HANYA PADA LAYANAN INTERNET ROZITECH.
        - JANGAN menyebutkan produk lain seperti HP, Laptop, atau barang elektronik.
        - Jika pelanggan bertanya tentang 'tombol' atau 'mana menu-nya', jelaskan bahwa menu muncul sebagai pilihan tombol/daftar di layar chat. Jika tidak muncul, minta mereka mengetik nomor pilihan yang tertera pada pesan menu.
        - JANGAN pernah mengira 'tombol' adalah tombol fisik pada modem kecuali pelanggan sedang dalam proses troubleshooting perangkat.
        
        KEPRIBADIAN:
        - Ramah, hangat, dan profesional.
        - Deteksi bahasa pelanggan (Indo atau Jawa). Balas sesuai bahasa mereka.

        KONTEKS BISNIS & SOP:
        $sopContent

        PENGETAHUAN TAMBAHAN (Database):
        $dynamicBotData

        ATURAN PENTING (WAJIB):
        - Gunakan List Bullet (•) atau penomoran manual (1, 2, 3).
        - *Tebalkan* harga, nomor rekening, dan info krusial.
        - Akhiri dengan Link Admin: https://wa.me/$adminNum dan Watermark _Respons by R-Care AI_";

        $messages = [['role' => 'system', 'content' => $systemInstruction]];

        foreach ($history as $h) {
            $messages[] = [
                'role' => $h->is_from_me ? 'assistant' : 'user',
                'content' => $h->message
            ];
        }

        // Add current message if not already in history
        $messages[] = ['role' => 'user', 'content' => $message];

        // Try models one by one
        foreach ($models as $model) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => 'https://rozitech.co.id',
                    'X-Title' => 'Rozitech R-Care Bot',
                ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $content = $result['choices'][0]['message']['content'] ?? null;
                    if ($content) {
                        Log::info("AI Response success via OpenRouter ($model) for: " . substr($message, 0, 50));
                        return $content;
                    }
                } else {
                    Log::error("OpenRouter Error ($model): " . $response->body());
                    continue; 
                }
            } catch (\Exception $e) {
                Log::error("AI Error ($model): " . $e->getMessage());
                continue; 
            }
        }

        // === JALUR PENYELAMAT: POLLINATIONS AI (GET - RINGKAS) ===
        Log::warning("OpenRouter Gagal. Mencoba Pollinations GET...");
        try {
            $encodedPrompt = urlencode($message);
            $encodedSystem = urlencode(substr($systemInstruction, 0, 500)); // Batasi panjang instruksi
            $url = "https://text.pollinations.ai/{$encodedPrompt}?system={$encodedSystem}&model=openai";
            
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $reply = $response->body();
                
                // --- PROSES PEMBERSIHAN WATERMARK/IKLAN ---
                if ($reply) {
                    // Hapus baris yang mengandung kata-kata iklan
                    $lines = explode("\n", $reply);
                    $cleanLines = array_filter($lines, function($line) {
                        $trimmed = trim($line);
                        $lowered = strtolower($trimmed);
                        return !str_contains($lowered, 'pollinations.ai') && 
                               !str_contains($lowered, '*ad*') && 
                               !str_contains($lowered, 'powered by') &&
                               !str_contains($lowered, 'support pollination') &&
                               $trimmed !== '---' && 
                               $trimmed !== '--' &&
                               $trimmed !== '';
                    });
                    $reply = trim(implode("\n", $cleanLines));
                }

                if ($reply && strlen($reply) > 5) {
                    return $reply;
                }
            }
        } catch (\Exception $e) {
            Log::error("Pollinations GET Error: " . $e->getMessage());
        }

        Log::warning("Semua jalur AI gagal untuk: " . $message);
        return null;
    }

    /**
     * Handle bot status updates (connected, disconnected, etc.)
     */
    public function status(Request $request)
    {
        Log::info('WhatsApp Status Update:', $request->all());
        
        $sessionId = $request->sessionId;
        $status    = $request->status;
        $user      = $request->user;

        // You can store this in DB or Cache to show in UI
        Cache::put("whatsapp_session_{$sessionId}_status", [
            'status' => $status,
            'user'   => $user,
            'last_update' => now()->toDateTimeString()
        ], 86400);

        return response()->json(['success' => true]);
    }

    /**
     * Save bot's own reply to history for AI context synchronization
     */
    private function saveBotReply($remoteJid, $message)
    {
        try {
            \App\Models\WhatsappTraining::create([
                'remote_jid' => (string)$remoteJid,
                'message' => (string)$message,
                'timestamp' => now()->toDateTimeString(),
                'type' => 'chat',
                'is_from_me' => true,
                'sender_name' => 'R-Care AI'
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to save bot reply to history: " . $e->getMessage());
        }
    }

    /**
     * Handle !train command from admin
     */
    private function handleTrainingCommand($message, $sender, $pushName, $shouldReply = true)
    {
        $incomingMsg = trim($message);
        if (preg_match('/^!train\s+/i', $incomingMsg)) {
            // DYNAMIC ADMIN CHECK
            $adminNumbers = explode(',', env('WHATSAPP_ADMIN_NUMBER', '6282187827382'));
            $adminIds = explode(',', env('WHATSAPP_ADMIN_IDS', '117849758691352,251749826822171'));
            $adminNames = explode(',', strtolower(env('WHATSAPP_ADMIN_NAMES', 'rozitech,kang digital,admin')));
            
            $senderClean = preg_replace('/[^0-9]/', '', $sender);
            $pushNameLower = strtolower($pushName);
            
            $isAdmin = false;
            
            // Check by Number
            foreach ($adminNumbers as $num) {
                if (str_contains($senderClean, trim($num))) {
                    $isAdmin = true;
                    break;
                }
            }
            
            // Check by ID
            if (!$isAdmin) {
                foreach ($adminIds as $id) {
                    if (str_contains($sender, trim($id))) {
                        $isAdmin = true;
                        break;
                    }
                }
            }
            
            // Check by Name
            if (!$isAdmin) {
                foreach ($adminNames as $name) {
                    if (str_contains($pushNameLower, trim($name))) {
                        $isAdmin = true;
                        break;
                    }
                }
            }

            if ($isAdmin) {
                $content = preg_replace('/^!train\s+/i', '', $incomingMsg);
                $parts = explode('|', $content, 2);
                
                if (count($parts) == 2) {
                    $keyword = trim($parts[0]);
                    $response = trim($parts[1]);
                    
                    // Store to DB (AI uses this as knowledge)
                    \App\Models\BotResponse::updateOrCreate(
                        ['keyword' => $keyword],
                        ['response' => $response, 'is_active' => true, 'is_exact_match' => true]
                    );
                    
                    // Send confirmation message
                    if ($shouldReply) {
                        try {
                            $client = new \App\Services\WhatsappClient();
                            $client->sendMessage($sender, [
                                'text' => "*Berhasil Melatih R-Care (Secara Dinamis)!* ✅\n\n*Keyword:* $keyword\n*Jawaban:* [Tersimpan]\n\nData sudah aktif di Database & AI nggih Kak."
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to send training confirmation: " . $e->getMessage());
                        }
                    }
                    
                    return true;
                }
            } else {
                Log::warning("Unauthorized training attempt from: $sender (Name: $pushName). If this is you, please add your ID/Name to .env");
            }
        }
        return false;
    }

    /**
     * Tampilkan menu secara dinamis (IM3 Style - List Message)
     */
    private function showMenu($remoteJid, $parentId = null)
    {
        $query = BotResponse::where('is_active', true);
        
        if ($parentId) {
            $query->where('parent_id', $parentId);
            $parent = BotResponse::find($parentId);
            $title = strtoupper($parent->keyword);
            $text = $parent->response;
        } else {
            $query->whereNull('parent_id')->where('is_menu', true);
            $title = "MENU UTAMA R-CARE";
            $text = "Selamat datang di layanan R-Care AI. Silakan pilih menu di bawah ini untuk memulai.";
        }

        $menus = $query->orderBy('sort_order')->get();

        if ($menus->isEmpty()) {
            return response()->json(['reply' => "Maaf, menu belum tersedia."]);
        }

        // Decide whether to use List or Buttons
        if ($menus->count() > 0 && $menus->count() <= 3) {
            $textFallback = $text . "\n\n*PILIHAN MENU:*\n";
            $buttons = [];
            foreach ($menus as $menu) {
                $label = strtoupper($menu->menu_label ?: explode(',', $menu->keyword)[0]);
                $textFallback .= "👉 Ketik *" . explode(',', $menu->keyword)[0] . "* untuk " . $label . "\n";
                
                $buttons[] = [
                    'buttonId' => explode(',', $menu->keyword)[0],
                    'buttonText' => ['displayText' => $label],
                    'type' => 1
                ];
            }

            $buttonMessage = [
                'text' => $textFallback . "\n_Silakan pilih tombol di bawah ini (atau ketik angkanya):_",
                'footer' => "R-Care AI • Rozitech",
                'buttons' => $buttons,
                'headerType' => 1
            ];

            $this->saveBotReply($remoteJid, "Menampilkan Menu: " . $title . "\n" . $textFallback);
            return response()->json($buttonMessage);
        } else {
            // Build List Message for Baileys (More than 3 items)
            $textFallback = $text . "\n\n*PILIHAN MENU:*\n";
            $rows = [];
            foreach ($menus as $menu) {
                $label = strtoupper($menu->menu_label ?: explode(',', $menu->keyword)[0]);
                $textFallback .= "👉 Ketik *" . explode(',', $menu->keyword)[0] . "* untuk " . $label . "\n";
                
                $rows[] = [
                    'title' => $label,
                    'rowId' => explode(',', $menu->keyword)[0],
                    'description' => Str::limit(strip_tags($menu->response), 50)
                ];
            }

            if ($parentId) {
                $rows[] = [
                    'title' => 'KEMBALI KE MENU UTAMA',
                    'rowId' => 'menu',
                    'description' => 'Lihat layanan utama kami'
                ];
            }

            $listMessage = [
                'text' => $textFallback . "\n_Silakan pilih menu di bawah ini (atau ketik pilihannya):_",
                'footer' => "R-Care AI • Rozitech",
                'title' => "📍 " . $title,
                'buttonText' => "Klik Pilih Menu",
                'sections' => [
                    [
                        'title' => "Silakan Pilih",
                        'rows' => $rows
                    ]
                ]
            ];

            $this->saveBotReply($remoteJid, "Menampilkan List Menu: " . $title . "\n" . $textFallback);
            return response()->json($listMessage);
        }
    }

    /**
     * Convert Markdown-like tables and common formatting to WhatsApp-friendly text
     */
    private function formatForWhatsapp($text)
    {
        if (empty($text)) return $text;

        // 1. Convert Markdown Tables to Lists
        // Matches | cell | cell | ... |
        $lines = explode("\n", $text);
        $formattedLines = [];
        $inTable = false;
        $tableData = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (preg_match('/^\|.*\|$/', $trimmedLine)) {
                // Table header separator |---|---|
                if (preg_match('/^[|:\-\s]+$/', $trimmedLine)) {
                    continue;
                }
                
                $inTable = true;
                $cells = array_map('trim', explode('|', trim($trimmedLine, '|')));
                $tableData[] = $cells;
            } else {
                if ($inTable) {
                    // Process collected table data
                    foreach ($tableData as $rowIndex => $row) {
                        if ($rowIndex === 0) {
                            // Header
                            $formattedLines[] = "*" . implode(" | ", $row) . "*";
                        } else {
                            // Row
                            $formattedLines[] = "• " . implode(": ", $row);
                        }
                    }
                    $formattedLines[] = ""; // Empty line after table
                    $tableData = [];
                    $inTable = false;
                }
                $formattedLines[] = $line;
            }
        }

        // Finalize table if text ends with one
        if ($inTable) {
            foreach ($tableData as $rowIndex => $row) {
                if ($rowIndex === 0) {
                    $formattedLines[] = "*" . implode(" | ", $row) . "*";
                } else {
                    $formattedLines[] = "• " . implode(": ", $row);
                }
            }
        }

        $result = implode("\n", $formattedLines);

        // 2. Fix excessive newlines
        $result = preg_replace("/\n{3,}/", "\n\n", $result);

        return trim($result);
    }
}
