<?php
require __DIR__ . '/koneksi.php';

if (userSaatIni()) {
    pindah('index.php');
}

$pesan = '';
$email = '';

if (adalahPost()) {
    cekCsrf();
    $pdo = db();

    if (post('demo') === '1') {
        // ===== Tombol "Coba akun demo": buat akun demo kalau belum ada =====
        $emailDemo = 'demo@tugasin.test';
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$emailDemo]);
        $akun = $stmt->fetch();

        if (!$akun) {
            $pdo->prepare('INSERT INTO users (nama, email, password_hash) VALUES (?, ?, ?)')
                ->execute(['Pengguna Demo', $emailDemo, password_hash('demo1234', PASSWORD_DEFAULT)]);
            $idDemo = (int) $pdo->lastInsertId();

            // Tiga contoh tugas (deadline relatif terhadap hari ini)
            $contoh = [
                ['Latihan array bahasa C',    'Dasar Pemrograman',  '+1 day', 'tinggi', 'Kerjakan soal latihan sampai nomor 5.', 0],
                ['Ringkasan materi himpunan', 'Matematika Diskrit', '+3 day', 'sedang', '', 0],
                ['Refleksi Bahasa Indonesia', 'Bahasa Indonesia',   '+5 day', 'rendah', '', 1],
            ];
            $ins = $pdo->prepare(
                'INSERT INTO tugas (user_id, judul, matkul, deadline, prioritas, catatan, selesai)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($contoh as $t) {
                $ins->execute([$idDemo, $t[0], $t[1], date('Y-m-d', strtotime($t[2])), $t[3], $t[4], $t[5]]);
            }
        } else {
            $idDemo = (int) $akun['id'];
        }
        mulaiSesi($idDemo);
        pindah('index.php');
    }

    // ===== Login biasa =====
    $email = strtolower(post('email'));
    $sandi = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($email === '' || $sandi === '') {
        $pesan = 'Email dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email belum benar.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Pesan sengaja sama untuk "email salah" dan "sandi salah".
        if (!$user || !password_verify($sandi, $user['password_hash'])) {
            $pesan = 'Email atau kata sandi tidak sesuai.';
        } else {
            mulaiSesi($user['id']);
            pindah('index.php');
        }
    }
}

$artBadge = 'TUGAS LEBIH TERTATA';
$artJudul = 'Lebih tenang<br>saat tugas<br>terencana.';
$artTeks  = 'Tugasin membantu kamu mencatat deadline dan menyelesaikan tugas kuliah dengan lebih rapi.';
$artP1    = 'Langkah kecil setiap hari';
$artP2    = 'Mulai dari tugas yang paling penting.';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk | Tugasin</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/app.js" defer></script>
</head>
<body class="auth-page">
  <main class="auth-layout">
    <?php require __DIR__ . '/partials/auth_kiri.php'; ?>
    <div class="auth-area"><div class="auth-card"><div class="auth-mobile-brand"><a class="brand" href="index.php" aria-label="Tugasin, ke beranda"><span class="brand-mark">t</span><span>tugasin<span class="brand-point">.</span></span></a></div><span class="eyebrow">SELAMAT DATANG</span><h2>Masuk ke Tugasin</h2><p class="auth-subtitle">Lanjutkan mengatur tugas kuliahmu.</p>
      <form method="post" action="login.php" novalidate>
        <?= csrfField() ?>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" placeholder="nama@email.com" value="<?= e($email) ?>" required autocomplete="username"></div>
        <div class="mb-3"><label class="form-label" for="password">Kata sandi</label><div class="password-wrap"><input id="password" name="password" type="password" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password"><button class="toggle-password" type="button" data-toggle-password="password" aria-label="Tampilkan kata sandi">Lihat</button></div></div>
        <?php if ($pesan !== ''): ?><div class="form-message" role="alert"><?= e($pesan) ?></div><?php endif; ?>
        <button type="submit" class="btn btn-primary auth-submit">Masuk</button>
      </form>
      <p class="auth-switch">Belum punya akun? <a href="daftar.php">Daftar sekarang</a></p>
      <div class="auth-divider"><span>atau</span></div>
      <form method="post" action="login.php"><?= csrfField() ?><input type="hidden" name="demo" value="1"><button type="submit" class="btn btn-demo">Coba akun demo</button></form>
      <p class="auth-note">Akun demo bisa dipakai untuk mencoba. Data tersimpan di database server.</p>
    </div></div>
  </main>
</body>
</html>
