<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notifikasi Zyh Corner')</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333333; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { color: #ffffff; padding: 25px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .body-content { padding: 30px; }
        .order-badge, .alert-badge, .badge-status { display: inline-block; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-table td.label { font-weight: 600; color: #666666; width: 40%; }
        .info-table td.value { color: #1a1a1a; font-weight: 500; }
        .items-header { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 13px; }
        .items-table th { background: #f8faf9; color: #555555; text-align: left; padding: 10px; border-bottom: 2px solid #e0e0e0; }
        .items-table td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .btn-action { display: block; margin: 25px auto 0; padding: 12px 20px; color: #ffffff; text-align: center; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 14px; }
        .footer { background: #f8faf9; padding: 15px 30px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
        @yield('extra-styles')
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header" style="@yield('header-style', 'background: linear-gradient(135deg, #00b207, #008a05);')">
            <h1>@yield('header-title', 'Zyh Corner')</h1>
            <p>@yield('header-subtitle', 'Sistem Notifikasi Toko')</p>
        </div>
        <div class="body-content">
            @yield('content')
        </div>
        <div class="footer">
            @yield('footer', 'Email ini dikirim secara otomatis oleh Sistem Zyh Corner.')
        </div>
    </div>
</body>
</html>
