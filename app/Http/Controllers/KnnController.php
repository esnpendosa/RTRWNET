<?php

/**
 * KnnController.php — Controller Klasifikasi KNN
 *
 * Skripsi: Optimasi Web GIS Sistem Manajemen Jaringan WiFi Menggunakan KNN
 * Mahasiswa: Muhammad As'ad Muhibbin Akbar — NIM 220602077
 *
 * Controller ini menghubungkan antarmuka UI dengan logika KNN di KnnService.php
 * Semua perhitungan matematis dilakukan di KnnService — controller hanya
 * mengatur alur request/response.
 */

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\KnnHasil;
use App\Services\KnnService;
use Illuminate\Http\Request;

class KnnController extends Controller
{
    /**
     * Dependency Injection: KnnService di-inject otomatis oleh Laravel IoC Container
     * KnnService berisi semua logika perhitungan Euclidean Distance 4D dan voting KNN
     */
    protected $knnService;

    public function __construct(KnnService $knnService)
    {
        $this->knnService = $knnService; // simpan instance service untuk digunakan di semua method
    }

    /**
     * ─────────────────────────────────────────────────────────────────────────
     * METHOD: index() — Halaman Utama KNN
     * ─────────────────────────────────────────────────────────────────────────
     * Menampilkan:
     *   - Daftar semua pelanggan (untuk pilih data uji)
     *   - Riwayat hasil klasifikasi KNN (Tabel 4.5, 4.6, 4.7 di skripsi)
     *   - Tabel akurasi, confusion matrix, F1-Score
     *
     * Route: GET /knn
     */
    public function index()
    {
        // Ambil semua pelanggan sebagai kandidat data uji
        $pelanggan = Pelanggan::all();

        // Ambil riwayat hasil KNN dengan relasi lengkap:
        // - pelanggan: nama pelanggan yang diklasifikasi
        // - parameter: nilai K yang digunakan
        // - details.tetangga: detail K tetangga terdekat (Rank, Jarak, Label)
        $hasil = KnnHasil::with(['pelanggan', 'parameter', 'details.tetangga'])
            ->latest()  // urutkan dari yang terbaru
            ->get();

        return view('content.knn.index', compact('pelanggan', 'hasil'));
    }

    /**
     * ─────────────────────────────────────────────────────────────────────────
     * METHOD: process() — Klasifikasi Satu Pelanggan
     * ─────────────────────────────────────────────────────────────────────────
     * Melakukan klasifikasi KNN untuk SATU pelanggan yang dipilih dari form.
     * Ini setara dengan menghitung satu baris DATA UJI baru di Excel.
     *
     * Alur:
     *   1. Validasi input (id_pelanggan dan nilai_k wajib diisi)
     *   2. Panggil KnnService::classify() untuk kalkulasi Euclidean + Voting
     *   3. Redirect balik dengan pesan hasil prediksi label (HIGH/MEDIUM/LOW)
     *
     * Route: POST /knn/process
     */
    public function process(Request $request)
    {
        // Validasi input form — pastikan data pelanggan ada di DB dan K adalah integer positif
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan', // data uji harus ada di DB
            'nilai_k'      => 'required|integer|min:1'                  // K minimal 1
        ]);

        // Jalankan klasifikasi KNN:
        // - Menghitung jarak Euclidean 4D ke semua data latih
        // - Mengambil K tetangga terdekat (RANK ≤ K)
        // - Melakukan voting label mayoritas
        // - Menyimpan hasil + detail tetangga ke DB
        $hasil = $this->knnService->classify($request->id_pelanggan, $request->nilai_k);

        // Jika gagal (data latih tidak cukup), kembalikan pesan error
        if (!$hasil) {
            return back()->with('error', 'Gagal memproses KNN. Pastikan data training mencukupi.');
        }

        // Berhasil — tampilkan label hasil prediksi (HIGH/MEDIUM/LOW)
        return back()->with('success', "Pelanggan berhasil diklasifikasikan sebagai: {$hasil->label_hasil}");
    }

    /**
     * ─────────────────────────────────────────────────────────────────────────
     * METHOD: batchProcess() — Klasifikasi Semua Pelanggan Sekaligus
     * ─────────────────────────────────────────────────────────────────────────
     * Melakukan klasifikasi KNN untuk SEMUA pelanggan secara batch.
     * Ini digunakan untuk:
     *   - Demo sidang (menampilkan akurasi 100% di Tabel 4.6)
     *   - Update label prioritas seluruh pelanggan setelah ada data baru
     *   - Validasi konsistensi sistem KNN
     *
     * Alur:
     *   1. Validasi bahwa data latih cukup (minimal K pelanggan sudah berlabel)
     *   2. Loop semua pelanggan, jalankan classify() satu per satu
     *   3. Redirect ke halaman KNN dengan total pelanggan yang berhasil diklasifikasi
     *
     * Route: POST /knn/batch
     */
    public function batchProcess(Request $request)
    {
        // Ambil nilai K dari request, default K=3 sesuai skripsi
        $k = $request->nilai_k ?? 3;

        // ── Validasi: cek apakah data latih mencukupi ─────────────────────────
        // Data latih = pelanggan yang sudah memiliki label (prioritas_label NOT NULL)
        // Minimal harus ada K data latih agar bisa mencari K tetangga terdekat
        $trainingCount = Pelanggan::whereNotNull('prioritas_label')->count();
        if ($trainingCount < $k) {
            return back()->with('error',
                "Data training tidak cukup. Dibutuhkan minimal $k pelanggan " .
                "yang sudah memiliki label (High/Medium/Low)."
            );
        }

        // ── Jalankan klasifikasi untuk SEMUA pelanggan ────────────────────────
        $pelanggan = Pelanggan::all(); // ambil semua pelanggan dari database

        $count = 0; // counter pelanggan yang berhasil diklasifikasi
        foreach ($pelanggan as $p) {
            // Setiap pelanggan diklasifikasikan menggunakan K tetangga terdekat
            // Pelanggan yang sudah berlabel juga diklasifikasikan ulang untuk validasi akurasi
            $hasil = $this->knnService->classify($p->id_pelanggan, $k);
            if ($hasil) $count++; // hitung yang berhasil
        }

        // Redirect ke halaman KNN dengan pesan sukses
        return redirect()->route('knn.index')
            ->with('success', "Seluruh pelanggan ($count data) telah berhasil diklasifikasikan ulang.");
    }
}
