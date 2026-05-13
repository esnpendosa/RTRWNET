<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\KnnHasil;
use App\Services\KnnService;
use Illuminate\Http\Request;

class KnnController extends Controller
{
    protected $knnService;

    public function __construct(KnnService $knnService)
    {
        $this->knnService = $knnService;
    }

    public function index()
    {
        $pelanggan = Pelanggan::all();
        $hasil = KnnHasil::with(['pelanggan', 'parameter', 'details.tetangga'])->latest()->get();
        return view('content.knn.index', compact('pelanggan', 'hasil'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'nilai_k' => 'required|integer|min:1'
        ]);

        $hasil = $this->knnService->classify($request->id_pelanggan, $request->nilai_k);

        if (!$hasil) {
            return back()->with('error', 'Gagal memproses KNN. Pastikan data training mencukupi.');
        }

        return back()->with('success', "Pelanggan berhasil diklasifikasikan sebagai: {$hasil->label_hasil}");
    }
    
    public function batchProcess(Request $request)
    {
        $k = $request->nilai_k ?? 3;
        
        $trainingCount = Pelanggan::whereNotNull('prioritas_label')->count();
        if ($trainingCount < $k) {
            return back()->with('error', "Data training tidak cukup. Dibutuhkan minimal $k pelanggan yang sudah memiliki label (High/Medium/Low).");
        }

        $pelanggan = Pelanggan::all();
        
        $count = 0;
        foreach ($pelanggan as $p) {
            $hasil = $this->knnService->classify($p->id_pelanggan, $k);
            if ($hasil) $count++;
        }

        return redirect()->route('knn.index')->with('success', "Seluruh pelanggan ($count data) telah berhasil diklasifikasikan ulang.");
    }
}
