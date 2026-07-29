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
        Schema::create('produk_kadaluarsas', function (Blueprint $table) {
            $table->id('id_expired');
            $table->foreignId('id_produk')->constrained('produks', 'id_produk')->onDelete('cascade');
            $table->date('tanggal_expired');
            $table->string('jumlah_stok')->nullable();
            $table->enum('status_tindakan', ['Perlu Tindakan', 'Diarsipkan', 'Dimusnahkan', 'Diretur'])->default('Perlu Tindakan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_kadaluarsas');
    }
};
