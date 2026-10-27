<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $fillable = [
        'key',
        'value',
        'label',
        'group',
    ];

    /**
     * Get a setting by key, with optional default fallback.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("store_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return ($setting && $setting->value !== null && $setting->value !== '') ? $setting->value : $default;
        });
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, $value, ?string $label = null, string $group = 'general')
    {
        Cache::forget("store_setting_{$key}");
        Cache::forget('store_settings_all');

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'label' => $label ?? $key,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings as key-value associative array.
     */
    public static function getAllAsArray(): array
    {
        return Cache::remember('store_settings_all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get clean WhatsApp international phone number (e.g. 628123456789).
     */
    public static function getWaNumber(?string $phone = null): string
    {
        $phone = $phone ?? static::get('store_phone', '08123456789');
        $cleaned = preg_replace('/[^0-9]/', '', (string)$phone);
        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }
        return $cleaned ?: '628123456789';
    }

    /**
     * Clear all store settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('store_settings_all');
        $keys = static::pluck('key');
        foreach ($keys as $k) {
            Cache::forget("store_setting_{$k}");
        }
    }
}
