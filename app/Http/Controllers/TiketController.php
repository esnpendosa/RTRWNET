<?php

namespace App\Http\Controllers;

use App\Models\TiketGangguan;
use App\Models\Pelanggan;
use App\Models\Teknisi;
use Illuminate\Http\Request;

class TiketController extends Controller
{
    public function index()
    {
        $tiket = TiketGangguan::with(['pelanggan', 'teknisi'])->latest()->get();
        return view('content.tiket.index', compact('tiket'));
    }

    public function create()
    {
        $pelanggan = Pelanggan::all();
        $teknisi = Teknisi::where('is_active', true)->get();
        return view('content.tiket.create', compact('pelanggan', 'teknisi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required',
            'keluhan' => 'required',
            'prioritas' => 'required',
        ]);

        TiketGangguan::create([
            'kode_tiket' => 'TKT-' . date('YmdHis'),
            'id_pelanggan' => $request->id_pelanggan,
            'prioritas' => $request->prioritas,
            'keluhan' => $request->keluhan,
            'id_teknisi' => $request->id_teknisi,
            'status' => 'Open'
        ]);

        return redirect()->route('tiket.index')->with('success', 'Tiket berhasil dibuat');
    }

    public function updateStatus(Request $request, TiketGangguan $tiket)
    {
        $tiket->update([
            'status' => $request->status,
            'closed_at' => $request->status == 'Closed' ? now() : $tiket->closed_at
        ]);
        return back()->with('success', 'Status tiket berhasil diupdate');
    }
}
