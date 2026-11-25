# Tugasin — Pengelola Tugas Kuliah (PHP native + MySQL)

Versi PHP murni: tiap halaman adalah file `.php` berisi HTML + PHP. Tanpa framework, tanpa `fetch`/API terpisah.

## Cara menjalankan di XAMPP
1. Start **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin folder `tugasin` ke `C:\xampp\htdocs\` (hapus dulu folder `tugasin` lama kalau ada).
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/tugasin.sql` → **Import**. (Cukup sekali.)
4. Buka `http://localhost/tugasin/login.php`.

Akun demo: tombol **Coba akun demo** (`demo@tugasin.test` / `demo1234`), dibuat otomatis saat pertama kali diklik.

## Pengaturan database
Ada di bagian atas `koneksi.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Default XAMPP sudah cocok.

## Struktur
```
tugasin/
├── koneksi.php      koneksi DB, sesi, token CSRF, fungsi bantu
├── login.php        halaman + proses login (dan tombol akun demo)
├── daftar.php       halaman + proses pendaftaran
├── logout.php       keluar
├── index.php        beranda (ringkasan tugas)
├── tugas.php        daftar tugas, cari/filter, centang selesai, hapus
├── tambah.php       form tambah & edit tugas
├── partials/        potongan HTML yang dipakai berulang (atas.php, bawah.php, auth_kiri.php)
├── css/ assets/ js/app.js
└── database/tugasin.sql
```

## Pola yang dipakai di tiap halaman
1. `require 'koneksi.php'` lalu `wajibLogin()`
2. Kalau request POST: cek token CSRF, validasi, simpan ke MySQL, lalu `pindah()` (redirect)
3. Ambil data dari MySQL
4. Cetak HTML, semua data dibungkus `e()` agar aman dari XSS

## Keamanan dasar
- Kata sandi memakai `password_hash()` / `password_verify()`
- Semua query memakai prepared statement
- Setiap tugas dicek kepemilikannya (`user_id`)
- Token CSRF di semua form POST, cookie sesi HttpOnly + SameSite
