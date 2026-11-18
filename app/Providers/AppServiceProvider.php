<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Pesanan;
use App\Models\Retur;
use App\Models\StoreSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));
            return Limit::perMinute(10)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('otp-resend', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Invalidate admin layout badge cache when Retur or ContactMessage changes
        $clearAdminBadges = static fn () => Cache::forget('admin_layout_badges');
        Retur::saved($clearAdminBadges);
        Retur::deleted($clearAdminBadges);
        ContactMessage::saved($clearAdminBadges);
        ContactMessage::deleted($clearAdminBadges);

        // Share store settings to all views (memoized per HTTP request via request attributes + Cache, zero Schema::hasTable queries)
        View::composer('*', function ($view) {
            try {
                $req = app()->bound('request') ? request() : null;
                if ($req && $req->attributes->has('_zyh_store_settings')) {
                    $view->with('storeSettings', $req->attributes->get('_zyh_store_settings'));
                    return;
                }

                $settings = StoreSetting::getAllAsArray();
                $settings['wa_number'] = StoreSetting::getWaNumber($settings['store_phone'] ?? null);
                $settings['social_instagram'] = StoreSetting::getInstagramUrl($settings['social_instagram'] ?? null);

                if ($req) {
                    $req->attributes->set('_zyh_store_settings', $settings);
                }

                $view->with('storeSettings', $settings);
            } catch (\Throwable $e) {
                // Fail-safe fallback if database is not migrated yet
            }
        });

        // Precompute & cache Admin/Kasir topbar and sidebar notification counters once per page
        // Store only plain arrays/scalars in Cache (Laravel 13 sets cache.serializable_classes = false)
        View::composer('Layout.layout_pAdmin', function ($view) {
            try {
                $computeBadges = static function (): array {
                    $unreadMessages = ContactMessage::where('status', 'Belum Dibaca')->count();
                    $recentMessages = ContactMessage::latest()
                        ->take(3)
                        ->get(['id', 'nama', 'email', 'created_at'])
                        ->map(fn (ContactMessage $msg) => [
                            'id' => $msg->id,
                            'nama' => (string) $msg->nama,
                            'email' => (string) $msg->email,
                            'created_at' => $msg->getRawOriginal('created_at') ?: ($msg->created_at ? $msg->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')),
                        ])
                        ->all();

                    $unconfirmedOrders = Pesanan::whereIn('order_status', [
                        'menunggu konfirmasi',
                        'Menunggu Konfirmasi',
                        'unconfirmed',
                        'Unconfirmed',
                    ])->count();
                    $newReturs = Retur::where('status', 'New')->count();

                    return [
                        'recentNavbarMessagesRaw' => $recentMessages,
                        'navbarUnreadMsgCount' => $unreadMessages,
                        'sidebarUnreadCount' => $unreadMessages,
                        'notifUnconfirmed' => $unconfirmedOrders,
                        'notifRetur' => $newReturs,
                        'totalNotif' => $unconfirmedOrders + $newReturs,
                    ];
                };

                $badges = Cache::remember('admin_layout_badges', 30, $computeBadges);

                if (!is_array($badges) || !isset($badges['recentNavbarMessagesRaw']) || !is_array($badges['recentNavbarMessagesRaw'])) {
                    Cache::forget('admin_layout_badges');
                    $badges = Cache::remember('admin_layout_badges', 30, $computeBadges);
                }

                $hydratedMessages = ContactMessage::hydrate($badges['recentNavbarMessagesRaw']);

                $view->with([
                    'recentNavbarMessages' => $hydratedMessages,
                    'navbarUnreadMsgCount' => (int) ($badges['navbarUnreadMsgCount'] ?? 0),
                    'sidebarUnreadCount' => (int) ($badges['sidebarUnreadCount'] ?? 0),
                    'notifUnconfirmed' => (int) ($badges['notifUnconfirmed'] ?? 0),
                    'notifRetur' => (int) ($badges['notifRetur'] ?? 0),
                    'totalNotif' => (int) ($badges['totalNotif'] ?? 0),
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'recentNavbarMessages' => collect(),
                    'navbarUnreadMsgCount' => 0,
                    'sidebarUnreadCount' => 0,
                    'notifUnconfirmed' => 0,
                    'notifRetur' => 0,
                    'totalNotif' => 0,
                ]);
            }
        });
    }
}
