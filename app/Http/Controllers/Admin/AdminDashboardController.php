<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use App\Models\Retur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        if (Auth::check() && Auth::user()->role === 'kasir') {
            return $this->kasirDashboard($now);
        }

        $startOfWeek = $now->copy()->startOfWeek();

        // 1. KPI Cards
        $penjualanMingguan = Pesanan::where('payment_status', 'Paid')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total');

        $pesananMingguan = Pesanan::where('created_at', '>=', $startOfWeek)->count();

        $totalPelanggan = User::where('role', 'pelanggan')->count();

        // 2. Data Grafik Penjualan 7 Hari Terakhir (1 agregasi GROUP BY menggantikan 7 query berulang)
        $sevenDaysStart = $now->copy()->subDays(6)->startOfDay();
        $dailyTotalsMap = Pesanan::where('payment_status', 'Paid')
            ->where('created_at', '>=', $sevenDaysStart)
            ->selectRaw('DATE(created_at) as sale_date, SUM(total) as daily_sum')
            ->groupByRaw('DATE(created_at)')
            ->pluck('daily_sum', 'sale_date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dateKey = $day->format('Y-m-d');
            $chartLabels[] = $day->translatedFormat('d M');
            $chartData[] = (float) ($dailyTotalsMap[$dateKey] ?? 0);
        }

        // 3. Sumber Transaksi (Online vs Offline) dalam 1 query GROUP BY
        $channelCounts = Pesanan::selectRaw('jenis_penjualan, COUNT(*) as cnt')
            ->groupBy('jenis_penjualan')
            ->pluck('cnt', 'jenis_penjualan');
        $onlineCount = (int) ($channelCounts['Online'] ?? 0);
        $offlineCount = (int) ($channelCounts['Offline'] ?? 0);

        // 4. Pesanan Terbaru (5 transaksi terakhir)
        $recentOrders = Pesanan::latest('created_at')->take(5)->get();

        return view('Admin.dashboard.dashboard', compact(
            'penjualanMingguan',
            'pesananMingguan',
            'totalPelanggan',
            'chartLabels',
            'chartData',
            'onlineCount',
            'offlineCount',
            'recentOrders'
        ));
    }

    private function kasirDashboard(Carbon $now)
    {
        Produk::checkAndEmptyExpiredProducts();

        $startOfDay = $now->copy()->startOfDay();

        $perluKonfirmasi = Pesanan::whereIn('order_status', [
            'menunggu konfirmasi',
            'Menunggu Konfirmasi',
            'unconfirmed',
            'Unconfirmed',
        ])->count();

        $pesananPerluDikemas = Pesanan::whereIn('order_status', ['dikemas', 'Dikemas'])->count();

        $transaksiPosHariIni = Pesanan::where('jenis_penjualan', 'Offline')
            ->where('created_at', '>=', $startOfDay)
            ->count();

        $setoranPosHariIni = Pesanan::where('jenis_penjualan', 'Offline')
            ->where('payment_status', 'Paid')
            ->where('created_at', '>=', $startOfDay)
            ->sum('total');

        $returBaruCount = Retur::where('status', 'New')->count();
        $expiredPerluTindakan = ProdukKadaluarsa::where('status_tindakan', 'Perlu Tindakan')->count();
        $unreadMessagesCount = ContactMessage::where('status', 'Belum Dibaca')->count();

        $lowStockProducts = Produk::with('kategori')
            ->where(function ($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->get()
            ->filter(fn (Produk $p) => $p->stok <= 3)
            ->sortBy('stok')
            ->take(6)
            ->values();

        $recentOrders = Pesanan::latest('created_at')->take(5)->get();

        return view('Admin.dashboard.kasir_dashboard', compact(
            'perluKonfirmasi',
            'pesananPerluDikemas',
            'transaksiPosHariIni',
            'setoranPosHariIni',
            'returBaruCount',
            'expiredPerluTindakan',
            'unreadMessagesCount',
            'lowStockProducts',
            'recentOrders'
        ));
    }
}
