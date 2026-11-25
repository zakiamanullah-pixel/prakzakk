// Satu-satunya JavaScript: tombol "Lihat / Sembunyi" pada kolom kata sandi.
document.querySelectorAll('[data-toggle-password]').forEach(function (tombol) {
  tombol.addEventListener('click', function () {
    var kolom = document.getElementById(tombol.dataset.togglePassword);
    var terlihat = kolom.type === 'text';
    kolom.type = terlihat ? 'password' : 'text';
    tombol.textContent = terlihat ? 'Lihat' : 'Sembunyi';
    tombol.setAttribute('aria-label', terlihat ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
  });
});
