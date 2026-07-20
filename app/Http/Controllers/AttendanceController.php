<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FingerprintService;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * ZKTeco/Solutions ADMS PUSH protocol handler
     */
    public function handleADMS(Request $request, FingerprintService $fingerprintService)
    {
        $sn = $request->query('sn') ?? $request->query('SN');
        $table = $request->query('table') ?? $request->query('Table');
        $method = $request->method();

        Log::info("ADMS Request: Path=" . $request->path() . ", SN=$sn, Table=$table, Method=$method");

        if ($sn) {
            $this->updateDeviceStatus($sn);
        }

        // 1. Handshake
        if ($method === 'GET' && str_contains($request->path(), 'cdata')) {
            return response("GET OPTION FROM: {$sn}\n" .
                            "RegistryCode=Default\n" .
                            "ServerVersion=3.1.1\n" .
                            "PushVersion=3.0.1\n" .
                            "Delay=30\n" .
                            "TransTimes=00:00;23:59\n" .
                            "TransInterval=1\n" .
                            "TransFlag=1111111111\n" .
                            "Realtime=1\n" .
                            "Encrypt=0");
        }

        // 2. Data Push (ATTLOG)
        if ($method === 'POST' && $table) {
            $content = $request->getContent();
            if (strtoupper($table) === 'ATTLOG') {
                Log::info("Processing Log for SN $sn:\n" . $content);
                $this->processLog($content, $fingerprintService, $sn);
                return response("OK")->header('Content-Type', 'text/plain');
            }
            return response("OK");
        }

        // 3. Command Request (Time Synchronization - Diberi batasan 1 jam sekali agar tidak loop terus-menerus)
        if ($method === 'GET' && str_contains($request->path(), 'getrequest')) {
            $cacheKey = "device_time_synced_{$sn}";
            if (!cache()->has($cacheKey)) {
                $now = now()->format('Y-m-d H:i:s');
                Log::info("ADMS: Sending SET_TIME command to SN $sn: $now");
                cache()->put($cacheKey, true, now()->addMinutes(60));
                return response("C:" . time() . ":SET_TIME " . $now);
            }
            return response("OK");
        }

        return response("OK");
    }

    /**
     * Webhook Handler for Generic JSON data (Cloud Fingerspot/Other)
     */
    public function handleGeneric(Request $request, FingerprintService $fingerprintService)
    {
        $data = $request->all();
        Log::info("Webhook Attendance Received: " . json_encode($data));

        if (is_array($data) && isset($data[0]['pin'])) {
            foreach ($data as $log) {
                $fingerprintService->syncLog($log);
            }
        } elseif (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $log) {
                $fingerprintService->syncLog($log);
            }
        } elseif (isset($data['pin'])) {
            $fingerprintService->syncLog($data);
        }

        return response()->json(['status' => 'success', 'message' => 'Data processed']);
    }

    protected function processLog($content, $service, $sn = null)
    {
        $lines = explode("\n", trim($content));
        $synced = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $pin = $parts[0];
                $date = $parts[1];
                $time = $parts[2] ?? '00:00:00';
                $state = $parts[3] ?? 0; // 0=Masuk, 1=Pulang
                
                // Gunakan waktu server real-time saat ini agar 100% akurat dengan jam sekarang
                $dateTime = now()->format('Y-m-d H:i:s');

                Log::info("FINGERPRINT RAW: ID={$pin}, DateTime={$dateTime} (Server Time Override), Status={$state}");

                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
                    $devicesJson = Setting::get('fingerspot_devices');
                    $devices = $devicesJson ? json_decode($devicesJson, true) : [];
                    $deviceName = $devices[0]['name'] ?? 'Solution X105';

                    if ($sn) {
                        foreach ($devices as $d) {
                            if (isset($d['sn']) && strtoupper(trim($d['sn'])) === strtoupper(trim($sn))) {
                                $deviceName = $d['name'] ?? $deviceName;
                                break;
                            }
                        }
                    }

                    $service->syncLog([
                        'pin'       => $pin,
                        'date_time' => $dateTime,
                        'status'    => $state,
                        'sn'        => $sn
                    ], $deviceName);
                    $synced++;
                }
            }
        }
        return $synced;
    }

    protected function updateDeviceStatus($sn)
    {
        $devicesJson = Setting::get('fingerspot_devices');
        $devices = $devicesJson ? json_decode($devicesJson, true) : [];
        $changed = false;

        foreach ($devices as &$device) {
            if (isset($device['sn']) && strtoupper(trim($device['sn'])) === strtoupper(trim($sn))) {
                $device['last_seen'] = now()->toDateTimeString();
                $changed = true;
                break;
            }
        }

        if ($changed) {
            Setting::updateOrCreate(
                ['key' => 'fingerspot_devices'],
                ['value' => json_encode(array_values($devices))]
            );
        }
    }

    /**
     * Rekap Absensi & Kehadiran Hari Ini
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->id_role == 4) {
            abort(403, 'Akses ditolak. Pelanggan tidak dapat mengakses halaman absensi pegawai.');
        }
        
        $targetUserId = $request->get('user_id');
        if (!$targetUserId) {
            $targetUserId = 'all';
        }

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        
        $startDate = Carbon::create($year, $month, 11)->subMonth()->startOfDay();
        $endDate = Carbon::create($year, $month, 10)->endOfDay();
        $periodeLabel = $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y');
        
        $defaultTab = ($targetUserId === 'all') ? 'bulanan' : 'harian';
        $tab = $request->get('tab', $defaultTab);

        // Fetch all employees (exclude Pelanggan and fachrozi/facrozi)
        $allUsers = User::where('id_role', '!=', 4)
            ->where('name', 'not like', '%facrozi%')
            ->where('name', 'not like', '%fachrozi%')
            ->orderBy('name')
            ->get();

        $counts = [
            'hadir' => 0, 'alpha' => 0, 'total' => 0
        ];

        // GLOBAL REPORT (All Employees)
        if ($targetUserId === 'all') {
            if ($tab === 'harian') {
                $perPage = $request->get('per_page', 50);
                $query = Absensi::with('user')
                    ->whereBetween('tgl', [$startDate->toDateString(), $endDate->toDateString()])
                    ->whereHas('user', fn($q) => $q->where('id_role', '!=', 4)->where('name', 'not like', '%facrozi%')->where('name', 'not like', '%fachrozi%'));
                
                $reportData = $query->orderBy('tgl', 'desc')->paginate($perPage);
                
                $counts = [
                    'hadir' => (clone $query)->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count(),
                    'alpha' => 0,
                ];
                $counts['total'] = $counts['hadir'];

                $targetUser = (object)['id' => 'all', 'name' => 'SEMUA PEGAWAI', 'kode_pegawai' => '-'];
                
                return view('kepegawaian.absensi_report', compact('tab', 'reportData', 'month', 'year', 'targetUser', 'counts', 'allUsers', 'targetUserId', 'periodeLabel'));
            }

            $reportData = [];

            foreach ($allUsers as $u) {
                $uAbsensis = Absensi::where('user_id', $u->id)
                    ->whereBetween('tgl', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();
                
                $hadir = $uAbsensis->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count();
                
                $workDays = 0;
                $currentDate = $startDate->copy();
                $periodEndDate = $endDate->greaterThan(now()) ? now() : $endDate;

                while ($currentDate->lessThanOrEqualTo($periodEndDate)) {
                    if (!$currentDate->isWeekend()) {
                        $workDays++;
                    }
                    $currentDate->addDay();
                }
                
                $alpha = max(0, $workDays - $hadir);

                $reportData[] = [
                    'user' => $u,
                    'hadir' => $hadir,
                    'alpha' => $alpha,
                    'persentase' => $workDays > 0 ? round(($hadir / $workDays) * 100) : 0
                ];

                $counts['hadir'] += $hadir;
                $counts['alpha'] += $alpha;
            }
            $counts['total'] = $counts['hadir'] + $counts['alpha'];

            $targetUser = (object)['id' => 'all', 'name' => 'SEMUA PEGAWAI', 'kode_pegawai' => '-'];
            
            return view('kepegawaian.absensi_report', compact('tab', 'reportData', 'month', 'year', 'targetUser', 'counts', 'allUsers', 'targetUserId', 'periodeLabel'));
        }

        // SINGLE USER REPORT
        $targetUser = User::findOrFail($targetUserId);
        
        if ($tab === 'harian') {
            $harianData = [];
            
            $devicesJson = Setting::get('fingerspot_devices');
            $devices = $devicesJson ? json_decode($devicesJson, true) : [];
            $defaultLocation = $devices[0]['name'] ?? 'Solution X105';

            $absensis = Absensi::where('user_id', $targetUserId)
                ->whereBetween('tgl', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->keyBy(fn($item) => Carbon::parse($item->tgl)->format('Y-m-d'));

            $currentDate = $startDate->copy();
            $periodEndDate = $endDate->greaterThan(now()) ? now() : $endDate;

            while ($currentDate->lessThanOrEqualTo($periodEndDate)) {
                $dateString = $currentDate->format('Y-m-d');
                $abs = $absensis->get($dateString);
                
                $isWeekend = $currentDate->isWeekend();
                $status = $abs ? $abs->status_kehadiran : ($isWeekend ? 'Libur Weekend' : 'Alpha');

                if (!$isWeekend || $abs) {
                    $harianData[] = [
                        'tanggal' => $dateString,
                        'status' => $status,
                        'absensi_id' => $abs ? $abs->id : null,
                        'jam_masuk' => $abs ? $abs->jam_masuk : null,
                        'jam_pulang' => $abs ? $abs->jam_pulang : null,
                        'lokasi' => $abs ? ($abs->lokasi ?? $defaultLocation) : $defaultLocation,
                        'keterangan' => $abs ? $abs->keterangan : '',
                    ];
                }
                $currentDate->addDay();
            }

            $counts = [
                'hadir' => $absensis->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count(),
                'alpha' => count(array_filter($harianData, fn($x) => $x['status'] == 'Alpha')),
            ];
            $counts['total'] = $counts['hadir'] + $counts['alpha'];

            return view('kepegawaian.absensi_report', compact('tab', 'harianData', 'month', 'year', 'targetUser', 'counts', 'allUsers', 'targetUserId', 'periodeLabel'));
        }

        // Bulanan Logic for individual
        $bulananData = [];
        for ($m = 1; $m <= 12; $m++) {
            if ($m <= ($year == date('Y') ? date('n') : 12)) {
                $monthAbs = Absensi::where('user_id', $targetUserId)
                    ->whereMonth('tgl', $m)
                    ->whereYear('tgl', $year)
                    ->get();
                
                $hadir = $monthAbs->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count();
                
                $daysInMonth = Carbon::create($year, $m, 1)->daysInMonth;
                $maxDay = ($m == date('n') && $year == date('Y')) ? date('j') : $daysInMonth;
                $workDays = 0;

                for ($d = 1; $d <= $maxDay; $d++) {
                    if (!Carbon::create($year, $m, $d)->isWeekend()) {
                        $workDays++;
                    }
                }
                
                $alpha = max(0, $workDays - $hadir);

                $bulananData[] = [
                    'bulan' => Carbon::create()->month($m)->translatedFormat('F'),
                    'hadir' => $hadir,
                    'alpha' => $alpha,
                    'total' => $workDays,
                    'persentase' => $workDays > 0 ? round(($hadir / $workDays) * 100) : 0
                ];

                $counts['hadir'] += $hadir;
                $counts['alpha'] += $alpha;
            }
        }
        $counts['total'] = $counts['hadir'] + $counts['alpha'];

        return view('kepegawaian.absensi_report', compact('tab', 'bulananData', 'month', 'year', 'targetUser', 'counts', 'allUsers', 'targetUserId', 'periodeLabel'));
    }

    /**
     * Halaman absensi hari ini (realtime monitoring)
     */
    public function today()
    {
        $user = Auth::user();
        if ($user->id_role == 4) {
            abort(403, 'Akses ditolak. Pelanggan tidak dapat mengakses halaman absensi pegawai.');
        }

        $today = Carbon::today()->toDateString();
        $absensis = Absensi::where('tgl', $today)->with('user')->orderBy('created_at', 'desc')->get();

        $devicesJson = Setting::get('fingerspot_devices');
        $devices = $devicesJson ? json_decode($devicesJson, true) : [];

        return view('kepegawaian.absensi_today', compact('absensis', 'devices'));
    }

    /**
     * Form Pengaturan Perangkat ADMS & Jam Kerja
     */
    public function showSettings()
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $devicesJson = Setting::get('fingerspot_devices');
        $devices = $devicesJson ? json_decode($devicesJson, true) : [];
        
        $minInterval = Setting::get('absensi_min_interval', 5);
        $batasMasuk = Setting::get('absensi_batas_masuk', '08:00:00');
        $batasPulang = Setting::get('absensi_batas_pulang', '17:00:00');
        $waRekapTarget = Setting::get('wa_rekap_absensi_target', '+62 821-8782-7382');

        return view('kepegawaian.absensi_settings', compact('devices', 'minInterval', 'batasMasuk', 'batasPulang', 'waRekapTarget'));
    }

    /**
     * Simpan Pengaturan
     */
    public function storeSettings(Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $request->validate([
            'absensi_min_interval'    => 'required|integer|min:0',
            'absensi_batas_masuk'     => 'required|date_format:H:i',
            'absensi_batas_pulang'    => 'required|date_format:H:i',
            'devices'                 => 'nullable|array',
            'wa_rekap_absensi_target' => 'required|string',
        ]);

        Setting::set('absensi_min_interval', $request->absensi_min_interval);
        Setting::set('absensi_batas_masuk', $request->absensi_batas_masuk . ':00');
        Setting::set('absensi_batas_pulang', $request->absensi_batas_pulang . ':00');
        Setting::set('wa_rekap_absensi_target', $request->wa_rekap_absensi_target);

        $devices = $request->devices ?: [];
        foreach ($devices as &$device) {
            // Guarantee internal endpoint url matching host and domain / IP
            $device['url'] = url('/iclock/cdata');
            if (!isset($device['last_seen'])) {
                $device['last_seen'] = null;
            }
        }

        Setting::set('fingerspot_devices', json_encode(array_values($devices)));

        return redirect()->back()->with('success', 'Pengaturan absensi dan perangkat sidik jari berhasil disimpan.');
    }

    /**
     * Pencatatan Absensi Manual oleh Admin
     */
    public function storeManual(Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'tgl'              => 'required|date',
            'jam_masuk'        => 'nullable|date_format:H:i',
            'jam_pulang'       => 'nullable|date_format:H:i',
            'status_kehadiran' => 'required|string',
            'keterangan'       => 'nullable|string',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        $absensi = Absensi::updateOrCreate(
            ['user_id' => $targetUser->id, 'tgl' => $request->tgl],
            [
                'jam_masuk'        => $request->jam_masuk ? $request->jam_masuk . ':00' : null,
                'jam_pulang'       => $request->jam_pulang ? $request->jam_pulang . ':00' : null,
                'status_kehadiran' => $request->status_kehadiran,
                'keterangan'       => $request->keterangan,
                'pin'              => $targetUser->pin_fingerspot ?: 'MANUAL',
                'lokasi'           => 'Pencatatan Manual'
            ]
        );

        return redirect()->back()->with('success', "Data absensi {$targetUser->name} berhasil disimpan.");
    }

    /**
     * Hapus Absensi
     */
    public function destroy($id)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Import Absensi dari CSV
     */
    public function importCsv(Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header
        fgetcsv($handle);

        $imported = 0;
        $skipped = 0;

        $batasMasuk = Setting::get('absensi_batas_masuk', '08:00:00');

        while (($row = fgetcsv($handle)) !== false) {
            $pin = trim($row[0] ?? '');
            $date = trim($row[1] ?? '');
            $in = trim($row[2] ?? '') ?: null;
            $out = trim($row[3] ?? '') ?: null;

            if (!$pin || !$date) {
                $skipped++;
                continue;
            }

            try {
                $tgl = Carbon::parse($date)->toDateString();
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }

            $user = User::where('pin_fingerspot', $pin)->first();
            if (!$user) {
                $skipped++;
                continue;
            }

            $status = 'Hadir';
            if ($in && $in > $batasMasuk) {
                $status = 'Terlambat';
            }

            Absensi::updateOrCreate(
                ['user_id' => $user->id, 'tgl' => $tgl],
                [
                    'pin'              => $pin,
                    'jam_masuk'        => $in,
                    'jam_pulang'       => $out,
                    'status_kehadiran' => $status,
                    'keterangan'       => 'Import CSV',
                    'lokasi'           => 'Import File CSV'
                ]
            );
            $imported++;
        }

        fclose($handle);

        return redirect()->back()->with('success', "Import selesai: {$imported} data berhasil diimpor, {$skipped} data dilewati.");
    }

    /**
     * Export ke CSV
     */
    public function exportExcel(Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $month = (int) $request->get('month', date('n'));
        $year = (int) $request->get('year', date('Y'));
        $targetUserId = $request->get('user_id');

        $startDate = Carbon::create($year, $month, 11)->subMonth()->startOfDay();
        $endDate = Carbon::create($year, $month, 10)->endOfDay();

        $query = Absensi::with('user')
            ->whereBetween('tgl', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('user', fn($q) => $q->where('id_role', '!=', 4)->where('name', 'not like', '%facrozi%')->where('name', 'not like', '%fachrozi%'));

        if ($targetUserId && $targetUserId !== 'all') {
            $query->where('user_id', $targetUserId);
        }

        $absensis = $query->orderBy('tgl', 'asc')->get();
        $fileName = "Rekap_Absensi_{$month}_{$year}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Pegawai', 'PIN', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran', 'Lokasi', 'Keterangan'];

        $callback = function() use($absensis, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($absensis as $abs) {
                fputcsv($file, [
                    $abs->user->name,
                    $abs->pin ?? '-',
                    $abs->tgl->toDateString(),
                    $abs->jam_masuk ?? '-',
                    $abs->jam_pulang ?? '-',
                    $abs->status_kehadiran,
                    $abs->lokasi ?? '-',
                    $abs->keterangan ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export ke PDF
     */
    public function exportPdf(Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $month = (int) $request->get('month', date('n'));
        $year = (int) $request->get('year', date('Y'));
        $targetUserId = $request->get('user_id');

        $startDate = Carbon::create($year, $month, 11)->subMonth()->startOfDay();
        $endDate = Carbon::create($year, $month, 10)->endOfDay();
        $periodeLabel = $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y');

        if ($targetUserId && $targetUserId !== 'all') {
            $allUsers = User::where('id', $targetUserId)->get();
        } else {
            $allUsers = User::where('id_role', '!=', 4)
                ->where('name', 'not like', '%facrozi%')
                ->where('name', 'not like', '%fachrozi%')
                ->orderBy('name')
                ->get();
        }

        $reportData = [];
        $batasMasukStr = Setting::get('absensi_batas_masuk', '08:00:00');
        $batasMasukTime = Carbon::parse($batasMasukStr);

        foreach ($allUsers as $u) {
            $uAbsensis = Absensi::where('user_id', $u->id)
                ->whereBetween('tgl', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
            
            $hadir = $uAbsensis->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count();
            
            $workDays = 0;
            $currentDate = $startDate->copy();
            $periodEndDate = $endDate->greaterThan(now()) ? now() : $endDate;

            while ($currentDate->lessThanOrEqualTo($periodEndDate)) {
                if (!$currentDate->isWeekend()) {
                    $workDays++;
                }
                $currentDate->addDay();
            }
            
            $alpha = max(0, $workDays - $hadir);

            $totalWorkingHours = 0;
            $totalLateMinutes = 0;

            foreach ($uAbsensis as $abs) {
                if ($abs->jam_masuk && $abs->jam_pulang) {
                    try {
                        $masuk = Carbon::parse($abs->jam_masuk);
                        $pulang = Carbon::parse($abs->jam_pulang);
                        if ($pulang->greaterThan($masuk)) {
                            $totalWorkingHours += $pulang->diffInMinutes($masuk, true) / 60;
                        }
                    } catch (\Exception $e) {
                        Log::error("Error parsing attendance times for user {$u->id}: " . $e->getMessage());
                    }
                }

                if ($abs->jam_masuk) {
                    try {
                        $masuk = Carbon::parse($abs->jam_masuk);
                        if ($masuk->greaterThan($batasMasukTime)) {
                            $totalLateMinutes += $masuk->diffInMinutes($batasMasukTime, true);
                        }
                    } catch (\Exception $e) {
                        Log::error("Error parsing late hours for user {$u->id}: " . $e->getMessage());
                    }
                }
            }
            $totalWorkingHours = (int) round($totalWorkingHours);
            $totalLateHours = (int) round($totalLateMinutes / 60);

            $reportData[] = [
                'user' => $u,
                'hadir' => $hadir,
                'alpha' => $alpha,
                'jam_telat' => $totalLateHours,
                'total_jam_kerja' => $totalWorkingHours
            ];
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[$month] ?? Carbon::create()->month($month)->translatedFormat('F');
        $reportDate = now()->translatedFormat('d F Y');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kepegawaian.absensi_pdf', compact('reportData', 'monthName', 'year', 'reportDate', 'periodeLabel'))
            ->setOption('isRemoteEnabled', true);

        $fileName = "Rekap_Kehadiran_" . str_replace(' ', '_', $monthName) . "_{$year}.pdf";
        return $pdf->download($fileName);
    }

    /**
     * Kirim Rekap Absensi Manual via WhatsApp (Trigger dari UI)
     */
    public function sendRekapManual(\Illuminate\Http\Request $request)
    {
        if (Auth::user()->id_role != 1) {
            abort(403);
        }

        $month = (int) $request->get('month', now()->subMonth()->month);
        $year  = (int) $request->get('year',  now()->subMonth()->year);

        try {
            // Gunakan Artisan::call() secara direct agar tidak terhambat oleh fungsi shell yang dideaktivasi di php.ini (seperti exec/popen)
            \Illuminate\Support\Facades\Artisan::call('attendance:send-monthly-recap', [
                '--force' => true,
                '--month' => $month,
                '--year' => $year
            ]);

            $monthNames = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];

            return redirect()->route('absensi.index', [
                'tab' => 'laporan',
                'month' => $month,
                'year' => $year
            ])->with('success', "✅ Rekap absensi periode *{$monthNames[$month]} {$year}* berhasil dipicu dan dikirim ke WhatsApp!");
        } catch (\Exception $e) {
            Log::error("sendRekapManual Error: " . $e->getMessage());
            return redirect()->route('absensi.index', [
                'tab' => 'laporan',
                'month' => $month,
                'year' => $year
            ])->with('error', 'Gagal mengirim rekap: ' . $e->getMessage());
        }
    }
}
