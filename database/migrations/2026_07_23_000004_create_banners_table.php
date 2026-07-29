<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id('id_banner');
            $table->string('judul_banner');
            $table->string('subjudul')->nullable();
            $table->string('gambar_banner');
            $table->enum('posisi_banner', ['home_hero', 'shop_header', 'custom_header', 'popup_promo'])->default('home_hero');
            $table->string('link_url')->nullable();
            $table->integer('urutan')->default(0);
            $table->string('status')->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
