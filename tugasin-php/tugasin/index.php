<?php
require __DIR__ . '/koneksi.php';
$user = wajibLogin();

// Ambil semua tugas milik user ini, urut dari deadline terdekat.
$stmt = db()->prepare('SELECT id, judul, matkul, deadline, prioritas, catatan, selesai FROM tugas WHERE user_id = ? ORDER BY deadline ASC');
$stmt->execute([$user['id']]);
$semua = $stmt->fetchAll();

// Hitung ringkasan
$belum = [];
$jumlahSelesai = 0;
$jumlahTelat = 0;
foreach ($semua as $t) {
    if ($t['selesai']) {
        $jumlahSelesai++;
    } else {
        $belum[] = $t;
        if (selisihHari($t['deadline']) < 0) {
            $jumlahTelat++;
        }
    }
}
$total    = count($semua);
$persen   = $total ? (int) round($jumlahSelesai / $total * 100) : 0;
$terdekat = array_slice($belum, 0, 4);
$namaDepan = explode(' ', trim($user['nama']))[0];

if (!$total) {
    $teksProgres = 'Tambahkan tugas pertama untuk mulai mengatur kegiatan kuliahmu.';
} else {
    $teksProgres = $jumlahSelesai . ' dari ' . $total . ' tugas selesai. '
        . (count($belum) ? 'Teruskan progresmu!' : 'Semua tugas sudah beres!');
}

$judulHalaman = 'Beranda';
$menuAktif = 'beranda';
require __DIR__ . '/partials/atas.php';
?>
<section class="welcome-panel">
  <div>
    <span class="eyebrow">DASHBOARD</span>
    <h1>Halo, <span><?= e($namaDepan) ?></span>!</h1>
    <p>Mulai dari satu tugas hari ini. Semua bisa selesai selangkah demi selangkah.</p>
  </div>
  <a href="tambah.php" class="btn btn-primary btn-add"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Tambah tugas</a>
</section>
<section aria-label="Ringkasan tugas" class="stat-grid">
  <div class="stat-card"><div class="stat-label">Belum selesai <span class="stat-icon"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01"/></svg></span></div><strong><?= count($belum) ?></strong><span>tugas aktif</span></div>
  <div class="stat-card"><div class="stat-label">Sudah selesai <span class="stat-icon"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span></div><strong><?= $jumlahSelesai ?></strong><span>tugas diselesaikan</span></div>
  <div class="stat-card"><div class="stat-label">Lewat deadline <span class="stat-icon icon-warning"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div><strong><?= $jumlahTelat ?></strong><span>perlu diprioritaskan</span></div>
</section>
<div class="row g-3 mt-1">
  <div class="col-12 col-xl-7">
    <section class="panel h-100" aria-labelledby="heading-deadline">
      <div class="panel-heading"><div><h2 id="heading-deadline" class="section-title">Deadline terdekat</h2><p class="section-sub">Fokus pada tugas yang paling mendesak.</p></div><a href="tugas.php" class="text-link">Lihat semua <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></div>
      <div>
<?php if ($terdekat): foreach ($terdekat as $t): ?>
        <div class="baris-tugas">
          <div class="baris-info"><h3><?= e($t['judul']) ?></h3><p><?= e($t['matkul']) ?> <?= badgePrioritas($t) ?></p></div>
          <div class="deadline-info"><?= badgeStatus($t) ?><span class="tanggal"><?= e(formatTanggal($t['deadline'])) ?></span></div>
        </div>
<?php endforeach; else: ?>
        <?= tampilanKosong('Belum ada tugas aktif', 'Tambahkan tugas baru atau nikmati waktu luangmu.', true) ?>
<?php endif; ?>
      </div>
    </section>
  </div>
  <div class="col-12 col-xl-5">
    <section class="panel h-100" aria-labelledby="heading-progress">
      <div class="panel-heading"><div><h2 id="heading-progress" class="section-title">Progres tugas</h2><p class="section-sub">Ringkasan pekerjaanmu saat ini.</p></div></div>
      <div class="progress-numeric"><strong><?= $persen ?>%</strong><span>selesai</span></div>
      <div class="progress" role="progressbar" aria-label="Persentase tugas selesai" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $persen ?>"><div class="progress-bar" style="width: <?= $persen ?>%"></div></div>
      <p class="progress-help"><?= e($teksProgres) ?></p>
      <a href="tugas.php" class="secondary-action">Atur daftar tugas <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
    </section>
  </div>
</div>
<?php require __DIR__ . '/partials/bawah.php'; ?>
