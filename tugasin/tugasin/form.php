<?php
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$pesan = "";

// nilai awal form (untuk tugas baru)
$id = 0;
$judulTugas = "";
$matkul = "";
$deadline = date("Y-m-d");
$prioritas = "Sedang";
$status = "aktif";
$catatan = "";

// dari kalender, tanggalnya sudah terisi
if (isset($_GET['tanggal'])) {
  $deadline = $_GET['tanggal'];
}

// kalau ada ?id=... berarti mode edit, ambil data lamanya
if (isset($_GET['id'])) {
  $idEdit = (int) $_GET['id'];
  $hasil = mysqli_query($koneksi, "SELECT * FROM tugas WHERE id = $idEdit AND user_id = $user_id");
  $data = mysqli_fetch_assoc($hasil);

  if ($data) {
    $id = $data['id'];
    $judulTugas = $data['judul'];
    $matkul = $data['matkul'];
    $deadline = $data['deadline'];
    $prioritas = $data['prioritas'];
    $status = $data['status'];
    $catatan = $data['catatan'];
  }
}

// proses simpan
if (isset($_POST['simpan'])) {
  $id = (int) $_POST['id'];
  $judulTugas = trim($_POST['judul']);
  $matkul = trim($_POST['matkul']);
  $deadline = $_POST['deadline'];
  $prioritas = $_POST['prioritas'];
  $status = $_POST['status'];
  $catatan = trim($_POST['catatan']);

  if ($judulTugas == "" || $matkul == "" || $deadline == "") {
    $pesan = "Judul, mata kuliah, dan tenggat waktu wajib diisi.";
  } else {
    $judulDb = mysqli_real_escape_string($koneksi, $judulTugas);
    $matkulDb = mysqli_real_escape_string($koneksi, $matkul);
    $deadlineDb = mysqli_real_escape_string($koneksi, $deadline);
    $prioritasDb = mysqli_real_escape_string($koneksi, $prioritas);
    $statusDb = mysqli_real_escape_string($koneksi, $status);
    $catatanDb = mysqli_real_escape_string($koneksi, $catatan);

    if ($id > 0) {
      mysqli_query($koneksi, "UPDATE tugas SET judul='$judulDb', matkul='$matkulDb', deadline='$deadlineDb', prioritas='$prioritasDb', status='$statusDb', catatan='$catatanDb' WHERE id = $id AND user_id = $user_id");
    } else {
      mysqli_query($koneksi, "INSERT INTO tugas (user_id, judul, matkul, deadline, prioritas, status, catatan) VALUES ($user_id, '$judulDb', '$matkulDb', '$deadlineDb', '$prioritasDb', '$statusDb', '$catatanDb')");
    }

    header("Location: tugas.php");
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - <?php if ($id > 0) { echo "Edit tugas"; } else { echo "Tambah tugas"; } ?></title>
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
      <a class="nav-link-custom aktif" href="form.php">+ Tambah tugas</a>
      <a class="nav-link-custom" href="pengaturan.php">Pengaturan</a>
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
        <h1><?php if ($id > 0) { echo "Edit tugas"; } else { echo "Tambah tugas"; } ?></h1>
        <p>Isi informasi tugas dengan lengkap.</p>
      </div>
    </header>


    <div class="kartu p-4" style="max-width:750px">

      <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo $pesan; ?></div>
      <?php } ?>

      <form method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="mb-3">
          <label class="form-label fw-semibold">Judul tugas</label>
          <input class="form-control" name="judul" value="<?php echo htmlspecialchars($judulTugas); ?>" placeholder="Contoh: Laporan Praktikum Pemrograman" required>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Mata kuliah</label>
            <input class="form-control" name="matkul" value="<?php echo htmlspecialchars($matkul); ?>" placeholder="Contoh: Dasar Pemrograman" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Tenggat waktu</label>
            <input class="form-control" type="date" name="deadline" value="<?php echo htmlspecialchars($deadline); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Prioritas</label>
            <select class="form-select" name="prioritas">
              <option value="Sedang" <?php if ($prioritas == "Sedang") { echo "selected"; } ?>>Sedang</option>
              <option value="Tinggi" <?php if ($prioritas == "Tinggi") { echo "selected"; } ?>>Tinggi</option>
              <option value="Rendah" <?php if ($prioritas == "Rendah") { echo "selected"; } ?>>Rendah</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select class="form-select" name="status">
              <option value="aktif" <?php if ($status == "aktif") { echo "selected"; } ?>>Belum selesai</option>
              <option value="selesai" <?php if ($status == "selesai") { echo "selected"; } ?>>Selesai</option>
            </select>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Catatan</label>
          <textarea class="form-control" name="catatan" rows="4" placeholder="Catatan tentang tugas ini..."><?php echo htmlspecialchars($catatan); ?></textarea>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button type="submit" name="simpan" class="btn btn-hijau px-4">Simpan tugas</button>
          <a class="btn btn-outline-secondary" href="tugas.php">Batal</a>
        </div>
      </form>

    </div>

  </main>
</div>

<script src="js/script.js"></script>
</body>
</html>

