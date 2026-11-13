<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') — {{ config('app.name', 'BlogVerse Pro') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f8fafc; color: #1e293b; }
        main { max-width: 32rem; margin: 0 auto; padding: 6rem 1.5rem; text-align: center; }
        .code { font-size: 0.875rem; font-weight: 600; color: #3730a3; }
        h1 { font-size: 1.875rem; margin: 0.5rem 0 1rem; }
        p { color: #334155; line-height: 1.6; }
        a { display: inline-block; margin-top: 1.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; background: #4338ca; color: #ffffff; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <main>
        <p class="code">Kode @yield('code', 'Error')</p>
        <h1>@yield('title', 'Terjadi kesalahan')</h1>
        <p>@yield('message', 'Terjadi masalah saat memuat halaman ini. Silakan coba lagi.')</p>
        <a href="{{ url('/') }}">Kembali ke beranda</a>
    </main>
</body>
</html>