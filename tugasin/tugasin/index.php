<?php
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$hariIni = date("Y-m-d");

// hitung statistik dari semua tugas milik user
$total = 0;
$selesai = 0;
$terlambat = 0;

$semua = mysqli_query($koneksi, "SELECT * FROM tugas WHERE user_id = $user_id");

while ($t = mysqli_fetch_assoc($semua)) {
  $total = $total + 1;

  if ($t['status'] == "selesai") {
    $selesai = $selesai + 1;
  } else if ($t['deadline'] < $hariIni) {
    $terlambat = $terlambat + 1;
  }
}

$persen = 0;
if ($total > 0) {
  $persen = round($selesai / $total * 100);
}

// 4 tugas terdekat yang belum selesai
$terdekat = mysqli_query($koneksi, "SELECT * FROM tugas WHERE user_id = $user_id AND status != 'selesai' ORDER BY deadline ASC LIMIT 4");

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - Beranda</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="app">

  <aside class="sidebar">
    <a class="brand px-2" href="index.php">Tugas<span>in.</span></a>

    <nav class="nav">
      <a class="nav-link-custom aktif" href="index.php">Beranda</a>
      <a class="nav-link-custom" href="tugas.php">Daftar tugas</a>
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
        <h1>Beranda</h1>
        <p>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>! Yuk, cek progres tugasmu.</p>
      </div>

              <a class="btn btn-hijau px-4 py-2" href="form.php">+ Tambah tugas</a>
    </header>


    <div class="row g-3 mb-4">
      <div class="col-6 col-xl-3">
        <div class="kartu kartu-stat">
          <div class="label-angka">Total tugas</div>
          <div class="angka"><?php echo $total; ?></div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="kartu kartu-stat">
          <div class="label-angka">Belum selesai</div>
          <div class="angka"><?php echo $total - $selesai; ?></div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="kartu kartu-stat">
          <div class="label-angka">Selesai</div>
          <div class="angka"><?php echo $selesai; ?></div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="kartu kartu-stat">
          <div class="label-angka">Terlambat</div>
          <div class="angka"><?php echo $terlambat; ?></div>
        </div>
      </div>
    </div>

    <div class="kartu p-4 mb-4">
      <div class="d-flex justify-content-between mb-2">
        <strong>Progres penyelesaian</strong>
        <span><?php echo $persen; ?>%</span>
      </div>
      <div class="progress">
        <div class="progress-bar" style="width:<?php echo $persen; ?>%"></div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="judul-bagian mb-0">Tugas terdekat</h2>
      <a class="btn btn-sm btn-outline-success" href="tugas.php">Lihat semua</a>
    </div>

    <div class="list-tugas">
      <?php
      if (mysqli_num_rows($terdekat) == 0) {
        echo '<div class="kosong">Belum ada tugas yang ditampilkan.</div>';
      }

      while ($t = mysqli_fetch_assoc($terdekat)) {
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

