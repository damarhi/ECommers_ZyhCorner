<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_with_redesigned_layout_and_contracts()
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'customer@example.com'
        ]);

        Customer::create([
            'user_id' => $user->user_id,
            'name' => 'Budi Santoso',
            'location' => 'Jl. Kenanga No. 12',
            'contact' => '081234567890',
        ]);

        $product = Produk::create([
            'nama_produk' => 'Bento Cake Custom',
            'harga' => 85000,
            'status_produk' => '10',
            'bisa_custom_teks' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('checkout'));

        $response->assertStatus(200);

        // Fixed typo check
        $response->assertSee('Ringkasan Pesanan');
        $response->assertDontSee('Order Summery');

        // 4-step sequence verification
        $response->assertSee('Alamat Pengiriman & Titik Lokasi', false);
        $response->assertSee('Tulisan di Atas Kue', false);
        $response->assertSee('Metode Pengiriman', false);
        $response->assertSee('Metode Pembayaran', false);

        // Form contracts verification
        $response->assertSee('id="recipientName"', false);
        $response->assertSee('id="recipientPhone"', false);
        $response->assertSee('id="areaSearchInput"', false);
        $response->assertSee('id="destinationAreaId"', false);
        $response->assertSee('id="postalCode"', false);
        $response->assertSee('id="areaDropdownResults"', false);
        $response->assertSee('id="shippingRatesContainer"', false);
        $response->assertSee('name="recipient_name"', false);
        $response->assertSee('name="recipient_phone"', false);
        $response->assertSee('name="postal_code"', false);
        $response->assertSee('id="streetAddress"', false);
        $response->assertSee('id="map"', false);
        $response->assertSee('id="latitude"', false);
        $response->assertSee('id="longitude"', false);
        $response->assertSee('id="coordsDisplay"', false);
        $response->assertSee('id="orderNotes"', false);
        $response->assertSee('id="customCakeTextCard"', false);
        $response->assertSee('id="customTextInputsContainer"', false);
        $response->assertSee('id="orderItemsSummary"', false);
        $response->assertSee('id="summarySubtotal"', false);
        $response->assertSee('id="summaryTotal"', false);
        $response->assertSee('id="payCod"', false);
        $response->assertSee('id="payTransfer"', false);
        $response->assertSee('id="payEwallet"', false);
        $response->assertDontSee('Pembayaran digital instan via Midtrans Snap', false);
        $response->assertDontSee('id="transferSubOptions"', false);
        $response->assertDontSee('id="ewalletSubOptions"', false);
        $response->assertSee('btn-place-order', false);
        $response->assertSee('name="paymentGroup"', false);
        $response->assertSee('name="shippingGroup"', false);
        $response->assertDontSee('name="bankSelect"', false);
        $response->assertDontSee('name="walletSelect"', false);
        $response->assertDontSee('Pastry Segar Dibuat Setiap Hari', false);
        $response->assertDontSee('Konfirmasi Pesanan Cepat', false);

        // Step 2 Simplified Shipping Methods
        $response->assertSee('value="Gojek"', false);
        $response->assertSee('value="Grab"', false);
        $response->assertSee('value="Maxim"', false);
        $response->assertSee('value="Pick Up"', false);
        $response->assertSee('Pick Up di Tempat', false);
        $response->assertDontSee('GoSend / Grab / Maxim Instant', false);
        $response->assertDontSee('Kuantar / Paxel / Grab Same Day', false);
        $response->assertDontSee('JNE / SiCepat / J&T Express', false);
        $response->assertDontSee('Ekspedisi Reguler', false);
        $response->assertDontSee('Same Day Service', false);

        // DOM Hierarchy & Merged Order Summary Verification
        $html = $response->getContent();
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // Left column should contain address, shipping methods (Step 2), and custom cake card (Step 3)
        $leftCol = $xpath->query('//div[contains(@class, "checkout-main-col")]')->item(0);
        $this->assertNotNull($leftCol);
        $leftShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $leftCol);
        $this->assertEquals(1, $leftShipping->length, 'Shipping options must be in the left column (Step 2)');
        $leftPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $leftCol);
        $this->assertEquals(0, $leftPayment->length, 'Payment options must not be in the left column');

        // Right column (Order Summary Card) contains product summary, payment options, totals, and the CTA button
        $summaryCard = $xpath->query('//div[contains(@class, "sticky-summary-card")]')->item(0);
        $this->assertNotNull($summaryCard);
        $summaryShipping = $xpath->query('.//div[contains(@class, "shipping-grid")]', $summaryCard);
        $this->assertEquals(0, $summaryShipping->length, 'Shipping options are now in left column for balanced layout');
        $summaryPayment = $xpath->query('.//div[contains(@class, "payment-options")]', $summaryCard);
        $this->assertEquals(1, $summaryPayment->length, 'Payment options must be inside the summary card');
        $summaryCta = $xpath->query('.//button[contains(@class, "btn-place-order")]', $summaryCard);
        $this->assertEquals(1, $summaryCta->length, 'Place order button must be inside the summary card');

        // Verify CTA button appears after summary totals in the summary card
        $totalsPos = strpos($html, 'class="summary-totals"');
        $ctaPos = strpos($html, 'class="btn-place-order"');
        $this->assertGreaterThan($totalsPos, $ctaPos, 'CTA button must be placed at the bottom after totals');
    }
}
