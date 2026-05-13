<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\TiketGangguan;
use App\Models\Teknisi;
use App\Models\Router;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Dashboard Teknisi
        if ($user->hasPermission('rute_manage') && $user->teknisi) {
            $teknisi = $user->teknisi;
            $pelangganHigh = Pelanggan::whereIn('prioritas_label', ['High', 'Medium'])
                ->whereHas('tiket', function($q) {
                    $q->where('status', 'Open');
                })->get()->toArray();

            $routeService = app(\App\Services\RouteOptimizationService::class);
            $optimizedRoute = $routeService->optimize($teknisi->base_latitude, $teknisi->base_longitude, $pelangganHigh);
            
            $routers = Router::all();
            
            return view('content.dashboard.technician', compact('teknisi', 'optimizedRoute', 'routers'));
        }

        // 2. Dashboard Pelanggan (Role ID 4)
        if ($user->id_role == 4) {
            $pelanggan = Pelanggan::where('id_user', $user->id)->first();
            
            if (!$pelanggan) {
                return view('content.dashboard.dashboard', ['error' => 'Data pelanggan tidak ditemukan.']);
            }

            $stats = [
                'total_tagihan' => $pelanggan->tagihan()->count(),
                'tagihan_unpaid' => $pelanggan->tagihan()->where('status', 'unpaid')->count(),
                'total_tiket' => $pelanggan->tiket()->count(),
                'tiket_open' => $pelanggan->tiket()->whereIn('status', ['open', 'pending', 'proses'])->count(),
            ];

            $recentTagihan = $pelanggan->tagihan()->latest()->take(5)->get();
            $recentTiket = $pelanggan->tiket()->latest()->take(5)->get();

            return view('content.dashboard.customer', compact('pelanggan', 'stats', 'recentTagihan', 'recentTiket'));
        }

        // 3. Dashboard Admin / Owner
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $stats = [
            'total_pelanggan' => Pelanggan::count(),
            'total_gangguan' => TiketGangguan::where('status', 'Open')->count(),
            'gangguan_high' => TiketGangguan::where('prioritas', 'High')->where('status', 'Open')->count(),
            'total_teknisi' => Teknisi::count(),
            'total_router' => Router::count(),
            'tagihan_lunas' => \App\Models\Tagihan::where('status', 'paid')->where('bulan', $currentMonth)->where('tahun', $currentYear)->count(),
            'tagihan_unpaid' => \App\Models\Tagihan::where('status', 'unpaid')->where('bulan', $currentMonth)->where('tahun', $currentYear)->count(),
            'total_pendapatan' => \App\Models\Tagihan::where('status', 'paid')->where('bulan', $currentMonth)->where('tahun', $currentYear)->sum('jumlah'),
            'total_tagihan_lunas' => \App\Models\Tagihan::where('status', 'paid')->count(),
            'total_tagihan_unpaid' => \App\Models\Tagihan::where('status', 'unpaid')->count(),
            'total_pendapatan_all' => \App\Models\Tagihan::where('status', 'paid')->sum('jumlah'),
        ];

        $recentTiket = TiketGangguan::with('pelanggan')->latest()->take(5)->get();

        // Data peta sinkron dengan Web GIS Pelanggan
        $pelangganMap = Pelanggan::with(['tagihan', 'tiket'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($p) {
                $status = 'online';
                $hasTicket   = $p->tiket->whereIn('status', ['open', 'pending', 'proses'])->count() > 0;
                $hasUnpaid   = $p->tagihan->whereIn('status', ['unpaid', 'belum_bayar'])->count() > 0;
                $isOffline   = (!$p->last_online_status || $p->last_online_status === 'offline' || $p->last_online_status == 0);

                if ($hasTicket)       $status = 'perbaikan';
                elseif ($hasUnpaid)   $status = 'timeout';
                elseif ($isOffline)   $status = 'offline';

                $p->status_gis = $status;
                return $p;
            });

        return view('content.dashboard.dashboard', compact('stats', 'recentTiket', 'pelangganMap'));
    }
}
