<?php
require __DIR__ . '/koneksi.php';
$user = wajibLogin();
$uid  = (int) $user['id'];

// Ada ?id=... berarti mode EDIT, kalau tidak ada berarti TAMBAH baru.
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$lama = null;
if ($id > 0) {
    $stmt = db()->prepare('SELECT id, judul, matkul, deadline, prioritas, catatan FROM tugas WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);
    $lama = $stmt->fetch() ?: null;
}
$tidakAda = ($id > 0 && !$lama);

// Nilai awal isian form
$judul     = $lama['judul'] ?? '';
$matkul    = $lama['matkul'] ?? '';
$deadline  = $lama['deadline'] ?? date('Y-m-d', strtotime('+1 day'));
$prioritas = $lama['prioritas'] ?? 'sedang';
$catatan   = $lama['catatan'] ?? '';
$pesan     = $tidakAda ? 'Tugas yang ingin diedit tidak ditemukan.' : '';

if (adalahPost() && !$tidakAda) {
    cekCsrf();
    $judul     = post('judul');
    $matkul    = post('matkul');
    $deadline  = post('deadline');
    $prioritas = post('prioritas');
    $catatan   = post('catatan');

    if (!in_array($prioritas, ['tinggi', 'sedang', 'rendah'], true)) {
        $prioritas = 'sedang';
    }

    if ($judul === '' || $matkul === '' || $deadline === '') {
        $pesan = 'Nama tugas, mata kuliah, dan deadline wajib diisi.';
    } elseif (mb_strlen($judul) > 100 || mb_strlen($matkul) > 80 || mb_strlen($catatan) > 400) {
        $pesan = 'Ada isian yang terlalu panjang.';
    } elseif (!tanggalValid($deadline)) {
        $pesan = 'Tanggal deadline tidak valid.';
    } else {
        if ($lama) {
            db()->prepare('UPDATE tugas SET judul = ?, matkul = ?, deadline = ?, prioritas = ?, catatan = ? WHERE id = ? AND user_id = ?')
                ->execute([$judul, $matkul, $deadline, $prioritas, $catatan, $id, $uid]);
        } else {
            db()->prepare('INSERT INTO tugas (user_id, judul, matkul, deadline, prioritas, catatan) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$uid, $judul, $matkul, $deadline, $prioritas, $catatan]);
        }
        pindah('tugas.php');
    }
}

$mode = $lama ? 'Edit tugas' : 'Tambah tugas';
$judulHalaman = $mode;
$menuAktif = 'tambah';
require __DIR__ . '/partials/atas.php';
?>
<section class="page-heading">
  <div><span class="eyebrow">MANAJEMEN TUGAS</span><h1><?= $mode ?></h1><p><?= $lama ? 'Perbarui informasi tugasmu di bawah ini.' : 'Tuliskan detail tugas agar tidak ada yang terlewat.' ?></p></div>
</section>
<section class="panel panel-form" aria-label="Formulir tugas">
<?php if ($tidakAda): ?>
  <div class="form-message" role="alert"><?= e($pesan) ?></div>
  <div class="form-footer"><a href="tugas.php" class="btn btn-outline-secondary">Kembali ke daftar</a></div>
<?php else: ?>
  <form id="form-tugas" method="post" action="tambah.php<?= $lama ? '?id=' . (int) $id : '' ?>">
    <?= csrfField() ?>
    <div class="mb-4"><label for="judul" class="form-label">Nama tugas <span class="required-star">*</span></label><input type="text" id="judul" name="judul" class="form-control" maxlength="100" placeholder="Misalnya: Latihan array bahasa C" value="<?= e($judul) ?>" required autofocus></div>
    <div class="mb-4"><label for="matkul" class="form-label">Mata kuliah <span class="required-star">*</span></label><input type="text" id="matkul" name="matkul" class="form-control" maxlength="80" placeholder="Misalnya: Dasar Pemrograman" value="<?= e($matkul) ?>" required></div>
    <div class="row g-3 mb-4"><div class="col-12 col-sm-6"><label for="deadline" class="form-label">Deadline <span class="required-star">*</span></label><input type="date" class="form-control" id="deadline" name="deadline" value="<?= e($deadline) ?>" required></div><div class="col-12 col-sm-6"><label for="prioritas" class="form-label">Prioritas</label>
      <select id="prioritas" name="prioritas" class="form-select">
        <option value="sedang" <?= $prioritas === 'sedang' ? 'selected' : '' ?>>Sedang</option>
        <option value="tinggi" <?= $prioritas === 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
        <option value="rendah" <?= $prioritas === 'rendah' ? 'selected' : '' ?>>Rendah</option>
      </select></div></div>
    <div class="mb-4"><label for="catatan" class="form-label">Catatan <span class="optional">(opsional)</span></label><textarea class="form-control" id="catatan" name="catatan" rows="4" maxlength="400" placeholder="Detail pengerjaan, referensi, atau informasi tambahan..."><?= e($catatan) ?></textarea><p class="form-helper">Maksimal 400 karakter.</p></div>
    <?php if ($pesan !== ''): ?><div id="pesan-form" class="form-message" role="alert"><?= e($pesan) ?></div><?php endif; ?>
    <div class="form-footer"><button type="submit" class="btn btn-primary"><?= $lama ? 'Simpan perubahan' : 'Simpan tugas' ?></button><a href="tugas.php" class="btn btn-outline-secondary">Batal</a></div>
  </form>
<?php endif; ?>
</section>
<?php require __DIR__ . '/partials/bawah.php'; ?>
