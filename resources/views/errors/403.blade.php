<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Zyh Corner</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png">
    
    <link rel="stylesheet" href="{{ asset('css/css_403.css') }}?v={{ file_exists(public_path('css/css_403.css')) ? filemtime(public_path('css/css_403.css')) : time() }}">
</head>
<body>

    <main class="minimal-card">
        
        <!-- 1. Logo -->
        <div class="brand-logo-wrapper">
            <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo" class="brand-logo">
        </div>

        <!-- 2. Teks Error -->
        <span class="error-tag">403 &bull; Forbidden</span>
        <h1 class="error-title">Akses Ditolak</h1>

        <!-- 3. Penjelasan Error -->
        <p class="error-desc">
            {{ $exception->getMessage() ?: 'Halaman ini memiliki batasan akses khusus dan tidak dapat dibuka dengan akun Anda saat ini.' }}
        </p>

        <!-- 4. Button Kembali ke Home / Dashboard -->
        @php
            $targetUrl = route('home');
            $btnLabel = 'Kembali ke Beranda';
            $showSubLink = false;

            if (Auth::check()) {
                $role = Auth::user()->role;
                if ($role === 'admin') {
                    $targetUrl = url('/admin/dashboard');
                    $btnLabel = 'Kembali ke Dashboard Admin';
                    $showSubLink = true;
                } elseif ($role === 'kasir') {
                    $targetUrl = route('orders.index');
                    $btnLabel = 'Kembali ke Kelola Pesanan';
                    $showSubLink = true;
                } else {
                    $targetUrl = route('dashboard');
                    $btnLabel = 'Kembali ke Dashboard Saya';
                    $showSubLink = true;
                }
            }
        @endphp

        <div class="actions-group">
            <a href="{{ $targetUrl }}" class="btn-action">
                {{ $btnLabel }}
            </a>

            @if($showSubLink)
                <a href="{{ route('home') }}" class="sub-link">
                    Buka Beranda Toko
                </a>
            @endif
        </div>

    </main>

</body>
</html>
