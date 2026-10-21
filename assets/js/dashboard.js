/*
| Daftar jenis artikel di beranda: tiap item punya tombol "?" yang membuka
| jendela kecil (dialog) berisi penjelasan lengkap jenis itu.
*/
(function () {

    var kotak = document.getElementById('pilihJenis');
    if (!kotak) return;

    kotak.querySelectorAll('.pilih-tanya').forEach(function (tombol) {

        var dialog = document.getElementById(tombol.getAttribute('data-modal'));
        if (!dialog) return;

        tombol.addEventListener('click', function () {
            dialog.showModal();
        });

        // Tutup lewat tombol ✕
        dialog.querySelectorAll('[data-tutup-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                dialog.close();
            });
        });

        // Tutup kalau area gelap di luar kotak ikut diklik.
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });
    });

})();
