<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('otp_verifications') && !Schema::hasColumn('otp_verifications', 'attempts')) {
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('otp_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('otp_verifications') && Schema::hasColumn('otp_verifications', 'attempts')) {
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->dropColumn('attempts');
            });
        }
    }
};
