/* Tanda "?" di tiap kotak jenis artikel membuka petunjuk singkat (popup). */
(function () {
    document.addEventListener('click', function (e) {
        var buka = e.target.closest('[data-dialog]');
        if (buka) {
            var d = document.getElementById(buka.getAttribute('data-dialog'));
            if (d && d.showModal) d.showModal();
            return;
        }
        if (e.target.closest('[data-tutup]')) {
            var dlg = e.target.closest('dialog');
            if (dlg) dlg.close();
            return;
        }
        /* Ketuk area gelap di luar kotak popup = tutup. */
        if (e.target.tagName === 'DIALOG') e.target.close();
    });
})();
