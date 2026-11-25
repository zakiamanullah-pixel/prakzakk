<?php
/*
  Kerangka halaman setelah login: sidebar + topbar.
  Variabel yang harus ada sebelum require: $judulHalaman, $menuAktif ('beranda' | 'tugas' | 'tambah'), $user
*/
$menu = [
    'beranda' => ['index.php',  'Beranda',      '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>'],
    'tugas'   => ['tugas.php',  'Daftar Tugas', '<path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01"/>'],
    'tambah'  => ['tambah.php', 'Tambah Tugas', '<path d="M12 5v14M5 12h14"/>'],
];
$inisial = mb_strtoupper(mb_substr($user['nama'], 0, 1));
$svgAwal = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Tugasin, pengelola tugas kuliah sederhana dan teratur.">
  <title><?= e($judulHalaman) ?> | Tugasin</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="app-page">
<div class="app">
  <aside class="sidebar" aria-label="Menu aplikasi">
    <a class="brand" href="index.php" aria-label="Tugasin, ke beranda"><span class="brand-mark">t</span><span>tugasin<span class="brand-point">.</span></span></a>
    <p class="menu-title">MENU UTAMA</p>
    <nav class="menu-list" aria-label="Navigasi utama">
<?php foreach ($menu as $kunci => $m): $aktif = ($kunci === $menuAktif); ?>
      <a class="menu-item <?= $aktif ? 'aktif' : '' ?>" href="<?= $m[0] ?>"<?= $aktif ? ' aria-current="page"' : '' ?>><?= $svgAwal . $m[2] ?></svg><span><?= $m[1] ?></span></a>
<?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
      <div class="side-user"><span class="user-avatar"><?= e($inisial) ?></span><span class="side-user-info"><strong><?= e($user['nama']) ?></strong><small><?= e($user['email']) ?></small></span></div>
      <form method="post" action="logout.php" class="form-inline"><?= csrfField() ?>
        <button type="submit" class="logout-link"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar akun</button>
      </form>
    </div>
  </aside>
  <div class="workspace">
    <header class="topbar">
      <div class="topbar-left"><span class="mobile-brand"><a class="brand" href="index.php" aria-label="Tugasin, ke beranda"><span class="brand-mark">t</span><span>tugasin<span class="brand-point">.</span></span></a></span><span class="topbar-tagline">Kelola tugas, lebih terarah.</span></div>
      <div class="topbar-right"><span class="tanggal-header"><?= e(hariIni()) ?></span><span class="user-avatar header-avatar"><?= e($inisial) ?></span>
        <form method="post" action="logout.php" class="form-inline"><?= csrfField() ?><button class="mobile-logout" type="submit" aria-label="Keluar akun">Keluar</button></form>
      </div>
    </header>
    <nav class="menu-hp" aria-label="Navigasi di HP">
<?php foreach ($menu as $kunci => $m): $aktif = ($kunci === $menuAktif); ?>
      <a href="<?= $m[0] ?>" class="<?= $aktif ? 'aktif' : '' ?>"<?= $aktif ? ' aria-current="page"' : '' ?>><?= $m[1] ?></a>
<?php endforeach; ?>
    </nav>
    <main class="main-content">
