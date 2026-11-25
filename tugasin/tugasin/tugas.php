<?php
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$hariIni = date("Y-m-d");

// ambil kata kunci pencarian dan filter dari URL (form method GET)
$cari = "";
if (isset($_GET['cari'])) {
  $cari = trim($_GET['cari']);
}

$filter = "semua";
if (isset($_GET['filter'])) {
  $filter = $_GET['filter'];
}

$cariDb = mysqli_real_escape_string($koneksi, $cari);

$sql = "SELECT * FROM tugas WHERE user_id = $user_id AND (judul LIKE '%$cariDb%' OR matkul LIKE '%$cariDb%')";

if ($filter == "aktif") {
  $sql = $sql . " AND status = 'aktif'";
} else if ($filter == "selesai") {
  $sql = $sql . " AND status = 'selesai'";
} else if ($filter == "terlambat") {
  $sql = $sql . " AND status != 'selesai' AND deadline < '$hariIni'";
}

// yang belum selesai di atas, lalu urut deadline
$sql = $sql . " ORDER BY (status = 'selesai') ASC, deadline ASC";

$hasil = mysqli_query($koneksi, $sql);

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - Daftar tugas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="app">

  <aside class="sidebar">
    <a class="brand px-2" href="index.php">Tugas<span>in.</span></a>

    <nav class="nav">
      <a class="nav-link-custom" href="index.php">Beranda</a>
      <a class="nav-link-custom aktif" href="tugas.php">Daftar tugas</a>
      <a class="nav-link-custom" href="kalender.php">Kalender</a>
      <a class="nav-link-custom" href="form.php">+ Tambah tugas</a>
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
        <h1>Daftar tugas</h1>
        <p>Cari, kelola, dan selesaikan tugasmu.</p>
      </div>

              <a class="btn btn-hijau px-4 py-2" href="form.php">+ Tambah tugas</a>
    </header>


    <div class="kartu p-3 mb-3">
      <form method="get" class="row g-2">
        <div class="col-md-6">
          <input class="form-control" type="search" name="cari" value="<?php echo htmlspecialchars($cari); ?>" placeholder="Cari judul atau mata kuliah...">
        </div>

        <div class="col-md-4">
          <select class="form-select" name="filter">
            <option value="semua" <?php if ($filter == "semua") { echo "selected"; } ?>>Semua tugas</option>
            <option value="aktif" <?php if ($filter == "aktif") { echo "selected"; } ?>>Belum selesai</option>
            <option value="selesai" <?php if ($filter == "selesai") { echo "selected"; } ?>>Selesai</option>
            <option value="terlambat" <?php if ($filter == "terlambat") { echo "selected"; } ?>>Terlambat</option>
          </select>
        </div>

        <div class="col-md-2">
          <button class="btn btn-hijau w-100 py-2" type="submit">Cari</button>
        </div>
      </form>
    </div>

    <div class="list-tugas">
      <?php
      if (mysqli_num_rows($hasil) == 0) {
        echo '<div class="kosong">Belum ada tugas yang ditampilkan.</div>';
      }

      while ($t = mysqli_fetch_assoc($hasil)) {
        // satu baris tugas, dipakai di index.php, tugas.php, dan kalender.php
// variabel $t harus sudah berisi 1 baris data tugas dari database

$kelasBaris = "";
$label = $t['prioritas'];
$chip = "chip-" . strtolower($t['prioritas']);
$tombol = "Selesai";

if ($t['status'] == "selesai") {
  $kelasBaris = " selesai";
  $label = "Selesai";
  $chip = "chip-selesai";
  $tombol = "Buka lagi";
} else if ($t['deadline'] < date("Y-m-d")) {
  $label = "Terlambat";
  $chip = "chip-terlambat";
}
?>
<article class="baris-tugas<?php echo $kelasBaris; ?>">
  <div style="min-width:0">
    <h3 class="nama-tugas"><?php echo htmlspecialchars($t['judul']); ?></h3>
    <div class="detail-tugas">
      <?php echo htmlspecialchars($t['matkul']); ?> - Deadline: <?php echo date("d/m/Y", strtotime($t['deadline'])); ?>
    </div>
    <?php if ($t['catatan'] != "") { ?>
      <div class="detail-tugas mt-1"><?php echo htmlspecialchars($t['catatan']); ?></div>
    <?php } ?>
  </div>

  <div class="aksi-tugas">
    <span class="chip <?php echo $chip; ?>"><?php echo $label; ?></span>
    <a class="btn btn-outline-success" href="aksi.php?do=toggle&id=<?php echo $t['id']; ?>"><?php echo $tombol; ?></a>
    <a class="btn btn-outline-secondary" href="form.php?id=<?php echo $t['id']; ?>">Edit</a>
    <a class="btn btn-outline-danger" href="aksi.php?do=hapus&id=<?php echo $t['id']; ?>" onclick="return confirm('Hapus tugas ini?')">Hapus</a>
  </div>
</article>

<?php
      }
      ?>
    </div>

  </main>
</div>

<script src="js/script.js"></script>
</body>
</html>

