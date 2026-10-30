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
            $table->string('bukti_pembayaran')->nullable()->after('snap_token');
            $table->timestamp('bukti_pembayaran_uploaded_at')->nullable()->after('bukti_pembayaran');
            $table->text('catatan_verifikasi_pembayaran')->nullable()->after('bukti_pembayaran_uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_pembayaran',
                'bukti_pembayaran_uploaded_at',
                'catatan_verifikasi_pembayaran',
            ]);
        });
    }
};
