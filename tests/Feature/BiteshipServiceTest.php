<?php

namespace Tests\Feature;

use App\Services\BiteshipService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BiteshipServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_search_areas_returns_local_fallback_when_api_key_not_set(): void
    {
        config(['services.biteship.api_key' => null]);

        $results = BiteshipService::searchAreas('Pangkalan Kerinci');

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('Pangkalan Kerinci', $results[0]['name']);
        $this->assertEquals('28654', $results[0]['postal']);
    }

    public function test_search_areas_calls_biteship_api_when_api_key_provided(): void
    {
        config(['services.biteship.api_key' => 'mock-biteship-api-key']);

        Http::fake([
            'https://api.biteship.com/v1/locations/areas*' => Http::response([
                'success' => true,
                'areas' => [
                    [
                        'id' => 'BITESHIP_AREA_99',
                        'name' => 'Kerinci Kota',
                        'administrative_division_level_1_name' => 'Riau',
                        'administrative_division_level_2_name' => 'Pelalawan',
                        'postal_code' => 28654,
                    ]
                ]
            ], 200),
        ]);

        $results = BiteshipService::searchAreas('Kerinci');

        $this->assertNotEmpty($results);
        $this->assertEquals('BITESHIP_AREA_99', $results[0]['id']);
        $this->assertEquals('Kerinci Kota', $results[0]['name']);
        $this->assertEquals('Pelalawan, Riau', $results[0]['city']);
        $this->assertEquals('28654', $results[0]['postal']);
    }

    public function test_biteship_areas_route_returns_json(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson(route('biteship.areas', ['search' => 'Pekanbaru']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'city', 'postal']
        ]);
    }

    public function test_get_rates_returns_standard_tiers_fallback(): void
    {
        config(['services.biteship.api_key' => null]);

        $result = BiteshipService::getRates('ID_PLW_01');

        $this->assertTrue($result['success']);
        $this->assertEquals('standard_tiers', $result['source']);
        $this->assertCount(4, $result['rates']);
    }

    public function test_biteship_rates_route_returns_json_and_couriers_mapping(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->postJson(route('biteship.rates'), [
            'destination_area_id' => 'ID_PLW_01',
            'destination_area_name' => 'Pangkalan Kerinci Kota'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'source',
            'destination_area_id',
            'couriers' => ['Gojek', 'Grab', 'Maxim', 'Pick Up'],
            'rates'
        ]);
        $this->assertEquals(12000, $response->json('couriers.Maxim'));
        $this->assertEquals(18000, $response->json('couriers.Gojek'));
        $this->assertEquals(20000, $response->json('couriers.Grab'));
        $this->assertEquals(0, $response->json('couriers.Pick Up'));
    }

    public function test_biteship_rates_adapts_to_pekanbaru_zone(): void
    {
        $response = $this->postJson(route('biteship.rates'), [
            'destination_area_id' => 'ID_PKU_01',
            'destination_area_name' => 'Sukajadi, Pekanbaru'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(35000, $response->json('couriers.Maxim'));
        $this->assertEquals(42000, $response->json('couriers.Gojek'));
        $this->assertEquals(45000, $response->json('couriers.Grab'));
        $this->assertEquals(0, $response->json('couriers.Pick Up'));
    }

    public function test_format_checkout_rates_maps_api_pricing_accurately(): void
    {
        $mockRatesResult = [
            'success' => true,
            'source' => 'biteship_api',
            'rates' => [
                ['company' => 'gojek', 'courier_name' => 'Gojek Instant', 'price' => 22000],
                ['company' => 'grab', 'courier_name' => 'Grab Express', 'price' => 25000],
                ['company' => 'maxim', 'courier_name' => 'Maxim Delivery', 'price' => 18000],
            ]
        ];

        $formatted = BiteshipService::formatCheckoutRates($mockRatesResult);

        $this->assertEquals(22000, $formatted['Gojek']);
        $this->assertEquals(25000, $formatted['Grab']);
        $this->assertEquals(18000, $formatted['Maxim']);
        $this->assertEquals(0, $formatted['Pick Up']);
    }
    public function test_redirect_to_google_fails_safely_when_client_id_missing(): void
    {
        config(['services.google.client_id' => null]);

        $response = $this->get(route('login.google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }
}
