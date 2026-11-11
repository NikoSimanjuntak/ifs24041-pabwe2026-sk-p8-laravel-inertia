# BlogVerse Pro — {username}-pabwe2026-sk-p8-laravel-inertia

Paket ini adalah **overlay**: berisi semua kode aplikasi (Feature-First) yang ditimpa ke proyek Laravel 13 baru.

## 1. Inisialisasi (ganti `username` dengan username Anda)
```bash
composer create-project laravel/laravel username-pabwe2026-sk-p8-laravel-inertia
cd username-pabwe2026-sk-p8-laravel-inertia
```
Ekstrak isi zip ini ke folder proyek (timpa berkas yang sama), lalu:
```bash
rm -f resources/views/welcome.blade.php tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php
composer require inertiajs/inertia-laravel phpoffice/phpspreadsheet
npm install react react-dom @inertiajs/react @vitejs/plugin-react @toast-ui/editor lucide-react axios
```
(`laravel-vite-plugin`, `vite`, `tailwindcss`, `@tailwindcss/vite` sudah ada di starter Laravel; bila belum: `npm install -D tailwindcss @tailwindcss/vite`.)

## 2. Database PostgreSQL
```bash
psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"
```
Gabungkan isi `.env.snippet` ke `.env`, lalu:
```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```
Akun seed: `admin` / `admin12345` (Admin) dan `penulis` / `penulis12345` (Anggota).

## 3. Jalankan
```bash
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

## 4. Pengujian & mutu
```bash
php artisan test --coverage --min=100   # butuh ekstensi pcov atau xdebug
composer audit
```
Seluruh subdirektori `storage/` sudah memuat `.gitignore` (`*` + `!.gitignore`) agar `view:clear` lolos di CI.
