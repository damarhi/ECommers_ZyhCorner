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
        Schema::table('pesanans', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('jenis_penjualan');
            $table->string('shipping_method')->nullable()->after('payment_method');
            $table->text('alamat_pengiriman')->nullable()->after('customer_name');
            $table->text('catatan_pesanan')->nullable()->after('alamat_pengiriman');
            $table->string('snap_token')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'shipping_method',
                'alamat_pengiriman',
                'catatan_pesanan',
                'snap_token'
            ]);
        });
    }
};
