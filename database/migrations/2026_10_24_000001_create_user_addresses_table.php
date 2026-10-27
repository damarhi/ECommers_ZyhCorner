<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('label')->default('Alamat Rumah');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('destination_area_id')->nullable();
            $table->string('destination_area_name')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('street_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_default']);
        });

        // Migrate existing users.alamat if available
        $usersWithAddress = DB::table('users')->whereNotNull('alamat')->where('alamat', '!=', '')->get();
        foreach ($usersWithAddress as $user) {
            DB::table('user_addresses')->insert([
                'user_id' => $user->user_id,
                'label' => 'Alamat Utama',
                'recipient_name' => $user->nama ?? 'Penerima',
                'recipient_phone' => $user->no_hp ?? '',
                'destination_area_id' => null,
                'destination_area_name' => null,
                'postal_code' => null,
                'street_address' => $user->alamat,
                'latitude' => null,
                'longitude' => null,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
