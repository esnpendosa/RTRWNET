<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $user = auth()->user();
        $query = Tagihan::with('pelanggan');

        if ($search) {
            $query->whereHas('pelanggan', function ($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        // If user is a customer, only show their own bills
        if ($user->id_role == 4) {
            $query->whereHas('pelanggan', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        }

        $tagihan = $query->latest()->get();
        $allPelanggan = Pelanggan::where('is_active', true)->orderBy('nama_pelanggan')->get();
        return view('content.billing.index', compact('tagihan', 'allPelanggan'));
    }

    public function updateAmount(Request $request, Tagihan $tagihan)
    {
        if (auth()->user()->id_role != 1) abort(403);

        $request->validate([
            'jumlah' => 'required|numeric|min:0',
        ]);

        $tagihan->update([
            'jumlah' => $request->jumlah,
        ]);

        return back()->with('success', 'Jumlah tagihan berhasil diperbarui.');
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        if (auth()->user()->id_role != 1) abort(403);

        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'jumlah' => 'required|numeric|min:0',
            'status' => 'required|in:unpaid,paid,pending,cancelled',
            'created_at' => 'nullable|date',
        ]);

        $oldStatus = $tagihan->status;
        $tagihan->update($request->all());

        if ($oldStatus !== 'paid' && $tagihan->status === 'paid') {
            $tagihan->update(['paid_at' => now()]);
            
            $pelanggan = $tagihan->pelanggan;
            if ($pelanggan && $pelanggan->id_router) {
                $mikrotikService = app(\App\Services\MikrotikService::class);
                $mikrotikService->setSecretStatus($pelanggan->router, $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan, $pelanggan->mikrotik_type, false, $pelanggan->ip_address);
                $pelanggan->update(['is_active' => true]);
            }

            // Kirim Nota
            if ($pelanggan && $pelanggan->no_wa) {
                try {
                    $waClient = new \App\Services\WhatsappClient();
                    $waClient->sendReceipt($tagihan, true);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim nota WA dari update: ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Detail tagihan berhasil diperbarui.');
    }

    public function destroy(Tagihan $tagihan)
    {
        if (auth()->user()->id_role != 1) abort(403);
        $tagihan->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

    public function deleteAll()
    {
        if (auth()->user()->id_role != 1) abort(403);
        Tagihan::truncate();
        return back()->with('success', 'Semua data tagihan berhasil dikosongkan.');
    }

    public function generateMonthlyBills(Request $request)
    {
        $currentMonth = $request->bulan ?? now()->month;
        $currentYear = $request->tahun ?? now()->year;

        $query = Pelanggan::where('is_active', true);

        if ($request->id_pelanggan) {
            $query->where('id_pelanggan', $request->id_pelanggan);
        } elseif ($request->date_start && $request->date_end) {
            $query->whereBetween('billing_date', [$request->date_start, $request->date_end]);
        }

        $pelanggans = $query->get();
        $generatedCount = 0;

        $waClient = new \App\Services\WhatsappClient();
        foreach ($pelanggans as $p) {
            // Check if bill already exists for this month
            $exists = Tagihan::where('id_pelanggan', $p->id_pelanggan)
                ->where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->exists();

            if (!$exists && $p->harga_layanan > 0) {
                Tagihan::create([
                    'id_pelanggan' => $p->id_pelanggan,
                    'bulan' => $currentMonth,
                    'tahun' => $currentYear,
                    'jumlah' => $p->harga_layanan,
                    'status' => 'unpaid',
                    'created_at' => $request->created_at ?? now(),
                ]);
                $generatedCount++;

                // Kirim Notifikasi WA jika nomor WA ada
                if ($p->no_wa) {
                    try {
                        $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));
                        $message = "🔔 *PEMBERITAHUAN TAGIHAN BARU*\n\n";
                        $message .= "Halo *" . $p->kode_pelanggan . "* " . $p->nama_pelanggan . ",\n\n";
                        $message .= "Tagihan internet Anda untuk periode *" . $monthName . " " . $currentYear . "* telah terbit pembayaran maximal per tgl 10.\n\n";
                        $message .= "Jumlah: *Rp " . number_format($p->harga_layanan) . "*\n";
                        $message .= "Status: *BELUM BAYAR*\n\n";
                        $message .= "Silakan lakukan pembayaran agar layanan tetap aktif.\n";
                        $message .= "Ketik *Cek Tagihan* untuk melihat detail pembayaran.\n\n";
                        $message .= "Pembayaran Melalui Rekening  :\n";
                        $message .= "BRI = 621001017663537\n";
                        $message .= "BCA = 7415234155\n";
                        $message .= "Dana = 082187827382\n\n";
                        $message .= "Semua AN/ FACHRUR ROZI\n\n";
                        $message .= "Setelah Melakukan Pembayaran Silahkan Screenshoot / Konfirmasi Pembayaran Melalui Whatsapp wa.me/+6285604118932";

                        $waClient->sendMessage($p->no_wa, ['text' => $message]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi tagihan baru: ' . $e->getMessage());
                    }
                }
            }
        }

        return back()->with('success', "Berhasil membuat {$generatedCount} tagihan baru untuk periode bulan ini.");
    }

    public function confirmPayment(Request $request, Tagihan $tagihan)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string',
            'bukti_bayar' => 'required|image|max:2048',
        ]);

        $data = [
            'metode_pembayaran' => $request->metode_pembayaran,
            'status' => 'unpaid', // Tetap unpaid, UI akan mendeteksi bukti_bayar untuk menampilkan 'Menunggu Verifikasi'
        ];

        if ($request->hasFile('bukti_bayar')) {
            $path = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
            $data['bukti_bayar'] = $path;
        }

        $tagihan->update($data);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }

    public function verifikasi(Request $request, Tagihan $tagihan)
    {
        // Only admin should access this
        if (auth()->user()->id_role != 1) {
            abort(403);
        }

        $tagihan->update([
            'status' => 'paid',
            'paid_at' => $request->paid_at ?? now(),
            'catatan_admin' => $request->catatan_admin
        ]);

        $pelanggan = $tagihan->pelanggan;
        if ($pelanggan && $pelanggan->id_router) {
            $mikrotikService = app(\App\Services\MikrotikService::class);
            $mikrotikService->setSecretStatus($pelanggan->router, $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan, $pelanggan->mikrotik_type, false, $pelanggan->ip_address);
            $pelanggan->update(['is_active' => true]);
        }

        // Kirim Notifikasi WA jika nomor WA ada
        if ($pelanggan && $pelanggan->no_wa) {
            try {
                $waClient = new \App\Services\WhatsappClient();
                $waClient->sendReceipt($tagihan, true);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi WA: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Tagihan berhasil diverifikasi dan layanan diaktifkan.');
    }

    public function settings()
    {
        if (auth()->user()->id_role != 1) abort(403);
        
        return view('content.billing.settings');
    }

    public function updateSettings(Request $request)
    {
        if (auth()->user()->id_role != 1) abort(403);

        \App\Models\Setting::set('payment_gateway_enabled', $request->gateway_enabled ? '1' : '0', 'payment');
        \App\Models\Setting::set('midtrans_merchant_id', trim($request->midtrans_merchant_id), 'payment');
        \App\Models\Setting::set('midtrans_client_key', trim($request->midtrans_client_key), 'payment');
        \App\Models\Setting::set('midtrans_server_key', trim($request->midtrans_server_key), 'payment');
        \App\Models\Setting::set('midtrans_is_production', $request->midtrans_is_production ? '1' : '0', 'payment');
        
        \App\Models\Setting::set('payment_fee', $request->payment_fee ?? '0', 'payment');

        \App\Models\Setting::set('manual_payment_enabled', $request->manual_enabled ? '1' : '0', 'payment');
        \App\Models\Setting::set('manual_payment_methods', $request->manual_methods, 'payment');
        \App\Models\Setting::set('manual_bank_info', $request->bank_info, 'payment');

        // New Automation Settings
        \App\Models\Setting::set('billing_generate_date', $request->billing_generate_date ?? '1', 'automation');
        \App\Models\Setting::set('billing_start_date', $request->billing_start_date ?? '1', 'automation');
        \App\Models\Setting::set('billing_isolir_date', $request->billing_isolir_date ?? '10', 'automation');
        \App\Models\Setting::set('billing_isolir_hour', $request->billing_isolir_hour ?? '12', 'automation');
        \App\Models\Setting::set('billing_auto_isolir_enabled', $request->auto_isolir_enabled ? '1' : '0', 'automation');

        return back()->with('success', 'Pengaturan pembayaran dan otomatisasi berhasil diperbarui.');
    }

    public function downloadReceipt(Tagihan $tagihan)
    {
        // For security, maybe check if user is admin or the owner of the bill
        $user = auth()->user();
        if ($user && $user->id_role != 1 && $tagihan->pelanggan->id_user != $user->id) {
            abort(403);
        }

        if ($tagihan->status !== 'paid') {
            return back()->with('error', 'Nota hanya tersedia untuk tagihan yang sudah lunas.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('content.billing.receipt-pdf', compact('tagihan'))
            ->setOption('isRemoteEnabled', true);
        
        $fileName = 'Nota-' . $tagihan->pelanggan->kode_pelanggan . '-' . date('M-Y', mktime(0, 0, 0, $tagihan->bulan, 10)) . '.pdf';
        
        return $pdf->download($fileName);
    }
    public function runIsolirSync(Request $request)
    {
        if (auth()->user()->id_role != 1) abort(403);

        $type = $request->query('type', 'all'); // 'disable', 'enable', 'all'
        
        try {
            if ($type == 'disable' || $type == 'all') {
                \Illuminate\Support\Facades\Artisan::call('billing:disable-unpaid');
            }
            
            if ($type == 'enable' || $type == 'all') {
                \Illuminate\Support\Facades\Artisan::call('billing:enable-paid');
            }
            
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            return back()->with('success', 'Sinkronisasi On/Off otomatis selesai dijalankan. Hasil: ' . nl2br($output));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan sinkronisasi: ' . $e->getMessage());
        }
    }
}
