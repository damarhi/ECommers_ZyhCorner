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
        Schema::create('about_settings', function (Blueprint $table) {
            $table->id('id_about');
            $table->string('judul_utama')->nullable();
            $table->string('subjudul_utama')->nullable();
            $table->string('gambar_tentang')->nullable();
            $table->text('cerita_singkat')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->string('jam_operasional')->nullable();
            $table->text('alamat_toko')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->text('link_maps')->nullable();
            $table->string('foto_galeri_1')->nullable();
            $table->string('foto_galeri_2')->nullable();
            $table->string('foto_galeri_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_settings');
    }
};
