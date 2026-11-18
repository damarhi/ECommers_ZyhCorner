<?php

use App\Models\Produk;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:sync-expired', function () {
    Cache::forget('produk_expired_sync_lock');
    Produk::checkAndEmptyExpiredProducts();
    $this->info('Sinkronisasi produk kadaluarsa selesai.');
})->purpose('Periksa dan pindahkan stok produk showcase yang telah kadaluarsa');

Schedule::command('products:sync-expired')
    ->hourly()
    ->withoutOverlapping();
