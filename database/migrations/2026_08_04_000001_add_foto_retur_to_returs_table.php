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
        if (!Schema::hasColumn('returs', 'foto_retur')) {
            Schema::table('returs', function (Blueprint $table) {
                $table->string('foto_retur')->nullable()->after('product_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('returs', 'foto_retur')) {
            Schema::table('returs', function (Blueprint $table) {
                $table->dropColumn('foto_retur');
            });
        }
    }
};
