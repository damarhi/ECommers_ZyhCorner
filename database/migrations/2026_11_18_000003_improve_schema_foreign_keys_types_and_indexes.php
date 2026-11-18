<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasForeignKey(string $table, string $name): bool
    {
        foreach (Schema::getForeignKeys($table) as $fk) {
            if (($fk['name'] ?? null) === $name) {
                return true;
            }
        }
        return false;
    }

    public function up(): void
    {
        // 1. Users: change no_hp from BIGINT to VARCHAR(30) NULL, add remember_token & role index
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 30)->nullable()->change();
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (!Schema::hasIndex('users', 'users_role_idx')) {
                $table->index('role', 'users_role_idx');
            }
        });

        // 2. Pesanans: widen total to DECIMAL(15,2), add user_id FK + operational indexes
        $hasPesananUserFk = $this->hasForeignKey('pesanans', 'pesanans_user_id_fk');
        Schema::table('pesanans', function (Blueprint $table) use ($hasPesananUserFk) {
            $table->decimal('total', 15, 2)->change();
            if (!Schema::hasColumn('pesanans', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!$hasPesananUserFk) {
                $table->foreign('user_id', 'pesanans_user_id_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasIndex('pesanans', 'pesanans_email_pembeli_idx')) {
                $table->index('email_pembeli', 'pesanans_email_pembeli_idx');
            }
            if (!Schema::hasIndex('pesanans', 'pesanans_jenis_penjualan_idx')) {
                $table->index('jenis_penjualan', 'pesanans_jenis_penjualan_idx');
            }
            if (!Schema::hasIndex('pesanans', 'pesanans_payment_status_idx')) {
                $table->index('payment_status', 'pesanans_payment_status_idx');
            }
            if (!Schema::hasIndex('pesanans', 'pesanans_order_status_idx')) {
                $table->index('order_status', 'pesanans_order_status_idx');
            }
            if (!Schema::hasIndex('pesanans', 'pesanans_created_at_idx')) {
                $table->index('created_at', 'pesanans_created_at_idx');
            }
        });

        // Backfill pesanans.user_id from matching users.email
        $usersByEmail = DB::table('users')
            ->whereNotNull('email')
            ->pluck('user_id', 'email');

        if ($usersByEmail->isNotEmpty()) {
            foreach ($usersByEmail as $email => $uid) {
                DB::table('pesanans')
                    ->whereNull('user_id')
                    ->where('email_pembeli', $email)
                    ->update(['user_id' => $uid]);
            }
        }

        // 3. Returs: add user_id FK + indexes
        $hasReturUserFk = $this->hasForeignKey('returs', 'returs_user_id_fk');
        Schema::table('returs', function (Blueprint $table) use ($hasReturUserFk) {
            if (!Schema::hasColumn('returs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!$hasReturUserFk) {
                $table->foreign('user_id', 'returs_user_id_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasIndex('returs', 'returs_order_id_idx')) {
                $table->index('order_id', 'returs_order_id_idx');
            }
            if (!Schema::hasIndex('returs', 'returs_status_idx')) {
                $table->index('status', 'returs_status_idx');
            }
            if (!Schema::hasIndex('returs', 'returs_return_date_idx')) {
                $table->index('return_date', 'returs_return_date_idx');
            }
        });

        // Backfill returs.user_id via associated pesanans.order_number -> user_id
        $ordersWithUser = DB::table('pesanans')
            ->whereNotNull('user_id')
            ->whereNotNull('order_number')
            ->pluck('user_id', 'order_number');

        if ($ordersWithUser->isNotEmpty()) {
            foreach ($ordersWithUser as $orderNum => $uid) {
                DB::table('returs')
                    ->whereNull('user_id')
                    ->where('order_id', $orderNum)
                    ->update(['user_id' => $uid]);
            }
        }

        // 4. Produks: sanitize orphaned id_kategori, add FK nullOnDelete + filter indexes
        DB::table('produks')
            ->whereNotNull('id_kategori')
            ->whereNotIn('id_kategori', DB::table('kategoris')->select('id_kategori'))
            ->update(['id_kategori' => null]);

        $hasProdukKategoriFk = $this->hasForeignKey('produks', 'produks_id_kategori_fk');
        Schema::table('produks', function (Blueprint $table) use ($hasProdukKategoriFk) {
            if (!$hasProdukKategoriFk) {
                $table->foreign('id_kategori', 'produks_id_kategori_fk')
                    ->references('id_kategori')
                    ->on('kategoris')
                    ->nullOnDelete();
            }
            if (!Schema::hasIndex('produks', 'produks_jenis_produk_idx')) {
                $table->index('jenis_produk', 'produks_jenis_produk_idx');
            }
            if (!Schema::hasIndex('produks', 'produks_status_produk_idx')) {
                $table->index('status_produk', 'produks_status_produk_idx');
            }
            if (!Schema::hasIndex('produks', 'produks_tanggal_expired_idx')) {
                $table->index('tanggal_expired', 'produks_tanggal_expired_idx');
            }
        });

        // 5. Additional operational indexes on supporting tables
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasIndex('customers', 'customers_status_idx')) {
                $table->index('status', 'customers_status_idx');
            }
            if (!Schema::hasIndex('customers', 'customers_contact_idx')) {
                $table->index('contact', 'customers_contact_idx');
            }
        });

        Schema::table('cassiers', function (Blueprint $table) {
            if (!Schema::hasIndex('cassiers', 'cassiers_email_idx')) {
                $table->index('email', 'cassiers_email_idx');
            }
            if (!Schema::hasIndex('cassiers', 'cassiers_status_idx')) {
                $table->index('status', 'cassiers_status_idx');
            }
        });

        Schema::table('diskons', function (Blueprint $table) {
            if (!Schema::hasIndex('diskons', 'diskons_active_window_idx')) {
                $table->index(['status', 'tanggal_mulai', 'tanggal_selesai'], 'diskons_active_window_idx');
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasIndex('banners', 'banners_active_order_idx')) {
                $table->index(['status', 'posisi_banner', 'urutan'], 'banners_active_order_idx');
            }
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            if (!Schema::hasIndex('contact_messages', 'contact_messages_status_idx')) {
                $table->index('status', 'contact_messages_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasIndex('contact_messages', 'contact_messages_status_idx')) {
                $table->dropIndex('contact_messages_status_idx');
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasIndex('banners', 'banners_active_order_idx')) {
                $table->dropIndex('banners_active_order_idx');
            }
        });

        Schema::table('diskons', function (Blueprint $table) {
            if (Schema::hasIndex('diskons', 'diskons_active_window_idx')) {
                $table->dropIndex('diskons_active_window_idx');
            }
        });

        Schema::table('cassiers', function (Blueprint $table) {
            if (Schema::hasIndex('cassiers', 'cassiers_email_idx')) {
                $table->dropIndex('cassiers_email_idx');
            }
            if (Schema::hasIndex('cassiers', 'cassiers_status_idx')) {
                $table->dropIndex('cassiers_status_idx');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasIndex('customers', 'customers_status_idx')) {
                $table->dropIndex('customers_status_idx');
            }
            if (Schema::hasIndex('customers', 'customers_contact_idx')) {
                $table->dropIndex('customers_contact_idx');
            }
        });

        $hasProdukKategoriFk = $this->hasForeignKey('produks', 'produks_id_kategori_fk');
        Schema::table('produks', function (Blueprint $table) use ($hasProdukKategoriFk) {
            if ($hasProdukKategoriFk) {
                $table->dropForeign('produks_id_kategori_fk');
            }
            if (Schema::hasIndex('produks', 'produks_jenis_produk_idx')) {
                $table->dropIndex('produks_jenis_produk_idx');
            }
            if (Schema::hasIndex('produks', 'produks_status_produk_idx')) {
                $table->dropIndex('produks_status_produk_idx');
            }
            if (Schema::hasIndex('produks', 'produks_tanggal_expired_idx')) {
                $table->dropIndex('produks_tanggal_expired_idx');
            }
        });

        $hasReturUserFk = $this->hasForeignKey('returs', 'returs_user_id_fk');
        Schema::table('returs', function (Blueprint $table) use ($hasReturUserFk) {
            if ($hasReturUserFk) {
                $table->dropForeign('returs_user_id_fk');
            }
            if (Schema::hasColumn('returs', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasIndex('returs', 'returs_order_id_idx')) {
                $table->dropIndex('returs_order_id_idx');
            }
            if (Schema::hasIndex('returs', 'returs_status_idx')) {
                $table->dropIndex('returs_status_idx');
            }
            if (Schema::hasIndex('returs', 'returs_return_date_idx')) {
                $table->dropIndex('returs_return_date_idx');
            }
        });

        $hasPesananUserFk = $this->hasForeignKey('pesanans', 'pesanans_user_id_fk');
        Schema::table('pesanans', function (Blueprint $table) use ($hasPesananUserFk) {
            if ($hasPesananUserFk) {
                $table->dropForeign('pesanans_user_id_fk');
            }
            if (Schema::hasColumn('pesanans', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasIndex('pesanans', 'pesanans_email_pembeli_idx')) {
                $table->dropIndex('pesanans_email_pembeli_idx');
            }
            if (Schema::hasIndex('pesanans', 'pesanans_jenis_penjualan_idx')) {
                $table->dropIndex('pesanans_jenis_penjualan_idx');
            }
            if (Schema::hasIndex('pesanans', 'pesanans_payment_status_idx')) {
                $table->dropIndex('pesanans_payment_status_idx');
            }
            if (Schema::hasIndex('pesanans', 'pesanans_order_status_idx')) {
                $table->dropIndex('pesanans_order_status_idx');
            }
            if (Schema::hasIndex('pesanans', 'pesanans_created_at_idx')) {
                $table->dropIndex('pesanans_created_at_idx');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_role_idx')) {
                $table->dropIndex('users_role_idx');
            }
        });
    }
};
