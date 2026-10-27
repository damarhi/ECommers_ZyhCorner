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
    
    <style>
        :root {
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --primary: #024220;
            --primary-hover: #012a14;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --badge-bg: #f1f5f9;
            --badge-color: #334155;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            background-image: radial-gradient(circle at 50% 30%, rgba(2, 66, 32, 0.03) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .minimal-card {
            background: var(--card-bg);
            max-width: 440px;
            width: 100%;
            border-radius: 20px;
            padding: 44px 32px;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: 0 16px 36px -12px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 1. Logo */
        .brand-logo-wrapper {
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 6px 16px rgba(2, 66, 32, 0.12);
            display: block;
        }

        /* 2. Teks Error */
        .error-tag {
            display: inline-block;
            background: var(--badge-bg);
            color: var(--badge-color);
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        /* 3. Penjelasan Error */
        .error-desc {
            font-size: 14.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 28px;
            max-width: 360px;
        }

        /* 4. Button Kembali */
        .actions-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 13px 24px;
            border-radius: 12px;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: 0.1px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(2, 66, 32, 0.2);
        }

        .btn-action:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2, 66, 32, 0.28);
        }

        .sub-link {
            font-size: 13.5px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .sub-link:hover {
            color: var(--text-main);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .minimal-card {
                padding: 36px 24px;
                border-radius: 16px;
            }

            .brand-logo {
                width: 64px;
                height: 64px;
            }

            .error-title {
                font-size: 20px;
            }

            .error-desc {
                font-size: 14px;
            }
        }
    </style>
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
