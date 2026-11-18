<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanans', 'midtrans_order_id')) {
                $table->string('midtrans_order_id', 100)->nullable()->after('order_number')->index();
            }
            if (!Schema::hasColumn('pesanans', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('pesanans', 'stock_restored_at')) {
                $table->timestamp('stock_restored_at')->nullable()->after('cancel_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $dropCols = [];
            foreach (['midtrans_order_id', 'shipping_cost', 'stock_restored_at'] as $col) {
                if (Schema::hasColumn('pesanans', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
