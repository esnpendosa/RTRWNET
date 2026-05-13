<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pelanggan;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPelangganCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pelanggan {file=all data pelanggan.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data pelanggan dari file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $filePath = base_path($file);

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return;
        }

        $this->info("Membaca file: {$file}...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header (row 1)
            $header = array_shift($rows);
            
            $this->info("Memproses data...");
            $imported = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                // Column mapping based on my analysis of sheet1.xml
                // A: No, B: Nama, C: ID, D: Usage/Value
                $namaRaw = $row[1] ?? null;
                $kode = $row[2] ?? null;
                $usage = $row[3] ?? 0;

                // Use one as fallback for another
                if (!$namaRaw && $kode) {
                    $namaRaw = $kode;
                } elseif (!$kode && $namaRaw) {
                    $kode = $namaRaw;
                }

                // If both are still empty, then skip
                if (!$namaRaw && !$kode) {
                    $skipped++;
                    continue;
                }

                // Extract address from name prefix
                $parts = explode('_', $namaRaw);
                if (count($parts) > 1) {
                    $alamatParts = [];
                    foreach ($parts as $part) {
                        // If it looks like a name (usually at the end or long), stop adding to address
                        if (strlen($part) > 12 && count($alamatParts) >= 1) break;
                        $alamatParts[] = strtoupper($part);
                        // If it's pure location info like RT, keep going but maybe stop soon
                        if (strpos(strtolower($part), 'rt') !== false) {
                            // keep it
                        }
                    }
                    $alamat = implode(' ', $alamatParts);
                } else {
                    $alamat = "LERAN, MANYAR, GRESIK";
                }

                // Clean up name
                $nama = trim(str_replace('_', ' ', $namaRaw));

                Pelanggan::updateOrCreate(
                    ['kode_pelanggan' => $kode],
                    [
                        'nama_pelanggan' => $nama,
                        'alamat' => $alamat . ", MANYAR, GRESIK",
                        'usage_gb' => is_numeric($usage) ? (float)$usage : 0,
                        'jumlah_device' => 1,
                        'prioritas_label' => 'Regular',
                        'latitude' => 0.0,
                        'longitude' => 0.0,
                    ]
                );

                $imported++;
            }

            $this->info("Import selesai!");
            $this->info("Berhasil: {$imported}");
            $this->info("Dilewati: {$skipped}");

        } catch (\Exception $e) {
            $this->error("Gagal melakukan import: " . $e->getMessage());
        }
    }
}
