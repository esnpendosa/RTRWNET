<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\TiketGangguan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        $monthlyTickets = TiketGangguan::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();
            
        return view('content.laporan.index', compact('monthlyTickets'));
    }

    public function tagihan(Request $request)
    {
        $query = Tagihan::with('pelanggan');

        // Filter by Month
        if ($request->month) {
            $query->where('bulan', $request->month);
        }

        // Filter by Year
        if ($request->year) {
            $query->where('tahun', $request->year);
        }

        // Filter by Status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by Date Range (paid_at)
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('paid_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $tagihan = $query->latest()->get();
        
        $total_jumlah = $tagihan->sum('jumlah');
        $total_lunas = $tagihan->where('status', 'paid')->sum('jumlah');
        $total_piutang = $tagihan->where('status', 'unpaid')->sum('jumlah');

        return view('content.laporan.tagihan', compact('tagihan', 'total_jumlah', 'total_lunas', 'total_piutang'));
    }

    public function exportPdf(Request $request)
    {
        $query = Tagihan::with('pelanggan');

        if ($request->month) $query->where('bulan', $request->month);
        if ($request->year) $query->where('tahun', $request->year);
        if ($request->status) $query->where('status', $request->status);
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('paid_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $tagihan = $query->latest()->get();
        $total_jumlah = $tagihan->sum('jumlah');
        $total_lunas = $tagihan->where('status', 'paid')->sum('jumlah');
        $total_piutang = $tagihan->where('status', 'unpaid')->sum('jumlah');

        $data = [
            'tagihan' => $tagihan,
            'total_jumlah' => $total_jumlah,
            'total_lunas' => $total_lunas,
            'total_piutang' => $total_piutang,
            'filter' => $request->all(),
            'title' => 'Laporan Pembayaran Tagihan'
        ];

        $pdf = Pdf::loadView('content.laporan.tagihan_pdf', $data);
        return $pdf->download('laporan-tagihan-' . date('Y-m-d') . '.pdf');
    }
}
