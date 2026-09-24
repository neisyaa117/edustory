/*
| Daftar jenis artikel di beranda: klik satu jenis untuk membaca deskripsinya.
| Mendukung panah keyboard, Home, dan End.
*/
(function () {

    var kotak = document.getElementById('pilihJenis');
    if (!kotak) return;

    var tab = Array.prototype.slice.call(kotak.querySelectorAll('[role="tab"]'));

    function pilih(yang, fokus) {

        tab.forEach(function (t) {
            var aktif = t === yang;

            t.setAttribute('aria-selected', aktif ? 'true' : 'false');
            t.tabIndex = aktif ? 0 : -1;

            var panel = document.getElementById(t.getAttribute('aria-controls'));
            if (panel) panel.hidden = !aktif;
        });

        if (fokus) yang.focus();

        yang.scrollIntoView({ block: 'nearest', inline: 'center' });
    }

    tab.forEach(function (t, i) {

        t.addEventListener('click', function () {
            pilih(t, false);
        });

        t.addEventListener('keydown', function (e) {

            var ke = -1;

            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') ke = (i + 1) % tab.length;
            else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') ke = (i - 1 + tab.length) % tab.length;
            else if (e.key === 'Home') ke = 0;
            else if (e.key === 'End') ke = tab.length - 1;

            if (ke >= 0) {
                e.preventDefault();
                pilih(tab[ke], true);
            }
        });
    });

})();
