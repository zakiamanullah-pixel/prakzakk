<?php
require __DIR__ . '/koneksi.php';

if (userSaatIni()) {
    pindah('index.php');
}

$pesan = '';
$nama  = '';
$email = '';

if (adalahPost()) {
    cekCsrf();
    $nama       = post('nama');
    $email      = strtolower(post('email'));
    $sandi      = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $konfirmasi = is_string($_POST['konfirmasi'] ?? null) ? $_POST['konfirmasi'] : '';

    if ($nama === '' || $email === '' || $sandi === '' || $konfirmasi === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (mb_strlen($nama) < 2 || mb_strlen($nama) > 40) {
        $pesan = 'Nama harus 2 sampai 40 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $pesan = 'Masukkan alamat email yang valid.';
    } elseif (strlen($sandi) < 8) {
        $pesan = 'Kata sandi minimal 8 karakter.';
    } elseif ($sandi !== $konfirmasi) {
        $pesan = 'Konfirmasi kata sandi belum sama.';
    } else {
        $pdo = db();
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);

        if ($cek->fetch()) {
            $pesan = 'Email sudah terdaftar. Silakan masuk.';
        } else {
            try {
                // Kata sandi TIDAK disimpan apa adanya, tapi di-hash.
                $pdo->prepare('INSERT INTO users (nama, email, password_hash) VALUES (?, ?, ?)')
                    ->execute([$nama, $email, password_hash($sandi, PASSWORD_DEFAULT)]);
                mulaiSesi($pdo->lastInsertId());
                pindah('index.php');
            } catch (PDOException $e) {
                $pesan = 'Pendaftaran gagal. Coba ulangi.';
            }
        }
    }
}

$artBadge = 'MULAI DARI SEKARANG';
$artJudul = 'Semua tugas,<br>satu tempat.';
$artTeks  = 'Catat tugas, tentukan prioritas, dan pantau progres dalam tampilan yang sederhana.';
$artP1    = 'Lebih siap menghadapi deadline';
$artP2    = 'Buat akun dan mulai mencatat.';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun | Tugasin</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/app.js" defer></script>
</head>
<body class="auth-page">
  <main class="auth-layout">
    <?php require __DIR__ . '/partials/auth_kiri.php'; ?>
    <div class="auth-area"><div class="auth-card"><div class="auth-mobile-brand"><a class="brand" href="index.php" aria-label="Tugasin, ke beranda"><span class="brand-mark">t</span><span>tugasin<span class="brand-point">.</span></span></a></div><span class="eyebrow">AKUN BARU</span><h2>Buat akun Tugasin</h2><p class="auth-subtitle">Hanya perlu beberapa informasi untuk mulai.</p>
      <form method="post" action="daftar.php" novalidate>
        <?= csrfField() ?>
        <div class="mb-3"><label class="form-label" for="nama">Nama panggilan</label><input class="form-control" id="nama" name="nama" type="text" maxlength="40" placeholder="Misalnya: Matius" value="<?= e($nama) ?>" autocomplete="name" required></div>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" placeholder="nama@email.com" value="<?= e($email) ?>" autocomplete="email" required></div>
        <div class="mb-3"><label class="form-label" for="password">Kata sandi</label><div class="password-wrap"><input class="form-control" id="password" name="password" type="password" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password" required><button class="toggle-password" type="button" data-toggle-password="password" aria-label="Tampilkan kata sandi">Lihat</button></div></div>
        <div class="mb-3"><label class="form-label" for="konfirmasi-password">Ulangi kata sandi</label><div class="password-wrap"><input class="form-control" id="konfirmasi-password" name="konfirmasi" type="password" placeholder="Masukkan ulang kata sandi" autocomplete="new-password" required><button class="toggle-password" type="button" data-toggle-password="konfirmasi-password" aria-label="Tampilkan kata sandi">Lihat</button></div></div>
        <?php if ($pesan !== ''): ?><div class="form-message" role="alert"><?= e($pesan) ?></div><?php endif; ?>
        <button type="submit" class="btn btn-primary auth-submit">Buat akun</button>
      </form>
      <p class="auth-switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
      <p class="auth-note">Untuk latihan, jangan pakai kata sandi akun pentingmu.</p>
    </div></div>
  </main>
</body>
</html>
