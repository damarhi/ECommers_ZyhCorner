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
            $table->string('order_number')->unique()->after('id');
            $table->string('customer_name')->after('order_number');
            $table->string('payment_status')->default('Pending')->after('customer_name');
            $table->string('order_status')->default('Ready')->after('payment_status');
            $table->decimal('total', 10, 2)->after('order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'customer_name', 'payment_status', 'order_status', 'total']);
        });
    }
};
