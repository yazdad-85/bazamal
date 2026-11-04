<?php

namespace App\Services;

use App\Models\DeliveryVillage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VillageDistanceService
{
    /**
     * Store the road distance from the origin to every paid village. Villages the router
     * cannot reach keep their current distance.
     *
     * @return array{updated: int, unreachable: int}
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws RuntimeException
     */
    public function recalculateFrom(string $originLatitude, string $originLongitude): array
    {
        $villages = DeliveryVillage::query()
            ->whereNotIn('district', DeliveryVillage::FREE_DELIVERY_DISTRICTS)
            ->orderBy('id')
            ->get();
        $coordinates = $villages
            ->map(fn (DeliveryVillage $village): string => $village->longitude.','.$village->latitude)
            ->prepend($originLongitude.','.$originLatitude)
            ->implode(';');

        $distances = Http::withUserAgent('BazarAmal/1.0 (+'.config('app.url').')')
            ->connectTimeout(5)
            ->timeout(30)
            ->get(config('services.routing.url').'/table/v1/driving/'.$coordinates, [
                'sources' => 0,
                'annotations' => 'distance',
            ])
            ->throw()
            ->json('distances.0');

        if (! is_array($distances) || count($distances) !== $villages->count() + 1) {
            throw new RuntimeException('The routing service returned an unexpected distance table.');
        }

        $updated = 0;

        foreach ($villages->values() as $index => $village) {
            $meters = $distances[$index + 1];

            if (is_numeric($meters)) {
                $village->update(['distance_km' => round($meters / 1000, 2)]);
                $updated++;
            }
        }

        return ['updated' => $updated, 'unreachable' => $villages->count() - $updated];
    }
}
