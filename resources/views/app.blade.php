<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BlogVerse Pro: platform publikasi artikel. Baca artikel terbaru, atau daftar dan terbitkan tulisan Anda sendiri.">
    <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @inertia
</body>
</html>