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
        Schema::table('about_settings', function (Blueprint $table) {
            $table->string('judul_fitur')->nullable();
            $table->text('deskripsi_fitur')->nullable();
            $table->string('judul_delivery')->nullable();
            $table->text('deskripsi_delivery')->nullable();
            $table->string('poin_delivery_1')->nullable();
            $table->string('poin_delivery_2')->nullable();
            $table->string('poin_delivery_3')->nullable();
            $table->string('judul_tim')->nullable();
            $table->text('deskripsi_tim')->nullable();
            $table->string('judul_testimoni')->nullable();
            $table->text('deskripsi_testimoni')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_settings', function (Blueprint $table) {
            $table->dropColumn([
                'judul_fitur',
                'deskripsi_fitur',
                'judul_delivery',
                'deskripsi_delivery',
                'poin_delivery_1',
                'poin_delivery_2',
                'poin_delivery_3',
                'judul_tim',
                'deskripsi_tim',
                'judul_testimoni',
                'deskripsi_testimoni',
            ]);
        });
    }
};
