<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Pelanggan::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $pelanggan = $query->get();
        return view('content.pelanggan.index', compact('pelanggan'));
    }

    public function create(Request $request)
    {
        $routers = \App\Models\Router::all();
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        return view('content.pelanggan.create', compact('routers', 'lat', 'lng'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_router' => 'nullable|exists:mikrotik_router,id_router',
            'kode_pelanggan' => 'required|unique:pelanggan',
            'nama_pelanggan' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'no_wa' => 'nullable|string',
            'mikrotik_type' => 'required|in:pppoe,hotspot,static',
            'alamat' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'usage_gb' => 'nullable|numeric',
            'jumlah_device' => 'nullable|integer',
            'paket' => 'nullable|string',
            'harga_layanan' => 'required|numeric',
            'ip_address' => 'nullable|string',
            'billing_date' => 'required|integer|min:1|max:28',
        ]);

        $pelanggan = Pelanggan::create($validated);

        // Auto-create User account
        $prefix = strtolower($pelanggan->kode_pelanggan);
        $userEmail = $pelanggan->email ?: $prefix . '@rtrwnet.com';
        
        $user = \App\Models\User::create([
            'name' => $pelanggan->nama_pelanggan,
            'email' => $userEmail,
            'username_email' => $prefix, // Set username specifically to the code (e.g. ad20)
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'id_role' => 4, // Role Pelanggan
            'is_active' => true
        ]);

        $pelanggan->update(['id_user' => $user->id]);

        // Sync with Mikrotik if router is set
        if ($pelanggan->id_router) {
            $mikrotik = app(MikrotikService::class);
            $username = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
            $mikrotik->setSecretStatus($pelanggan->router, $username, $pelanggan->mikrotik_type, !$pelanggan->is_active, $pelanggan->ip_address);
        }

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function edit(Pelanggan $pelanggan)
    {
        $routers = \App\Models\Router::all();
        return view('content.pelanggan.edit', compact('pelanggan', 'routers'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'id_router' => 'nullable|exists:mikrotik_router,id_router',
            'nama_pelanggan' => 'required',
            'email' => 'nullable|email|unique:users,email,' . ($pelanggan->id_user ?? 0),
            'no_wa' => 'nullable|string',
            'mikrotik_username' => 'nullable|string',
            'mikrotik_type' => 'required|in:pppoe,hotspot,static',
            'alamat' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'usage_gb' => 'nullable|numeric',
            'jumlah_device' => 'nullable|integer',
            'paket' => 'nullable|string',
            'harga_layanan' => 'required|numeric',
            'ip_address' => 'nullable|string',
            'is_active' => 'required|boolean',
            'billing_date' => 'required|integer|min:1|max:28',
        ]);

        $pelanggan->update($validated);

        // Sync with User account if email changed
        if ($pelanggan->id_user && $pelanggan->email) {
            $user = \App\Models\User::find($pelanggan->id_user);
            if ($user && $user->email !== $pelanggan->email) {
                $user->update(['email' => $pelanggan->email]);
            }
        }

        // Sync with Mikrotik if router is set
        if ($pelanggan->id_router) {
            $mikrotik = app(MikrotikService::class);
            $username = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
            $mikrotik->setSecretStatus($pelanggan->router, $username, $pelanggan->mikrotik_type, !$pelanggan->is_active, $pelanggan->ip_address);
        }

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diupdate');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();
        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus');
    }

    public function show(Pelanggan $pelanggan)
    {
        // Security check
        $user = auth()->user();
        if ($user->id_role != 1 && $pelanggan->id_user != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $mikrotikData = null;
        if ($pelanggan->id_router && $pelanggan->router) {
            $mikrotik = app(MikrotikService::class);
            $username = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
            $mikrotikData = $mikrotik->getUserDetails($pelanggan->router, $username, $pelanggan->mikrotik_type, $pelanggan);
        }

        return view('content.pelanggan.show', compact('pelanggan', 'mikrotikData'));
    }

    public function traffic(Pelanggan $pelanggan)
    {
        // Allow if user is admin OR if user is the customer themselves
        if (!auth()->user()->hasPermission('pelanggan_manage') && auth()->user()->id !== $pelanggan->id_user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$pelanggan->id_router || !$pelanggan->router) {
            return response()->json(['error' => 'No router'], 404);
        }

        $mikrotik = app(MikrotikService::class);
        $username = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
        
        $traffic = null;

        // Try to get real traffic first
        try {
            if ($pelanggan->mikrotik_type === 'pppoe') {
                $interfaces = [
                    '<pppoe-' . $username . '>',
                    'pppoe-' . $username,
                    $username
                ];

                foreach ($interfaces as $iface) {
                    $traffic = $mikrotik->getTraffic($pelanggan->router, $iface);
                    if (!empty($traffic) && isset($traffic[0]['rx-bits-per-second'])) {
                        $traffic = $traffic[0];
                        break;
                    }
                }
            } else {
                $traffic = $mikrotik->getQueueTraffic($pelanggan->router, $username, $pelanggan);
            }
        } catch (\Exception $e) {
            $traffic = null;
        }

        return response()->json($traffic ?? ['rx-bits-per-second' => 0, 'tx-bits-per-second' => 0]);
    }

    public function myConnection()
    {
        $user = auth()->user();
        $isAdmin = $user->hasPermission('pelanggan_manage');
        $pelanggan = Pelanggan::where('id_user', $user->id)->first();
        
        $selectedId = request('id');
        if ($selectedId && $isAdmin) {
            $pelanggan = Pelanggan::find($selectedId);
        }

        if (!$pelanggan && $isAdmin) {
            $pelanggan = Pelanggan::first();
        }

        if (!$pelanggan) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pelanggan. Silakan hubungi admin.');
        }

        $mikrotikData = null;
        if ($pelanggan->id_router && $pelanggan->router) {
            $mikrotik = app(MikrotikService::class);
            $username = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
            
            // Real-time Sync IP from Mikrotik if missing
            $activeIp = $mikrotik->getPelangganActiveIp($pelanggan->router, $username, $pelanggan->mikrotik_type);
            if ($activeIp && (!$pelanggan->ip_address || $pelanggan->ip_address === 'N/A')) {
                $pelanggan->update(['ip_address' => $activeIp]);
            }

            $mikrotikData = $mikrotik->getUserDetails($pelanggan->router, $username, $pelanggan->mikrotik_type, $pelanggan);
            
            // Update last online status in DB for GIS map consistency
            $isOnline = isset($mikrotikData['active']) ? 1 : 0;
            if ($pelanggan->last_online_status != $isOnline) {
                $pelanggan->update(['last_online_status' => $isOnline]);
            }
        }

        $allPelanggan = $isAdmin ? Pelanggan::all() : null;

        // Get latest bill status
        $currentBill = \App\Models\Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->latest()
            ->first();

        return view('content.pelanggan.my-connection', compact('pelanggan', 'mikrotikData', 'allPelanggan', 'isAdmin', 'currentBill'));
    }

    public function map()
    {
        $pelanggan = Pelanggan::with(['tagihan', 'tiket'])->get()->map(function($p) {
            $status = 'online'; // Default Hijau (Online & Aktif)
            
            // 1. Prioritas Tertinggi: Perbaikan (Biru) - Ada tiket aktif
            $hasTicket = $p->tiket->whereIn('status', ['open', 'pending', 'proses'])->count() > 0;
            
            // 2. Timeout (Merah) - Ada tagihan belum bayar (mendukung 'unpaid' atau 'belum_bayar')
            $hasUnpaidBill = $p->tagihan->whereIn('status', ['unpaid', 'belum_bayar'])->count() > 0;

            // 3. Offline (Kuning) - Status ping terakhir offline (mendukung boolean 0/false atau string 'offline')
            $isOffline = (!$p->last_online_status || $p->last_online_status === 'offline' || $p->last_online_status == 0 || $p->last_online_status === false);

            if ($hasTicket) {
                $status = 'perbaikan';
            } elseif ($hasUnpaidBill) {
                $status = 'timeout';
            } elseif ($isOffline) {
                $status = 'offline';
            }

            $p->status_gis = $status;
            return $p;
        });

        return view('content.pelanggan.map', compact('pelanggan'));
    }

    public function toggleStatus(Pelanggan $pelanggan)
    {
        $newStatus = $pelanggan->is_active ? 0 : 1;
        $pelanggan->update(['is_active' => $newStatus]);

        if ($pelanggan->id_router) {
            $mikrotik = new \App\Services\MikrotikService();
            $router = $pelanggan->router;
            if ($router) {
                $disable = ($newStatus == 0);
                $mUser = $pelanggan->mikrotik_username ?: $pelanggan->kode_pelanggan;
                $mikrotik->setSecretStatus($router, $mUser, $pelanggan->mikrotik_type, $disable, $pelanggan->ip_address);
            }
        }

        return back()->with('success', 'Status pelanggan ' . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'));
    }

    public function card(Pelanggan $pelanggan)
    {
        // Security check
        $user = auth()->user();
        if ($user->id_role != 1 && $pelanggan->id_user != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $pelanggans = collect([$pelanggan]);
        return view('content.pelanggan.card', compact('pelanggans'));
    }

    public function cardMassal(Request $request)
    {
        $query = Pelanggan::query();
        if ($request->search) {
            $query->where('nama_pelanggan', 'like', "%{$request->search}%")
                  ->orWhere('kode_pelanggan', 'like', "%{$request->search}%");
        }
        $pelanggans = $query->get();
        return view('content.pelanggan.card', compact('pelanggans'));
    }

    public function export()
    {
        $pelanggan = Pelanggan::all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'ID Pelanggan', 'ID Router', 'Kode Pelanggan', 'Nama Pelanggan', 'No WA', 
            'Mikrotik Username', 'Mikrotik Type', 'Alamat', 'Latitude', 'Longitude', 
            'Usage GB', 'Jumlah Device', 'Harga Layanan', 'Paket', 'Status Aktif', 'Prioritas', 
            'IP Address', 'Billing Date'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $column++;
        }

        $row = 2;
        foreach ($pelanggan as $p) {
            $sheet->setCellValue('A' . $row, $p->id_pelanggan);
            $sheet->setCellValue('B' . $row, $p->id_router);
            $sheet->setCellValue('C' . $row, $p->kode_pelanggan);
            $sheet->setCellValue('D' . $row, $p->nama_pelanggan);
            $sheet->setCellValue('E' . $row, $p->no_wa);
            $sheet->setCellValue('F' . $row, $p->mikrotik_username);
            $sheet->setCellValue('G' . $row, $p->mikrotik_type);
            $sheet->setCellValue('H' . $row, $p->alamat);
            $sheet->setCellValue('I' . $row, $p->latitude);
            $sheet->setCellValue('J' . $row, $p->longitude);
            $sheet->setCellValue('K' . $row, $p->usage_gb);
            $sheet->setCellValue('L' . $row, $p->jumlah_device);
            $sheet->setCellValue('M' . $row, $p->harga_layanan);
            $sheet->setCellValue('N' . $row, $p->paket);
            $sheet->setCellValue('O' . $row, $p->is_active ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('P' . $row, $p->prioritas_label);
            $sheet->setCellValue('Q' . $row, $p->ip_address);
            $sheet->setCellValue('R' . $row, $p->billing_date);
            $row++;
        }

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'data_pelanggan_lengkap_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header
        unset($rows[0]);

        $imported = 0;
        foreach ($rows as $row) {
            if (empty($row[2])) continue; // Skip if Kode Pelanggan is empty (Column C)

            Pelanggan::updateOrCreate(
                ['kode_pelanggan' => $row[2]],
                [
                    'id_router' => $row[1],
                    'nama_pelanggan' => $row[3],
                    'no_wa' => $row[4],
                    'mikrotik_username' => $row[5],
                    'mikrotik_type' => $row[6] ?: 'pppoe',
                    'alamat' => $row[7],
                    'latitude' => $row[8],
                    'longitude' => $row[9],
                    'usage_gb' => $row[10],
                    'jumlah_device' => $row[11],
                    'harga_layanan' => $row[12],
                    'paket' => $row[13],
                    'is_active' => (strtolower($row[14]) == 'aktif' || $row[14] == 1) ? 1 : 0,
                    'prioritas_label' => $row[15],
                    'ip_address' => $row[16],
                    'billing_date' => $row[17] ?: 1,
                ]
            );
            $imported++;
        }

        return back()->with('success', $imported . ' data pelanggan berhasil diimport dengan data lengkap');
    }
}
