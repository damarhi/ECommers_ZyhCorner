<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    /**
     * Default list of operational areas in Pelalawan & Riau used as fast local fallback
     * when the Biteship API key is not configured or during network disruptions.
     */
    public static array $defaultAreas = [
        ['id' => 'ID_PLW_01', 'name' => 'Pangkalan Kerinci Kota', 'city' => 'Pangkalan Kerinci, Pelalawan, Riau', 'postal' => '28654'],
        ['id' => 'ID_PLW_02', 'name' => 'Pangkalan Kerinci Barat', 'city' => 'Pangkalan Kerinci, Pelalawan, Riau', 'postal' => '28381'],
        ['id' => 'ID_PLW_03', 'name' => 'Pangkalan Kerinci Timur', 'city' => 'Pangkalan Kerinci, Pelalawan, Riau', 'postal' => '28654'],
        ['id' => 'ID_PLW_04', 'name' => 'Kuala Terusan', 'city' => 'Pangkalan Kerinci, Pelalawan, Riau', 'postal' => '28654'],
        ['id' => 'ID_PLW_05', 'name' => 'Makmur', 'city' => 'Pangkalan Kerinci, Pelalawan, Riau', 'postal' => '28654'],
        ['id' => 'ID_PLW_06', 'name' => 'Bandar Seikijang', 'city' => 'Pelalawan, Riau', 'postal' => '28383'],
        ['id' => 'ID_PLW_07', 'name' => 'Pangkalan Kuras / Sorek', 'city' => 'Pelalawan, Riau', 'postal' => '28382'],
        ['id' => 'ID_PKU_01', 'name' => 'Sukajadi', 'city' => 'Pekanbaru, Riau', 'postal' => '28121'],
        ['id' => 'ID_PKU_02', 'name' => 'Tampan / Tuah Madani', 'city' => 'Pekanbaru, Riau', 'postal' => '28292'],
        ['id' => 'ID_PKU_03', 'name' => 'Marpoyan Damai', 'city' => 'Pekanbaru, Riau', 'postal' => '28282'],
        ['id' => 'ID_PKU_04', 'name' => 'Bukit Raya', 'city' => 'Pekanbaru, Riau', 'postal' => '28288'],
        ['id' => 'ID_PKU_05', 'name' => 'Payung Sekaki', 'city' => 'Pekanbaru, Riau', 'postal' => '28291'],
        ['id' => 'ID_PKU_06', 'name' => 'Rumbai / Rumbai Pesisir', 'city' => 'Pekanbaru, Riau', 'postal' => '28261'],
        ['id' => 'ID_PKU_07', 'name' => 'Tenayan Raya / Kulim', 'city' => 'Pekanbaru, Riau', 'postal' => '28285'],
        ['id' => 'ID_PKU_08', 'name' => 'Senapelan', 'city' => 'Pekanbaru, Riau', 'postal' => '28153'],
        ['id' => 'ID_PKU_09', 'name' => 'Limapuluh', 'city' => 'Pekanbaru, Riau', 'postal' => '28144'],
        ['id' => 'ID_PKU_10', 'name' => 'Bina Widya', 'city' => 'Pekanbaru, Riau', 'postal' => '28293'],
    ];

    /**
     * Search areas across Indonesia via Biteship API, with automated local fallback.
     *
     * @param string $query
     * @return array
     */
    public static function searchAreas(string $query): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        $apiKey = config('services.biteship.api_key');

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(5)->get('https://api.biteship.com/v1/locations/areas', [
                    'countries' => 'ID',
                    'input' => $query,
                    'type' => 'single',
                ]);

                if ($response->successful()) {
                    $areas = $response->json('areas') ?? [];
                    if (!empty($areas)) {
                        return array_map(function ($item) {
                            $division2 = $item['administrative_division_level_2_name'] ?? '';
                            $division1 = $item['administrative_division_level_1_name'] ?? '';
                            $city = trim($division2 . ($division1 ? ', ' . $division1 : ''));

                            return [
                                'id' => $item['id'] ?? '',
                                'name' => $item['name'] ?? '',
                                'city' => $city,
                                'postal' => (string) ($item['postal_code'] ?? ''),
                            ];
                        }, $areas);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Biteship API area search failed: ' . $e->getMessage());
            }
        }

        // Fast local fallback for Pelalawan & Riau
        $qLower = strtolower($query);
        return array_values(array_filter(self::$defaultAreas, function ($a) use ($qLower) {
            return str_contains(strtolower($a['name']), $qLower) ||
                   str_contains(strtolower($a['city']), $qLower) ||
                   str_contains((string) $a['postal'], $qLower);
        }));
    }

    /**
     * Get real-time courier shipping rates from Biteship API or local standard tiers.
     *
     * @param string $destinationAreaId
     * @param array $items
     * @param array|null $couriers
     * @return array
     */
    public static function getRates(string $destinationAreaId, array $items = [], ?array $couriers = null): array
    {
        $apiKey = config('services.biteship.api_key');
        $originAreaId = config('services.biteship.origin_area_id', 'ID_PLW_01');

        if ($apiKey && $destinationAreaId) {
            try {
                $courierList = $couriers ?? ['gosend', 'grab', 'maxim', 'jne', 'sicepat', 'jnt'];
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(5)->post('https://api.biteship.com/v1/rates/couriers', [
                    'origin_area_id' => $originAreaId,
                    'destination_area_id' => $destinationAreaId,
                    'couriers' => implode(',', $courierList),
                    'items' => !empty($items) ? $items : [
                        [
                            'name' => 'Pesanan Kue & Roti Zyh Corner',
                            'value' => 50000,
                            'weight' => 1000,
                            'quantity' => 1,
                        ]
                    ],
                ]);

                if ($response->successful()) {
                    $pricing = $response->json('pricing') ?? [];
                    if (!empty($pricing)) {
                        return [
                            'success' => true,
                            'source' => 'biteship_api',
                            'rates' => $pricing,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Biteship getRates failed: ' . $e->getMessage());
            }
        }

        // Fallback to standard tiered rates
        return [
            'success' => true,
            'source' => 'standard_tiers',
            'rates' => self::getStandardRates(),
        ];
    }

    /**
     * Retrieve live tracking milestones for a courier waybill.
     *
     * @param string $waybillId
     * @param string|null $courierCode
     * @return array
     */
    public static function getTracking(string $waybillId, ?string $courierCode = null): array
    {
        $apiKey = config('services.biteship.api_key');

        if ($apiKey && $waybillId) {
            try {
                $url = 'https://api.biteship.com/v1/trackings/' . urlencode($waybillId);
                if ($courierCode) {
                    $url .= '/couriers/' . urlencode(strtolower($courierCode));
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(5)->get($url);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'source' => 'biteship_api',
                        'data' => $response->json(),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Biteship getTracking failed: ' . $e->getMessage());
            }
        }

        return [
            'success' => false,
            'source' => 'fallback',
            'message' => 'Layanan pelacakan real-time eksternal belum aktif. Menggunakan status internal sistem.',
            'waybill_id' => $waybillId,
        ];
    }

    /**
     * Get default standard rates for Zyh Corner shipping options.
     *
     * @return array
     */
    public static function getStandardRates(): array
    {
        return [
            [
                'service_type' => 'Instant Courier',
                'service_group' => 'Maxim',
                'description' => 'GoSend / Grab / Maxim Instant',
                'duration' => '1-2 Jam',
                'cost' => 20000,
                'icon' => 'fa-motorcycle',
            ],
            [
                'service_type' => 'Same Day Service',
                'service_group' => 'SameDay',
                'description' => 'Kuantar / Paxel / Grab Same Day',
                'duration' => '4-6 Jam',
                'cost' => 15000,
                'icon' => 'fa-truck-fast',
            ],
            [
                'service_type' => 'Ekspedisi Reguler',
                'service_group' => 'JNE',
                'description' => 'JNE / SiCepat / J&T Express',
                'duration' => '1-2 Hari',
                'cost' => 12000,
                'icon' => 'fa-box',
            ],
            [
                'service_type' => 'Ambil di Toko',
                'service_group' => 'PickUp',
                'description' => 'Pick Up langsung di Toko Zyh Corner',
                'duration' => 'Sesuai Jam Operasional',
                'cost' => 0,
                'icon' => 'fa-store',
            ],
        ];
    }

    /**
     * Map rates from Biteship API or standard fallback into Zyh Corner checkout courier options.
     *
     * @param array $ratesResult
     * @param string|null $destinationAreaId
     * @param string|null $destinationAreaName
     * @return array
     */
    public static function formatCheckoutRates(array $ratesResult, ?string $destinationAreaId = null, ?string $destinationAreaName = null): array
    {
        $destId = strtoupper((string) ($destinationAreaId ?? $ratesResult['destination_area_id'] ?? ''));
        $destName = strtolower((string) ($destinationAreaName ?? $ratesResult['destination_area_name'] ?? ''));

        // Default local base tier (Pangkalan Kerinci Kota - store origin area)
        // Differentiated realistic rates: Maxim (ekonomis), Gojek (standar), Grab (ekspres), Pick Up (gratis)
        $courierCosts = [
            'Gojek' => 18000,
            'Grab' => 20000,
            'Maxim' => 12000,
            'Pick Up' => 0,
        ];

        // If destination is in outer Pelalawan subdistricts
        if (in_array($destId, ['ID_PLW_04', 'ID_PLW_05', 'ID_PLW_06', 'ID_PLW_07', 'ID_PLW_08', 'ID_PLW_09', 'ID_PLW_10']) ||
            (str_contains($destName, 'pelalawan') && !str_contains($destName, 'kerinci kota') && !str_contains($destName, 'kerinci barat') && !str_contains($destName, 'kerinci timur'))) {
            $courierCosts = [
                'Gojek' => 28000,
                'Grab' => 30000,
                'Maxim' => 22000,
                'Pick Up' => 0,
            ];
        } elseif (str_starts_with($destId, 'ID_PKU') || str_contains($destName, 'pekanbaru')) {
            // Pekanbaru city zone
            $courierCosts = [
                'Gojek' => 42000,
                'Grab' => 45000,
                'Maxim' => 35000,
                'Pick Up' => 0,
            ];
        } elseif (!empty($destId) && !str_starts_with($destId, 'ID_PLW')) {
            // Other regencies in Riau or national
            $courierCosts = [
                'Gojek' => 48000,
                'Grab' => 50000,
                'Maxim' => 40000,
                'Pick Up' => 0,
            ];
        }

        // If live API returned valid pricing with positive amounts, override with real-time API values
        if (!empty($ratesResult['rates']) && ($ratesResult['source'] ?? '') === 'biteship_api') {
            foreach ($ratesResult['rates'] as $rate) {
                $comp = strtolower($rate['company'] ?? '');
                $courier = strtolower($rate['courier_name'] ?? '');
                $price = (int) ($rate['price'] ?? 0);

                if ($price > 0) {
                    if (str_contains($comp, 'gojek') || str_contains($courier, 'gojek') || str_contains($comp, 'gosend')) {
                        $courierCosts['Gojek'] = $price;
                    } elseif (str_contains($comp, 'grab') || str_contains($courier, 'grab')) {
                        $courierCosts['Grab'] = $price;
                    } elseif (str_contains($comp, 'maxim') || str_contains($courier, 'maxim')) {
                        $courierCosts['Maxim'] = $price;
                    }
                }
            }
        }

        return $courierCosts;
    }
}
