<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Setting;
use App\Http\Resources\TagihanResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of bills with filters (status, month, year).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tagihan::with('pelanggan');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->input('tahun'));
        }

        $perPage = $request->input('per_page', 10);
        $tagihan = $query->paginate($perPage);

        return $this->successResponse(
            TagihanResource::collection($tagihan),
            'Berhasil mengambil daftar tagihan'
        );
    }

    /**
     * Display bills that are due today.
     */
    public function jatuhTempoHariIni(Request $request): JsonResponse
    {
        $day = $request->query('day', now()->day);
        $dueDay = (int) Setting::get('billing_isolir_date', '10');

        // Check if today matches the billing isolir date
        if ($day == $dueDay || $request->boolean('force')) {
            $tagihan = Tagihan::where('status', 'unpaid')
                ->where('bulan', now()->month)
                ->where('tahun', now()->year)
                ->with('pelanggan')
                ->get();
        } else {
            $tagihan = collect();
        }

        return $this->successResponse(
            TagihanResource::collection($tagihan),
            'Berhasil mengambil tagihan jatuh tempo hari ini'
        );
    }

    /**
     * Mark a bill as paid manually (Admin only).
     */
    public function tandaiLunas(Request $request, $id): JsonResponse
    {
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return $this->errorResponse('Tagihan tidak ditemukan', 404);
        }

        if ($tagihan->status === 'paid') {
            return $this->errorResponse('Tagihan sudah lunas sebelumnya', 400);
        }

        $validator = Validator::make($request->all(), [
            'metode_pembayaran' => 'nullable|string',
            'catatan_admin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi Gagal', 422, $validator->errors());
        }

        $metode = $request->input('metode_pembayaran', 'Manual via API');
        $catatan = $request->input('catatan_admin', 'Ditandai lunas via REST API');

        // Update tagihan status
        $tagihan->update([
            'status' => 'paid',
            'paid_at' => now(),
            'metode_pembayaran' => $metode,
            'catatan_admin' => $catatan
        ]);

        $pelanggan = $tagihan->pelanggan;
        $mikrotikSynced = true;
        $syncMessage = '';

        if ($pelanggan) {
            // Update active status in database
            $pelanggan->update(['is_active' => true]);

            // Sync with MikroTik if router is configured
            if ($pelanggan->id_router) {
                try {
                    $mikrotikService = app(\App\Services\MikrotikService::class);
                    $success = $mikrotikService->setSecretStatus(
                        $pelanggan->router,
                        $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan,
                        $pelanggan->mikrotik_type,
                        false,
                        $pelanggan->ip_address
                    );

                    if (!$success) {
                        $mikrotikSynced = false;
                        $syncMessage = 'Sinkronisasi MikroTik gagal, silakan periksa router Anda.';
                        Log::warning("API Billing: MikroTik sync failed for customer {$pelanggan->kode_pelanggan}");
                    }
                } catch (\Exception $e) {
                    $mikrotikSynced = false;
                    $syncMessage = 'Error MikroTik: ' . $e->getMessage();
                    Log::error("API Billing: MikroTik exception for customer {$pelanggan->kode_pelanggan}: " . $e->getMessage());
                }
            }
        }

        // Try sending WhatsApp receipt if active
        if ($pelanggan && $pelanggan->no_wa && $pelanggan->wa_active && Setting::get('wa_billing_notification_enabled', '1') == '1') {
            try {
                $waClient = new \App\Services\WhatsappClient();
                $waClient->sendReceipt($tagihan, true);
            } catch (\Exception $e) {
                Log::error('API Billing: Gagal kirim receipt WhatsApp: ' . $e->getMessage());
            }
        }

        // Log the activity
        try {
            \App\Helpers\ActivityLogger::log(
                'Memverifikasi lunas tagihan #' . $tagihan->id_tagihan . ' (' . ($pelanggan ? $pelanggan->nama_pelanggan : 'Umum') . ') sebesar Rp ' . number_format($tagihan->jumlah, 0, ',', '.') . ' via API',
                'tagihan'
            );
        } catch (\Exception $e) {
            Log::error("API Billing: Gagal mencatat log aktivitas: " . $e->getMessage());
        }

        return $this->successResponse(
            new TagihanResource($tagihan),
            'Tagihan berhasil ditandai lunas. ' . $syncMessage
        );
    }

    /**
     * Get billing statistics.
     */
    public function statistik(): JsonResponse
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $pendapatanBulanIni = Tagihan::where('status', 'paid')
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->sum('jumlah');

        $tunggakanBulanIni = Tagihan::where('status', 'unpaid')
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->sum('jumlah');

        $totalTunggakanSemua = Tagihan::where('status', 'unpaid')
            ->sum('jumlah');

        $lunasCount = Tagihan::where('status', 'paid')
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->count();

        $unpaidCount = Tagihan::where('status', 'unpaid')
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->count();

        $stats = [
            'pendapatan_bulan_ini' => (int) $pendapatanBulanIni,
            'tunggakan_bulan_ini' => (int) $tunggakanBulanIni,
            'total_tunggakan_semua' => (int) $totalTunggakanSemua,
            'pelanggan_lunas_bulan_ini' => $lunasCount,
            'pelanggan_belum_lunas_bulan_ini' => $unpaidCount,
        ];

        return $this->successResponse($stats, 'Berhasil mengambil statistik tagihan');
    }
}
