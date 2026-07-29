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
        Schema::table('about_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('about_settings', 'email_2')) {
                $table->string('email_2')->nullable()->after('email');
            }
            if (!Schema::hasColumn('about_settings', 'telepon_2')) {
                $table->string('telepon_2')->nullable()->after('telepon');
            }
            if (!Schema::hasColumn('about_settings', 'judul_form_kontak')) {
                $table->string('judul_form_kontak')->nullable()->after('link_maps');
            }
            if (!Schema::hasColumn('about_settings', 'deskripsi_form_kontak')) {
                $table->text('deskripsi_form_kontak')->nullable()->after('judul_form_kontak');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_settings', function (Blueprint $table) {
            $table->dropColumn(['email_2', 'telepon_2', 'judul_form_kontak', 'deskripsi_form_kontak']);
        });
    }
};
