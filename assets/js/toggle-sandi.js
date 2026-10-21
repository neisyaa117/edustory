/*
| Tombol mata di sebelah kotak password (login, daftar, admin, dsb).
| Berlaku untuk semua ".input-sandi" di halaman, tidak perlu id khusus.
*/
(function () {

    document.querySelectorAll('.input-sandi').forEach(function (kotak) {

        var input  = kotak.querySelector('input');
        var tombol = kotak.querySelector('.toggle-sandi');

        if (!input || !tombol) return;

        tombol.addEventListener('click', function () {

            var tampil = input.type === 'text';

            input.type = tampil ? 'password' : 'text';
            tombol.setAttribute('aria-pressed', tampil ? 'false' : 'true');
            tombol.setAttribute('aria-label', tampil ? 'Tampilkan password' : 'Sembunyikan password');
        });
    });

})();
