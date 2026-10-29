<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
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
        if (Auth::check() && Auth::user()->role === 'kasir') {
            return redirect()->route('orders.index')->with('error', 'Anda tidak memiliki hak akses ke halaman dashboard.');
        }

        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();

        // 1. KPI Cards
        $penjualanMingguan = Pesanan::where('payment_status', 'Paid')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total');

        $pesananMingguan = Pesanan::where('created_at', '>=', $startOfWeek)->count();

        $totalPelanggan = User::where('role', 'pelanggan')->count();

        // 2. Data Grafik Penjualan 7 Hari Terakhir
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $chartLabels[] = $day->translatedFormat('d M');
            $dailyTotal = Pesanan::where('payment_status', 'Paid')
                ->whereDate('created_at', $day->format('Y-m-d'))
                ->sum('total');
            $chartData[] = (float) $dailyTotal;
        }

        // 3. Sumber Transaksi (Online vs Offline)
        $onlineCount = Pesanan::where('jenis_penjualan', 'Online')->count();
        $offlineCount = Pesanan::where('jenis_penjualan', 'Offline')->count();

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
}
