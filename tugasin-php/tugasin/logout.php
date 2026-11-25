<?php
require __DIR__ . '/koneksi.php';

if (adalahPost()) {
    cekCsrf();
    $_SESSION = [];
    session_destroy();
}
pindah('login.php');
