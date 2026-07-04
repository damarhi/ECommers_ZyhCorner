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
        Schema::table('cassiers', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('password')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cassiers', function (Blueprint $table) {
            $table->dropColumn(['email', 'password']);
        });
    }
};
