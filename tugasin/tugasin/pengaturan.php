<?php
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$pesan = "";
$pesanOk = false;

// proses ubah nama
if (isset($_POST['simpan_nama'])) {
  $nama = trim($_POST['nama']);

  if (strlen($nama) < 2) {
    $pesan = "Nama minimal 2 karakter.";
  } else {
    $namaDb = mysqli_real_escape_string($koneksi, $nama);
    mysqli_query($koneksi, "UPDATE users SET nama = '$namaDb' WHERE id = $user_id");

    $_SESSION['nama'] = $nama;
    $pesan = "Nama berhasil diperbarui.";
    $pesanOk = true;
  }
}

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - Pengaturan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="app">

  <aside class="sidebar">
    <a class="brand px-2" href="index.php">Tugas<span>in.</span></a>

    <nav class="nav">
      <a class="nav-link-custom" href="index.php">Beranda</a>
      <a class="nav-link-custom" href="tugas.php">Daftar tugas</a>
      <a class="nav-link-custom" href="kalender.php">Kalender</a>
      <a class="nav-link-custom" href="form.php">+ Tambah tugas</a>
      <a class="nav-link-custom aktif" href="pengaturan.php">Pengaturan</a>
    </nav>

    <div class="profil">
      <a class="profil-info" href="pengaturan.php">
        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
        <small class="text-secondary"><?php echo htmlspecialchars($_SESSION['email']); ?></small>
      </a>
      <a class="btn btn-sm btn-outline-danger mt-2" href="logout.php">Keluar</a>
    </div>
  </aside>

  <main class="konten">

    <header class="topbar">
      <div>
        <h1>Pengaturan</h1>
        <p>Atur tema dan profilmu.</p>
      </div>

              <a class="btn btn-hijau px-4 py-2" href="form.php">+ Tambah tugas</a>
    </header>


    <div class="kartu kartu-set">
      <h2>Tema</h2>
      <p class="teks-kecil">Berlaku untuk seluruh halaman web.</p>

      <div class="btn-group">
        <button type="button" class="btn btn-outline-success" data-pilih-tema="light">Terang</button>
        <button type="button" class="btn btn-outline-success" data-pilih-tema="dark">Gelap</button>
      </div>
    </div>

    <div class="kartu kartu-set">
      <h2>Profil</h2>
      <p class="teks-kecil">Ubah nama atau keluar dari akun.</p>

      <?php if ($pesan != "") { ?>
        <div class="pesan <?php if ($pesanOk) { echo "ok"; } ?>"><?php echo $pesan; ?></div>
      <?php } ?>

      <form method="post" class="mb-3">
        <label class="form-label fw-semibold">Ubah nama</label>
        <div class="d-flex flex-wrap gap-2">
          <input class="form-control" style="flex:1 1 220px" name="nama" value="<?php echo htmlspecialchars($_SESSION['nama']); ?>" minlength="2" maxlength="60" required>
          <button type="submit" name="simpan_nama" class="btn btn-hijau px-4">Simpan nama</button>
        </div>
      </form>

      <div class="mb-3">
        <div class="label-angka mb-1">Email</div>
        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
      </div>

      <hr>

      <a class="btn btn-outline-danger" href="logout.php">Keluar</a>
    </div>

  </main>
</div>

<script src="js/script.js"></script>
</body>
</html>

