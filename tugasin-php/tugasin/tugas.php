<?php
require __DIR__ . '/koneksi.php';
$user = wajibLogin();
$uid  = (int) $user['id'];

$statusBoleh = ['semua', 'aktif', 'selesai', 'terlambat'];

// ===== Aksi dari tombol: centang selesai & hapus (dikirim lewat POST) =====
if (adalahPost()) {
    cekCsrf();
    $aksi = post('aksi');
    $id   = (int) post('id');

    if ($aksi === 'selesai') {
        $selesai = post('selesai') === '1' ? 1 : 0;
        db()->prepare('UPDATE tugas SET selesai = ? WHERE id = ? AND user_id = ?')->execute([$selesai, $id, $uid]);
    } elseif ($aksi === 'hapus') {
        db()->prepare('DELETE FROM tugas WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
    }

    // Kembali ke halaman ini dengan pencarian/filter yang sama
    $kembali = [];
    if (post('cari') !== '') {
        $kembali['cari'] = post('cari');
    }
    if (in_array(post('status'), $statusBoleh, true) && post('status') !== 'semua') {
        $kembali['status'] = post('status');
    }
    pindah('tugas.php' . ($kembali ? '?' . http_build_query($kembali) : ''));
}

// ===== Pencarian & filter (dikirim lewat GET) =====
$cari   = isset($_GET['cari']) && is_string($_GET['cari']) ? trim($_GET['cari']) : '';
$status = isset($_GET['status']) && is_string($_GET['status']) && in_array($_GET['status'], $statusBoleh, true) ? $_GET['status'] : 'semua';

$sql    = 'SELECT id, judul, matkul, deadline, prioritas, catatan, selesai FROM tugas WHERE user_id = ?';
$params = [$uid];

if ($cari !== '') {
    $sql .= ' AND (judul LIKE ? OR matkul LIKE ?)';
    $like = '%' . addcslashes($cari, '%_\\') . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($status === 'aktif') {
    $sql .= ' AND selesai = 0';
} elseif ($status === 'selesai') {
    $sql .= ' AND selesai = 1';
} elseif ($status === 'terlambat') {
    $sql .= ' AND selesai = 0 AND deadline < ?';
    $params[] = date('Y-m-d');
}
$sql .= ' ORDER BY selesai ASC, deadline ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$hasil = $stmt->fetchAll();

$hitung = db()->prepare('SELECT COUNT(*) FROM tugas WHERE user_id = ?');
$hitung->execute([$uid]);
$jumlahSemua = (int) $hitung->fetchColumn();

// Kolom tersembunyi agar pencarian/filter tetap sama setelah tombol ditekan
function kolomAksi($aksi, $id, $cari, $status)
{
    return csrfField()
        . '<input type="hidden" name="aksi" value="' . e($aksi) . '">'
        . '<input type="hidden" name="id" value="' . (int) $id . '">'
        . '<input type="hidden" name="cari" value="' . e($cari) . '">'
        . '<input type="hidden" name="status" value="' . e($status) . '">';
}

$judulHalaman = 'Daftar Tugas';
$menuAktif = 'tugas';
require __DIR__ . '/partials/atas.php';
?>
<section class="page-heading">
  <div><span class="eyebrow">ORGANISASI TUGAS</span><h1>Daftar tugas</h1><p>Cari, selesaikan, atau perbarui tugasmu di sini.</p></div>
  <a href="tambah.php" class="btn btn-primary btn-add"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg> Tambah tugas</a>
</section>
<section class="panel" aria-labelledby="semua-tugas">
  <div class="panel-heading task-panel-head"><div><h2 class="section-title" id="semua-tugas">Semua tugas</h2><p class="section-sub"><?= count($hasil) ?> dari <?= $jumlahSemua ?> tugas</p></div>
    <form class="filters" method="get" action="tugas.php">
      <label class="visually-hidden" for="cari-tugas">Cari tugas</label>
      <input id="cari-tugas" name="cari" class="form-control search-field" type="search" placeholder="Cari tugas atau mata kuliah..." value="<?= e($cari) ?>" autocomplete="off">
      <label class="visually-hidden" for="filter-status">Filter tugas</label>
      <select class="form-select filter-field" id="filter-status" name="status" onchange="this.form.submit()">
        <option value="semua" <?= $status === 'semua' ? 'selected' : '' ?>>Semua status</option>
        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Belum selesai</option>
        <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Sudah selesai</option>
        <option value="terlambat" <?= $status === 'terlambat' ? 'selected' : '' ?>>Lewat deadline</option>
      </select>
      <button type="submit" class="btn btn-outline-secondary">Cari</button>
    </form>
  </div>
  <div>
<?php if (!$hasil): ?>
    <?= $jumlahSemua
        ? tampilanKosong('Tugas tidak ditemukan', 'Coba kata kunci atau filter lain.')
        : tampilanKosong('Belum ada tugas', 'Ayo tambahkan tugas pertamamu.', true) ?>
<?php else: foreach ($hasil as $t): ?>
    <div class="baris-tugas <?= $t['selesai'] ? 'tercentang' : '' ?>">
      <div class="baris-info">
        <h3><?= e($t['judul']) ?></h3>
        <p><?= e($t['matkul']) ?> · Deadline <?= e(formatTanggal($t['deadline'])) ?> <?= badgePrioritas($t) ?></p>
<?php if ($t['catatan'] !== ''): ?>
        <p class="note-text"><?= e($t['catatan']) ?></p>
<?php endif; ?>
      </div>
      <div class="task-actions">
        <?= badgeStatus($t) ?>
        <form method="post" action="tugas.php" class="form-inline">
          <?= kolomAksi('selesai', $t['id'], $cari, $status) ?>
          <input type="hidden" name="selesai" value="<?= $t['selesai'] ? '0' : '1' ?>">
          <label class="tugas-check"><input type="checkbox" onchange="this.form.submit()" <?= $t['selesai'] ? 'checked' : '' ?>> Selesai</label>
        </form>
        <a class="action-link" href="tambah.php?id=<?= (int) $t['id'] ?>">Edit</a>
        <form method="post" action="tugas.php" class="form-inline" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
          <?= kolomAksi('hapus', $t['id'], $cari, $status) ?>
          <button type="submit" class="action-link action-danger">Hapus</button>
        </form>
      </div>
    </div>
<?php endforeach; endif; ?>
  </div>
</section>
<?php require __DIR__ . '/partials/bawah.php'; ?>
