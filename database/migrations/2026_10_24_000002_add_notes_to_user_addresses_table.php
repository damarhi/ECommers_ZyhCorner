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
        if (Schema::hasTable('user_addresses') && !Schema::hasColumn('user_addresses', 'notes')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('street_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_addresses') && Schema::hasColumn('user_addresses', 'notes')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
