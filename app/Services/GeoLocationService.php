<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Reverse geocode a latitude & longitude into a structured address object.
     * Caches successful results for 24 hours to avoid redundant network requests.
     *
     * @param float|string $latitude
     * @param float|string $longitude
     * @return array|null
     */
    public static function reverseGeocode($latitude, $longitude): ?array
    {
        $lat = floatval($latitude);
        $lng = floatval($longitude);

        if ($lat == 0.0 && $lng == 0.0) {
            return null;
        }

        // Cache key with 5 decimals precision (~1 meter resolution)
        $cacheKey = sprintf('geo_rev_%.5f_%.5f', $lat, $lng);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'ZyhCorner-BakeryApp/1.0 (contact@zyhcorner.com)',
                    'Accept-Language' => 'id,en;q=0.9',
                ])->timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data) && !empty($data['address'])) {
                        $addr = $data['address'];
                        $road = $addr['road'] ?? $addr['highway'] ?? $addr['pedestrian'] ?? null;
                        $place = $data['name'] ?? null;
                        if ($place === $road) {
                            $place = null;
                        }

                        $areaParts = array_filter([
                            $addr['suburb'] ?? $addr['village'] ?? $addr['neighbourhood'] ?? null,
                            $addr['district'] ?? $addr['city_district'] ?? null,
                            $addr['city'] ?? $addr['county'] ?? $addr['town'] ?? null,
                            $addr['state'] ?? null,
                        ]);

                        return [
                            'place_name' => $place,
                            'road' => $road,
                            'area' => implode(', ', array_unique($areaParts)),
                            'postal_code' => $addr['postcode'] ?? null,
                            'display_name' => $data['display_name'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Nominatim reverse geocode lookup failed: ' . $e->getMessage());
            }

            return null;
        });
    }
}
