<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutUsTest extends TestCase
{
    public function test_about_us_page_loads_successfully(): void
    {
        $response = $this->get(route('about'));

        $response->assertStatus(200);
        $response->assertSee('About Us');
        $response->assertSee('Cita Rasa Klasik dengan Sentuhan Premium');
        $response->assertSee('Mengapa Memilih Zyh Corner?');
        $response->assertSee('Tim Hebat Kami');
        $response->assertSee('Jenny Wilson');
        $response->assertSee('Founder & Head Baker', false);
        $response->assertSee('Robert Fox');
        $response->assertSee('Testimoni Pelanggan');
    }
}
