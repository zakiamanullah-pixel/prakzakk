<?php
// proses hapus tugas dan ubah status selesai/belum
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$id = (int) $_GET['id'];
$do = $_GET['do'];

if ($do == "hapus") {
  mysqli_query($koneksi, "DELETE FROM tugas WHERE id = $id AND user_id = $user_id");
}

if ($do == "toggle") {
  $hasil = mysqli_query($koneksi, "SELECT status FROM tugas WHERE id = $id AND user_id = $user_id");
  $t = mysqli_fetch_assoc($hasil);

  if ($t) {
    if ($t['status'] == "selesai") {
      $baru = "aktif";
    } else {
      $baru = "selesai";
    }
    mysqli_query($koneksi, "UPDATE tugas SET status = '$baru' WHERE id = $id AND user_id = $user_id");
  }
}

// kembali ke halaman sebelumnya
if (isset($_SERVER['HTTP_REFERER'])) {
  header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
  header("Location: tugas.php");
}
exit;
