<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\KnnParameter;
use App\Models\KnnHasil;
use App\Models\KnnDetailTetangga;

class KnnService
{
    /**
     * Calculate 4-Dimensional Euclidean Distance (as per thesis)
     * Features: Latitude (scaled * 10^5), Longitude (scaled * 10^5), Usage_GB, Jumlah_Device
     */
    public function euclideanDistance4D($lat1, $lon1, $usage1, $dev1, $lat2, $lon2, $usage2, $dev2)
    {
        // Scale coordinates by 10^5 (100,000) as per thesis manual calculation (e.g. Sari vs Ruqoiyah)
        $scaledLat1 = (double)$lat1 * 100000;
        $scaledLon1 = (double)$lon1 * 100000;
        
        $scaledLat2 = (double)$lat2 * 100000;
        $scaledLon2 = (double)$lon2 * 100000;

        return sqrt(
            pow($scaledLat1 - $scaledLat2, 2) +
            pow($scaledLon1 - $scaledLon2, 2) +
            pow((double)$usage1 - (double)$usage2, 2) +
            pow((double)$dev1 - (double)$dev2, 2)
        );
    }

    /**
     * Classify a customer based on training data
     * Features: Latitude, Longitude, Usage_GB, Jumlah_Device
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
            $dist = $this->euclideanDistance4D(
                $target->latitude, $target->longitude, $target->usage_gb, $target->jumlah_device,
                $train->latitude, $train->longitude, $train->usage_gb, $train->jumlah_device
            );
            
            $distances[] = [
                'id_pelanggan' => $train->id_pelanggan,
                'distance' => $dist,
                'label' => $train->prioritas_label,
                'nama' => $train->nama_pelanggan
            ];
        }

        // Sort by distance (smallest to largest)
        usort($distances, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // Take K nearest neighbors
        $neighbors = array_slice($distances, 0, $k);

        // Voting Label
        $votes = [];
        foreach ($neighbors as $n) {
            $label = strtoupper($n['label']);
            
            // Standardize labels to match thesis EXACTLY
            if ($label === 'SANGAT PRIORITAS' || $label === 'HIGH') {
                $label = 'HIGH';
            } elseif ($label === 'PRIORITAS' || $label === 'MEDIUM') {
                $label = 'MEDIUM';
            } else {
                $label = 'LOW';
            }
            
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        arsort($votes);
        $resultLabel = key($votes);

        // Store KNN Parameter
        $param = KnnParameter::create([
            'nilai_k' => $k,
            'distance_metric' => 'Euclidean (4D) - Thesis Match'
        ]);

        // Store KNN Classification result
        $hasil = KnnHasil::create([
            'id_pelanggan' => $targetPelangganId,
            'id_knn_param' => $param->id_knn_param,
            'jarak_min' => $neighbors[0]['distance'],
            'label_hasil' => $resultLabel
        ]);

        // Store details of K nearest neighbors
        foreach ($neighbors as $index => $n) {
            $neighborLabel = strtoupper($n['label']);
            if ($neighborLabel === 'SANGAT PRIORITAS' || $neighborLabel === 'HIGH') {
                $neighborLabel = 'HIGH';
            } elseif ($neighborLabel === 'PRIORITAS' || $neighborLabel === 'MEDIUM') {
                $neighborLabel = 'MEDIUM';
            } else {
                $neighborLabel = 'LOW';
            }

            KnnDetailTetangga::create([
                'id_knn_hasil' => $hasil->id_knn_hasil,
                'urutan' => $index + 1,
                'id_pelanggan_tetangga' => $n['id_pelanggan'],
                'jarak_euclidean' => $n['distance'],
                'label_tetangga' => $neighborLabel
            ]);
        }

        // Update target customer label to standard value
        $target->update(['prioritas_label' => $resultLabel]);

        return $hasil;
    }
}
