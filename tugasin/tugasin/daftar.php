<?php
include "koneksi.php";

$pesan = "";

if (isset($_POST['daftar'])) {
  $nama = trim($_POST['nama']);
  $email = strtolower(trim($_POST['email']));
  $password = $_POST['password'];

  $namaDb = mysqli_real_escape_string($koneksi, $nama);
  $emailDb = mysqli_real_escape_string($koneksi, $email);

  if (strlen($nama) < 2 || strlen($password) < 6) {
    $pesan = "Nama atau kata sandi terlalu pendek.";
  } else {
    // cek apakah email sudah dipakai
    $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$emailDb'");

    if (mysqli_num_rows($cek) > 0) {
      $pesan = "Email ini sudah terdaftar.";
    } else {
      // password disimpan dalam bentuk hash, bukan teks asli
      $hash = password_hash($password, PASSWORD_DEFAULT);

      mysqli_query($koneksi, "INSERT INTO users (nama, email, password) VALUES ('$namaDb', '$emailDb', '$hash')");

      $_SESSION['user_id'] = mysqli_insert_id($koneksi);
      $_SESSION['nama'] = $nama;
      $_SESSION['email'] = $email;
      header("Location: index.php");
      exit;
    }
  }
}

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - Pengelola Tugas Mahasiswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="layar-auth">

  <aside class="panel-sapa">
    <a class="brand" href="login.php">Tugas<span>in.</span></a>

    <div>
      <h1>Semua tugas kuliah,<br>lebih teratur.</h1>
      <p>Catat deadline, atur prioritas, dan selesaikan tugas satu per satu tanpa perlu bingung.</p>

      <div class="sapa-mini">
        <strong>Tetap produktif!</strong>
        <p class="mb-0 mt-2">Satu tempat untuk semua tugas yang perlu kamu kerjakan.</p>
      </div>
    </div>

    <small>Tugasin - Website pengelola tugas mahasiswa</small>
  </aside>

  <main class="form-auth">


    <section class="kartu">
      <h2 class="fw-bold mb-2">Buat akun</h2>
      <p class="text-secondary mb-4">Mulai kelola tugas kuliahmu.</p>

      <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo $pesan; ?></div>
      <?php } ?>

      <form method="post">
        <label class="form-label fw-semibold">Nama lengkap</label>
        <input class="form-control mb-3" name="nama" placeholder="Nama kamu" minlength="2" required>

        <label class="form-label fw-semibold">Email</label>
        <input class="form-control mb-3" type="email" name="email" placeholder="nama@email.com" required>

        <label class="form-label fw-semibold">Kata sandi (minimal 6 karakter)</label>
        <input class="form-control mb-4" type="password" name="password" minlength="6" required>

        <button class="btn btn-hijau w-100 py-3" type="submit" name="daftar">Daftar akun</button>
      </form>

      <div class="text-center mt-3">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
      </div>
    </section>

  </main>
</div>

<script src="js/script.js"></script>
</body>
</html>

