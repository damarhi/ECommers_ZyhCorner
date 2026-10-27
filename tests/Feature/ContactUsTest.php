<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_us_page_loads_successfully(): void
    {
        $response = $this->get(route('contact'));
        $response->assertStatus(200);
        $response->assertSee('Contact Us - Zyh Corner');
        $response->assertSee('Alamat Toko');
        $response->assertSee('Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294');
        $response->assertSee('Surel Kami');
        $response->assertSee('zyhcorner@gmail.com');
        $response->assertSee('Nomor Telepon');
        $response->assertSee('(+62) 812-3456-789');
        $response->assertSee('Just Say Hello!');
        $response->assertSee('Send Message');
        
        // Assert the Google Maps iframe is present
        $response->assertSee('https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7192150925703!2d101.85587509999999!3d0.40384089999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5c5c32fbe821f%3A0xcc0417290c322aec!2sZyh%20Corner!5e0!3m2!1sen!2sid!4v1782150424180!5m2!1sen!2sid', false);
    }
}
