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
     * Get primary registered admin's phone number if available.
     */
    public static function getAdminPhone(): ?string
    {
        try {
            return \App\Models\User::where('role', 'admin')
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->orderBy('user_id', 'asc')
                ->value('no_hp');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get clean WhatsApp international phone number (e.g. 628123456789).
     */
    public static function getWaNumber(?string $phone = null): string
    {
        if ($phone === null || trim($phone) === '') {
            $phone = static::get('store_phone');
        }

        if ($phone === null || trim((string)$phone) === '') {
            $phone = static::getAdminPhone();
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string)$phone);
        if ($cleaned === '') {
            return '628123456789';
        }

        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '62')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '8')) {
            return '62' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Get valid Instagram profile URL.
     */
    public static function getInstagramUrl(?string $url = null): ?string
    {
        if ($url === null || trim($url) === '') {
            $url = static::get('social_instagram');
        }

        if ($url === null || trim((string)$url) === '') {
            return null;
        }

        $trimmed = trim((string)$url);
        $trimmed = rtrim($trimmed, '/');

        if (preg_match('/^http:\/\//i', $trimmed)) {
            $trimmed = 'https://' . substr($trimmed, 7);
        }

        if (preg_match('/^https:\/\//i', $trimmed)) {
            return $trimmed;
        }

        $username = ltrim($trimmed, '@');
        $username = trim($username, '/');

        return 'https://instagram.com/' . $username;
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
