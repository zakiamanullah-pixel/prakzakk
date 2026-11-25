<?php
// Pengaturan + fungsi bantu. File ini di-require oleh semua halaman.

date_default_timezone_set('Asia/Jakarta');

// ====== Pengaturan database (default XAMPP) ======
const DB_HOST = 'localhost';
const DB_NAME = 'tugasin';
const DB_USER = 'root';
const DB_PASS = '';      // XAMPP default: kosong

// ====== Sesi login ======
session_set_cookie_params([
    'httponly' => true,  // cookie tidak bisa dibaca JavaScript
    'samesite' => 'Lax',
]);
session_start();

// Koneksi database (dibuat sekali saja).
function db()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Koneksi database gagal. Pastikan MySQL di XAMPP sudah Start, database "tugasin" sudah di-import, dan isi koneksi.php sudah benar.');
        }
    }
    return $pdo;
}

// Amankan teks sebelum dicetak ke HTML (mencegah XSS). Pakai untuk SEMUA data dari user/database.
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function pindah($url)
{
    header('Location: ' . $url);
    exit;
}

function adalahPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Ambil isian form (POST) berupa teks, spasi di tepi dibuang.
function post($kunci)
{
    $nilai = $_POST[$kunci] ?? '';
    return is_string($nilai) ? trim($nilai) : '';
}

// ====== Token CSRF: memastikan form benar-benar dikirim dari halaman kita ======
function csrfToken()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfField()
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}
function cekCsrf()
{
    $kirim = $_POST['csrf'] ?? '';
    if (!is_string($kirim) || !hash_equals(csrfToken(), $kirim)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Kembali, muat ulang halaman, lalu coba lagi.');
    }
}

// ====== Login ======
function mulaiSesi($userId)
{
    session_regenerate_id(true);   // cegah session fixation
    $_SESSION['user_id'] = (int) $userId;
}

// Data user yang sedang login, atau null kalau belum login.
function userSaatIni()
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare('SELECT id, nama, email FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
    }
    return $user;
}

// Dipanggil di awal halaman yang khusus untuk user login.
function wajibLogin()
{
    $user = userSaatIni();
    if (!$user) {
        pindah('login.php');
    }
    return $user;
}

// ====== Tanggal ======
const BULAN_PENDEK  = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const BULAN_PANJANG = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const NAMA_HARI     = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

function hariIni()
{
    return NAMA_HARI[(int) date('w')] . ', ' . date('j') . ' ' . BULAN_PANJANG[(int) date('n')] . ' ' . date('Y');
}

// "2026-11-13" -> "13 Nov 2026"
function formatTanggal($ymd)
{
    $p = explode('-', (string) $ymd);
    if (count($p) !== 3) {
        return 'Tanggal tidak valid';
    }
    return (int) $p[2] . ' ' . BULAN_PENDEK[(int) $p[1]] . ' ' . $p[0];
}

// Selisih hari dari hari ini: negatif = sudah lewat.
function selisihHari($ymd)
{
    $selisih = (new DateTime('today'))->diff(new DateTime($ymd));
    return (int) $selisih->format('%r%a');
}

function tanggalValid($teks)
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $teks, $m)) {
        return false;
    }
    return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
}

// ====== Lencana (badge) tugas ======
function badgeStatus($tugas)
{
    if ($tugas['selesai']) {
        return '<span class="badge-status done">Selesai</span>';
    }
    $sisa = selisihHari($tugas['deadline']);
    if ($sisa < 0)   return '<span class="badge-status late">Terlambat</span>';
    if ($sisa === 0) return '<span class="badge-status today">Hari ini</span>';
    if ($sisa === 1) return '<span class="badge-status">Besok</span>';
    return '<span class="badge-status">' . $sisa . ' hari lagi</span>';
}
function badgePrioritas($tugas)
{
    $nilai = in_array($tugas['prioritas'], ['tinggi', 'sedang', 'rendah'], true) ? $tugas['prioritas'] : 'sedang';
    return '<span class="badge-priority ' . $nilai . '">' . ucfirst($nilai) . '</span>';
}
function tampilanKosong($judul, $detail, $tombol = false)
{
    return '<div class="kosong"><strong>' . e($judul) . '</strong><p>' . e($detail) . '</p>'
        . ($tombol ? '<a class="btn btn-primary" href="tambah.php">+ Tambah tugas</a>' : '') . '</div>';
}
