<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\KnnParameter;
use App\Models\KnnHasil;
use App\Models\KnnDetailTetangga;

class KnnService
{
    /**
     * Calculate Haversine Distance between two coordinates in Kilometers
     */
    public function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Classify a customer based on training data
     * Features: Location (Spatial KNN), Usage, Device, Price
     */
    public function classify($targetPelangganId, $k = 3)
    {
        $target = Pelanggan::findOrFail($targetPelangganId);
        
        // Training data: Pelanggan who already have a label
        $trainingSet = Pelanggan::whereNotNull('prioritas_label')
            ->where('id_pelanggan', '!=', $targetPelangganId)
            ->get();

        if ($trainingSet->count() < $k) {
            return null; // Not enough data
        }

        $distances = [];
        foreach ($trainingSet as $train) {
            // Jarak Fisik Nyata (KM) - Sesuai Kebutuhan Optimasi Peta
            $distKM = $this->haversineDistance(
                $target->latitude, $target->longitude,
                $train->latitude, $train->longitude
            );
            
            $distances[] = [
                'id_pelanggan' => $train->id_pelanggan,
                'distance_km' => $distKM,
                'label' => $train->prioritas_label
            ];
        }

        // Urutkan berdasarkan jarak terdekat (KM)
        usort($distances, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });

        // Ambil K Tetangga Terdekat secara Spasial
        $neighbors = array_slice($distances, 0, $k);

        // Voting Label
        $votes = [];
        foreach ($neighbors as $n) {
            $label = $n['label'];
            if ($label == 'High') $label = 'Sangat Prioritas';
            if ($label == 'Medium') $label = 'Prioritas';
            if ($label == 'Low') $label = 'Tidak Prioritas';
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        arsort($votes);
        $resultLabel = key($votes);

        // Simpan Hasil dengan Jarak KM yang mudah dibaca
        $param = KnnParameter::create([
            'nilai_k' => $k,
            'distance_metric' => 'Haversine (KM) - Map Optimized'
        ]);

        $hasil = KnnHasil::create([
            'id_pelanggan' => $targetPelangganId,
            'id_knn_param' => $param->id_knn_param,
            'jarak_min' => $neighbors[0]['distance_km'], // Menggunakan KM
            'label_hasil' => $resultLabel
        ]);

        foreach ($neighbors as $index => $n) {
            KnnDetailTetangga::create([
                'id_knn_hasil' => $hasil->id_knn_hasil,
                'urutan' => $index + 1,
                'id_pelanggan_tetangga' => $n['id_pelanggan'],
                'jarak_euclidean' => $n['distance_km'],
                'label_tetangga' => $n['label']
            ]);
        }

        // Update pelanggan label
        $target->update(['prioritas_label' => $resultLabel]);

        return $hasil;
    }
}
