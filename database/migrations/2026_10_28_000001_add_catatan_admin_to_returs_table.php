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
        if (!Schema::hasColumn('returs', 'catatan_admin')) {
            Schema::table('returs', function (Blueprint $table) {
                $table->text('catatan_admin')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('returs', 'catatan_admin')) {
            Schema::table('returs', function (Blueprint $table) {
                $table->dropColumn('catatan_admin');
            });
        }
    }
};
