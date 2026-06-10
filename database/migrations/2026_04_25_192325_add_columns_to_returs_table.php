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
        Schema::table('returs', function (Blueprint $table) {
            $table->string('customer_name')->after('id');
            $table->string('order_id')->after('customer_name');
            $table->string('reason')->after('order_id');
            $table->date('return_date')->after('reason');
            $table->string('product_name')->after('return_date');
            $table->string('status')->default('New')->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'order_id', 'reason', 'return_date', 'product_name', 'status']);
        });
    }
};
