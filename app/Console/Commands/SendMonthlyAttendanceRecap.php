<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\User;
use App\Models\Absensi;
use App\Services\WhatsappClient;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class SendMonthlyAttendanceRecap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-monthly-recap {--force : Force send the recap for a specific month and year} {--month= : Month to generate (1-12)} {--year= : Year to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the monthly employee attendance recap PDF via WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);

        // Get target phone number
        $targetPhone = Setting::get('wa_rekap_absensi_target', '6282187827382');
        if (empty($targetPhone)) {
            $this->error('Target WhatsApp number is not configured.');
            return;
        }

        // Sanitasi nomor WA: hapus spasi, tanda +, dan strip (format harus 628xxx)
        $targetPhone = preg_replace('/[\s\-\(\)]/', '', $targetPhone);  // hapus spasi, strip, kurung
        $targetPhone = ltrim($targetPhone, '+');                         // hapus leading +
        if (str_starts_with($targetPhone, '0')) {
            $targetPhone = '62' . substr($targetPhone, 1);              // 08xxx → 628xxx
        }
        $this->info("Target phone (sanitized): {$targetPhone}");

        // Determine month and year
        if ($this->option('force') && $this->option('month') && $this->option('year')) {
            $month = (int)$this->option('month');
            $year = (int)$this->option('year');
        } else {
            // Default to previous month
            $previousMonth = now()->subMonth();
            $month = $previousMonth->month;
            $year = $previousMonth->year;
        }

        $this->info("Generating employee attendance recap for {$month}/{$year}...");

        // Fetch users (exclude customers: id_role == 4)
        $allUsers = User::where('id_role', '!=', 4)->orderBy('name')->get();

        if ($allUsers->isEmpty()) {
            $this->warn('No employees found to generate attendance recap.');
            return;
        }

        $reportData = [];
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        foreach ($allUsers as $u) {
            $uAbsensis = Absensi::where('user_id', $u->id)
                ->whereMonth('tgl', $month)
                ->whereYear('tgl', $year)
                ->get();
            
            $hadir = $uAbsensis->whereIn('status_kehadiran', ['Hadir', 'Terlambat', 'Pulang Lebih Awal', 'Terlambat & Pulang Awal'])->count();
            
            $workDays = 0;
            $maxDay = ($month == (int)date('n') && $year == (int)date('Y')) ? (int)date('j') : $daysInMonth;

            for ($d = 1; $d <= $maxDay; $d++) {
                $carbonDate = Carbon::create($year, $month, $d);
                if (!$carbonDate->isWeekend()) {
                    $workDays++;
                }
            }
            
            $alpha = max(0, $workDays - $hadir);

            $reportData[] = [
                'user' => $u,
                'hadir' => $hadir,
                'alpha' => $alpha,
                'persentase' => $workDays > 0 ? round(($hadir / $workDays) * 100) : 0
            ];
        }

        // Indonesian Month Mapping
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[$month] ?? Carbon::create()->month($month)->translatedFormat('F');

        // Formatted Date for Signatures
        $reportDate = now()->translatedFormat('d F Y');

        // Generate PDF
        $pdf = Pdf::loadView('kepegawaian.absensi_pdf', compact('reportData', 'monthName', 'year', 'reportDate'))
            ->setOption('isRemoteEnabled', true);
        $pdfContent = $pdf->output();

        $filename = "Rekap_Kehadiran_{$monthName}_{$year}.pdf";

        // Save a backup locally in storage
        try {
            $storagePath = 'rekap_absensi/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }
            file_put_contents($fullPath, $pdfContent);
            $this->info("Local PDF copy saved at: " . $fullPath);
        } catch (\Exception $e) {
            Log::warning("SendMonthlyAttendanceRecap: Could not save copy of PDF to storage: " . $e->getMessage());
        }

        $caption = "Berikut adalah laporan *Rekapitulasi Kehadiran Pegawai* Rozitech Multimedia Indonesia untuk periode *{$monthName} {$year}* dalam format PDF.";

        $this->info("Sending PDF to {$targetPhone} via WhatsApp...");

        $waClient = new WhatsappClient();
        $success = $waClient->sendFile($targetPhone, $pdfContent, $filename, 'application/pdf', $caption);

        if ($success) {
            $this->info("Successfully sent attendance recap to {$targetPhone}.");
            Log::info("SendMonthlyAttendanceRecap: Attendance recap for {$monthName} {$year} sent to {$targetPhone} successfully.");
        } else {
            $this->error("Failed to send attendance recap to {$targetPhone}.");
            Log::error("SendMonthlyAttendanceRecap: Failed to send attendance recap for {$monthName} {$year} to {$targetPhone}.");
        }
    }
}
