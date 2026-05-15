<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Teknisi;
use App\Models\Rute;
use App\Models\RuteDetail;
use App\Models\OdcOdp;
use App\Services\RouteOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuteController extends Controller
{
    protected $routeService;

    public function __construct(RouteOptimizationService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function index()
    {
        $rute = Rute::with('teknisi')->latest()->get();
        $teknisi = Teknisi::where('is_active', true)->get();
        $pelangganHigh = Pelanggan::whereIn('prioritas_label', ['High', 'Medium'])->get();
        
        return view('content.rute.index', compact('rute', 'teknisi', 'pelangganHigh'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'id_teknisi' => 'required',
            'pelanggan_ids' => 'required|array',
        ]);

        $teknisi = Teknisi::findOrFail($request->id_teknisi);
        $pelanggan = Pelanggan::whereIn('id_pelanggan', $request->pelanggan_ids)->get()->toArray();

        $optimization = $this->routeService->optimize($teknisi->base_latitude, $teknisi->base_longitude, $pelanggan);

        if (empty($optimization['route'])) {
            return back()->with('error', 'Gagal membuat rute. Tidak ada titik tujuan yang berhasil diproses.');
        }

        DB::transaction(function() use ($teknisi, $optimization) {
            $rute = Rute::create([
                'id_teknisi' => $teknisi->id_teknisi,
                'tanggal_kunjungan' => now(),
                'titik_awal_lat' => $teknisi->base_latitude,
                'titik_awal_lng' => $teknisi->base_longitude,
                'metode' => 'KNN',
                'total_jarak_km' => $optimization['total_distance_km'],
                'status' => 'Planned'
            ]);

            foreach ($optimization['route'] as $index => $point) {
                RuteDetail::create([
                    'id_rute' => $rute->id_rute,
                    'urutan' => $index + 1,
                    'id_pelanggan' => $point['id_pelanggan'],
                    'jarak_dari_sebelumnya_km' => $point['distance_from_previous'],
                    'estimasi_waktu_menit' => $point['estimasi_waktu_menit'],
                    'status_kunjungan' => 'Pending'
                ]);
            }
        });

        return redirect()->route('rute.index')->with('success', 'Optimasi rute berhasil dibuat.');
    }

    public function show(Rute $rute)
    {
        $rute->load(['teknisi', 'details.pelanggan']);
        $odc_odp = OdcOdp::all();
        return view('content.rute.show', compact('rute', 'odc_odp'));
    }

    public function updateDetailStatus(Request $request, RuteDetail $detail)
    {
        $detail->update([
            'status_kunjungan' => 'Visited',
            'selesai_at' => now()
        ]);

        // Cek apakah semua kunjungan di rute ini sudah selesai
        $rute = $detail->rute;
        $totalDetails = $rute->details()->count();
        $finishedDetails = $rute->details()->where('status_kunjungan', 'Visited')->count();

        if ($totalDetails > 0 && $totalDetails === $finishedDetails) {
            $rute->update([
                'status' => 'Completed'
            ]);
        }

        return back()->with('success', 'Status kunjungan berhasil diperbarui menjadi Selesai.');
    }
}
