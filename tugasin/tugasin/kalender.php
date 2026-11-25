<?php
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$hariIni = date("Y-m-d");

// bulan yang ditampilkan, format 2026-11 (default bulan ini)
$bulan = date("Y-m");
if (isset($_GET['bulan'])) {
  $bulan = $_GET['bulan'];
}

$tahun = (int) substr($bulan, 0, 4);
$bln = (int) substr($bulan, 5, 2);

if ($tahun < 2000 || $tahun > 2100 || $bln < 1 || $bln > 12) {
  $tahun = (int) date("Y");
  $bln = (int) date("n");
}

$bulan = $tahun . "-" . sprintf("%02d", $bln);

// tanggal yang dipilih (default hari ini)
$pilih = $hariIni;
if (isset($_GET['tanggal'])) {
  $pilih = $_GET['tanggal'];
}

$namaBulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
$namaHari = array("Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min");

$jumlahHari = date("t", mktime(0, 0, 0, $bln, 1, $tahun));
$geser = date("N", mktime(0, 0, 0, $bln, 1, $tahun)) - 1;   // Senin = 0

$bulanLalu = date("Y-m", mktime(0, 0, 0, $bln - 1, 1, $tahun));
$bulanDepan = date("Y-m", mktime(0, 0, 0, $bln + 1, 1, $tahun));

// ambil semua tugas di bulan ini, dikelompokkan per tanggal deadline
$peta = array();
$semua = mysqli_query($koneksi, "SELECT * FROM tugas WHERE user_id = $user_id AND deadline LIKE '$bulan%'");

while ($t = mysqli_fetch_assoc($semua)) {
  $tgl = $t['deadline'];
  $peta[$tgl][] = $t;
}

// tugas pada tanggal yang dipilih
$pilihDb = mysqli_real_escape_string($koneksi, $pilih);
$tugasPilih = mysqli_query($koneksi, "SELECT * FROM tugas WHERE user_id = $user_id AND deadline = '$pilihDb' ORDER BY (status = 'selesai') ASC");

?>
<!DOCTYPE html>
<html lang="id" data-tema="light" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tugasin - Kalender</title>
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
      <a class="nav-link-custom aktif" href="kalender.php">Kalender</a>
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
        <h1>Kalender</h1>
        <p>Lihat tanggal tenggat semua tugasmu.</p>
      </div>

              <a class="btn btn-hijau px-4 py-2" href="form.php">+ Tambah tugas</a>
    </header>


    <div class="kartu p-3 p-md-4 mb-4">
      <div class="kal-bar">
        <h2><?php echo $namaBulan[$bln - 1] . " " . $tahun; ?></h2>

        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-success" href="kalender.php?bulan=<?php echo $bulanLalu; ?>">&lsaquo;</a>
          <a class="btn btn-sm btn-outline-success" href="kalender.php">Hari ini</a>
          <a class="btn btn-sm btn-outline-success" href="kalender.php?bulan=<?php echo $bulanDepan; ?>">&rsaquo;</a>
        </div>
      </div>

      <div class="kal-grid">
        <?php
        // nama hari
        for ($h = 0; $h < 7; $h++) {
          echo '<div class="kal-nama-hari">' . $namaHari[$h] . '</div>';
        }

        // kotak kosong sebelum tanggal 1
        for ($k = 0; $k < $geser; $k++) {
          echo '<div class="kal-kosong"></div>';
        }

        // kotak tanggal
        for ($d = 1; $d <= $jumlahHari; $d++) {
          $kunci = $bulan . "-" . sprintf("%02d", $d);

          $kelas = "kal-hari";
          if ($kunci == $hariIni) {
            $kelas = $kelas . " hariini";
          }
          if ($kunci == $pilih) {
            $kelas = $kelas . " dipilih";
          }

          echo '<a class="' . $kelas . '" href="kalender.php?bulan=' . $bulan . '&tanggal=' . $kunci . '">';
          echo '<span class="kal-angka">' . $d . '</span>';

          if (isset($peta[$kunci])) {
            $jumlah = count($peta[$kunci]);
            echo '<span class="kal-penanda">';

            // maksimal 3 titik, sisanya ditulis +angka
            for ($i = 0; $i < $jumlah && $i < 3; $i++) {
              $t = $peta[$kunci][$i];
              $warna = "aktif";
              if ($t['status'] == "selesai") {
                $warna = "selesai";
              } else if ($t['deadline'] < $hariIni) {
                $warna = "terlambat";
              }
              echo '<i class="titik titik-' . $warna . '"></i>';
            }

            if ($jumlah > 3) {
              echo '<span>+' . ($jumlah - 3) . '</span>';
            }
            echo '</span>';
          }

          echo '</a>';
        }
        ?>
      </div>

      <div class="kal-legenda">
        <span><i class="titik titik-aktif"></i> Belum selesai</span>
        <span><i class="titik titik-selesai"></i> Selesai</span>
        <span><i class="titik titik-terlambat"></i> Terlambat</span>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h2 class="judul-bagian mb-0">Tugas pada <?php echo date("d/m/Y", strtotime($pilih)); ?></h2>
      <a class="btn btn-sm btn-hijau px-3" href="form.php?tanggal=<?php echo htmlspecialchars($pilih); ?>">+ Tambah di tanggal ini</a>
    </div>

    <div class="list-tugas">
      <?php
      if (mysqli_num_rows($tugasPilih) == 0) {
        echo '<div class="kosong">Tidak ada tugas pada tanggal ini.</div>';
      }

      while ($t = mysqli_fetch_assoc($tugasPilih)) {
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

