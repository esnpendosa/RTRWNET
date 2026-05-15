<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OdcOdp;

class OdcOdpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $odc1 = OdcOdp::create([
            'nama' => 'ODC-KEDUNG-01',
            'tipe' => 'ODC',
            'latitude' => -7.12383540,
            'longitude' => 112.59269178,
            'foto' => 'https://via.placeholder.com/300x200?text=Foto+ODC+01',
            'deskripsi' => 'Central ODC Area Kedung'
        ]);

        OdcOdp::create([
            'nama' => 'ODP-AD-10',
            'tipe' => 'ODP',
            'latitude' => -7.12313143,
            'longitude' => 112.58978426,
            'parent_id' => $odc1->id,
            'foto' => 'https://via.placeholder.com/300x200?text=Foto+ODP+AD+10',
            'deskripsi' => 'ODP Pelanggan Area AD-10'
        ]);

        OdcOdp::create([
            'nama' => 'ODP-AC-05',
            'tipe' => 'ODP',
            'latitude' => -7.12450000,
            'longitude' => 112.59500000,
            'parent_id' => $odc1->id,
            'foto' => 'https://via.placeholder.com/300x200?text=Foto+ODP+AC+05',
            'deskripsi' => 'ODP Area AC-05'
        ]);
    }
}
