<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PelangganMapsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Mapping berdasarkan Excel (KODE => URL)
        $data = [
            // Seri AD (Area Kedung Febi / Marist)
            'AD01' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD02' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD03' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD04' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD05' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD06' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD07' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD08' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD09' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD10' => 'https://www.google.com/maps?q=-7.12313143,112.58978426',
            'AD11' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD12' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD13' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD14' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD15' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD16' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD17' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD18' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD19' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AD20' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            
            // Seri AC (Area Kedung Febi 2)
            'AC01' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AC02' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AC03' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AC04' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AC05' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'AC06' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            
            // Seri KTR (Kantor / Central)
            'KTR01' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'KTR02' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
            'KTR03' => 'https://www.google.com/maps?q=-7.12383540,112.59269178',
        ];

        $updatedCount = 0;
        foreach ($data as $kode => $url) {
            $affected = \App\Models\Pelanggan::where('kode_pelanggan', 'like', $kode)
                ->update(['maps_url' => $url]);
            $updatedCount += $affected;
        }
        
        $this->command->info("Berhasil mengupdate {$updatedCount} data lokasi dari " . count($data) . " mapping.");
    }
}
