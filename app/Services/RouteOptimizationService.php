<?php

namespace App\Services;

class RouteOptimizationService
{
    /**
     * Euclidean distance between two lat/lng points (KNN Method as per thesis)
     */
    public function euclideanDistance($lat1, $lon1, $lat2, $lon2)
    {
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // Euclidean Distance formula: D = sqrt((x2-x1)^2 + (y2-y1)^2)
        $distanceInDegrees = sqrt(pow($dLat, 2) + pow($dLon, 2));
        
        // Convert distance in degrees to kilometers (1 degree ≈ 111.32 km)
        return $distanceInDegrees * 111.32;
    }

    /**
     * Get Priority Level (Lower is higher priority)
     */
    private function getPriorityLevel($label)
    {
        $label = strtolower(trim($label));
        if ($label === 'high' || $label === 'sangat prioritas') return 1;
        if ($label === 'medium' || $label === 'prioritas') return 2;
        if ($label === 'low' || $label === 'tidak prioritas') return 3;
        return 4;
    }

    /**
     * Optimize route using K-Nearest Neighbor (KNN) logic approach
     * prioritizing based on priority label (High -> Medium -> Low)
     */
    public function optimize($startLat, $startLng, $locations)
    {
        $unvisited = $locations;
        $route = [];
        $currentLat = $startLat;
        $currentLng = $startLng;
        $totalDistance = 0;

        while (!empty($unvisited)) {
            $nearestIndex = -1;
            $minDist = PHP_INT_MAX;

            // 1. Find the highest priority available in unvisited locations
            $highestPriorityLevel = 4;
            foreach ($unvisited as $loc) {
                $lvl = $this->getPriorityLevel($loc['prioritas_label'] ?? '');
                if ($lvl < $highestPriorityLevel) {
                    $highestPriorityLevel = $lvl;
                }
            }

            // 2. KNN (K=1, finding the nearest neighbor) among the highest priority locations
            foreach ($unvisited as $index => $loc) {
                $locPriority = $this->getPriorityLevel($loc['prioritas_label'] ?? '');
                
                if ($locPriority === $highestPriorityLevel) {
                    $dist = $this->euclideanDistance($currentLat, $currentLng, $loc['latitude'], $loc['longitude']);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $nearestIndex = $index;
                    }
                }
            }

            if ($nearestIndex !== -1) {
                $nearest = $unvisited[$nearestIndex];
                $nearest['distance_from_previous'] = $minDist;
                
                // Assuming average speed 30 km/h (0.5 km per minute)
                $nearest['estimasi_waktu_menit'] = ceil($minDist / 0.5);
                
                $totalDistance += $minDist;
                
                $route[] = $nearest;
                
                $currentLat = $nearest['latitude'];
                $currentLng = $nearest['longitude'];
                
                unset($unvisited[$nearestIndex]);
                // Re-index unvisited
                $unvisited = array_values($unvisited);
            }
        }

        return [
            'route' => $route,
            'total_distance_km' => $totalDistance
        ];
    }
}
