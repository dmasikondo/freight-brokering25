<?php

namespace App\Services;

use App\Models\Lane;
use App\Models\Freight;
use Illuminate\Support\Collection;

class FreightMatchingService
{
    /**
     * Find matching lanes for a specific freight load.
     */
    public function findMatchesForFreight(Freight $freight): Collection
    {
        return Lane::query()
            ->where('status', 'submitted')
            // 1. Exact Match on Trailer Type
            ->where('trailer', $freight->vehicle_type)
            // 2. Route Match (Origin/Destination)
            ->where('countryfrom', $freight->countryfrom)
            ->where('countryto', $freight->countryto)
            // 3. Date Match (Lanes available on or after freight date)
            ->where('availability_date', '<=', $freight->datefrom)
            ->get()
            ->map(function ($lane) use ($freight) {
                // Calculate how much of the load this specific truck covers
                $coverage = $this->calculateCoverage($lane, $freight);
                
                return [
                    'lane' => $lane,
                    'match_percentage' => $this->calculateMatchScore($lane, $freight),
                    'trucks_needed' => ceil($freight->weight / ($lane->capacity ?: 1)),
                    'covers_full_load' => $lane->capacity >= $freight->weight,
                ];
            })
            ->sortByDesc('match_percentage');
    }

    private function calculateMatchScore(Lane $lane, Freight $freight): int
    {
        $score = 0;
        
        // Cities match (Higher priority than just country)
        if ($lane->cityfrom === $freight->cityfrom) $score += 40;
        if ($lane->cityto === $freight->cityto) $score += 40;

        // Capacity Unit alignment
        if ($lane->capacity_unit === $freight->capacity_unit) $score += 20;

        return min($score, 100);
    }

    private function calculateCoverage(Lane $lane, Freight $freight): float
    {
        if ($lane->capacity <= 0) return 0;
        return ($lane->capacity / $freight->weight) * 100;
    }
}