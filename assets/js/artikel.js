/*
| Halaman artikel:
| - menulis artikel lewat proses/generate.php lalu memuat ulang halaman
| - tombol salin (judul, isi, kode desa)
| - tulis ulang artikel
*/
(function () {

    /* ---------------- Salin ---------------- */

    function teksIsi() {

        var kotak = document.getElementById('isiArtikel');
        if (!kotak) return '';

        var bagian = [];

        Array.prototype.forEach.call(kotak.children, function (el) {

            var tag = el.tagName;

            if (tag === 'UL' || tag === 'OL') {

                var no = 0;

                bagian.push(Array.prototype.map.call(el.children, function (li) {
                    no++;
                    return (tag === 'OL' ? no + '. ' : '- ') + li.textContent.trim();
                }).join('\n'));

            } else {
                bagian.push(el.textContent.trim());
            }
        });

        return bagian.join('\n\n');
    }

    function salinTeks(teks, html) {

        var cadangan = function () {

            var t = document.createElement('textarea');
            t.value = teks;
            t.setAttribute('readonly', '');
            t.style.position = 'fixed';
            t.style.opacity = '0';
            document.body.appendChild(t);
            t.select();

            var ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }

            document.body.removeChild(t);
            return ok;
        };

        // Isi artikel disalin sebagai HTML dan teks biasa: tempel di editor
        // yang mendukung format akan mempertahankan subjudul dan daftar.
        if (html && window.ClipboardItem && navigator.clipboard && navigator.clipboard.write) {

            var item = new ClipboardItem({
                'text/html': new Blob([html], { type: 'text/html' }),
                'text/plain': new Blob([teks], { type: 'text/plain' })
            });

            return navigator.clipboard.write([item]).then(function () { return true; }, function () { return cadangan(); });
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(teks).then(function () { return true; }, function () { return cadangan(); });
        }

        return Promise.resolve(cadangan());
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-salin]'), function (tombol) {

        var awal = tombol.textContent;

        tombol.addEventListener('click', function () {

            var jenis = tombol.dataset.salin;
            var teks = '';
            var html = '';

            if (jenis === 'judul') {
                teks = document.getElementById('judulArtikel').textContent.trim();
            } else if (jenis === 'kode') {
                teks = document.getElementById('kodeDesa').textContent.trim();
            } else {
                teks = teksIsi();
                html = document.getElementById('isiArtikel').innerHTML;
            }

            salinTeks(teks, html).then(function (ok) {

                tombol.textContent = ok ? 'Tersalin' : 'Gagal menyalin';

                setTimeout(function () { tombol.textContent = awal; }, 1800);
            });
        });
    });


    /* ---------------- Tulis ulang ---------------- */

    var btnUlang = document.getElementById('btnUlang');

    if (btnUlang) {

        btnUlang.addEventListener('click', function () {

            if (!window.confirm('Artikel yang sekarang akan diganti dengan versi baru. Lanjutkan?')) return;

            var pesan = document.getElementById('aksiPesan');
            var awal = btnUlang.textContent;

            var fd = new FormData();
            fd.append('id', btnUlang.dataset.id);
            fd.append('csrf', btnUlang.dataset.csrf);
            fd.append('ulang', '1');

            btnUlang.disabled = true;
            btnUlang.textContent = 'Sedang menulis ulang...';
            pesan.textContent = '';

            fetch('proses/generate.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) {

                    if (d.ok && d.status === 'selesai') {
                        window.location.reload();
                        return;
                    }

                    pesan.textContent = d.pesan || 'Belum berhasil menulis ulang.';
                    btnUlang.disabled = false;
                    btnUlang.textContent = awal;
                })
                .catch(function () {
                    pesan.textContent = 'Koneksi terputus. Muat ulang halaman untuk melihat hasilnya.';
                    btnUlang.disabled = false;
                    btnUlang.textContent = awal;
                });
        });
    }


    /* ---------------- Menulis artikel ---------------- */

    var kotak = document.getElementById('menulis');

    if (!kotak) return;

    var id = kotak.dataset.id;
    var csrf = kotak.dataset.csrf;

    var judul = document.getElementById('menulisJudul');
    var teksTunggu = document.getElementById('menulisTeks');
    var kotakGalat = document.getElementById('menulisGalat');
    var teksGalat = document.getElementById('menulisGalatTeks');
    var btnCoba = document.getElementById('btnCoba');
    var garis = kotak.querySelector('.menulis-garis');

    function tampilkanMenunggu() {
        kotakGalat.hidden = true;
        teksTunggu.hidden = false;
        garis.hidden = false;
        kotak.classList.remove('menulis-gagal');
    }

    function tampilkanGalat(pesan) {
        garis.hidden = true;
        teksTunggu.hidden = true;
        kotak.classList.add('menulis-gagal');
        teksGalat.textContent = pesan;
        kotakGalat.hidden = false;
    }

    function pantau(sisa) {

        fetch('proses/generate.php?cek=1&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {

                if (d.status === 'selesai') {
                    window.location.reload();
                } else if (d.status === 'gagal') {
                    tampilkanGalat(d.pesan || 'Artikel belum berhasil ditulis.');
                } else if (sisa > 0) {
                    setTimeout(function () { pantau(sisa - 1); }, 3000);
                } else {
                    tampilkanGalat('Prosesnya lebih lama dari biasanya. Coba lagi.');
                }
            })
            .catch(function () {
                if (sisa > 0) {
                    setTimeout(function () { pantau(sisa - 1); }, 4000);
                } else {
                    tampilkanGalat('Koneksi terputus. Muat ulang halaman ini.');
                }
            });
    }

    function mulai() {

        tampilkanMenunggu();

        var fd = new FormData();
        fd.append('id', id);
        fd.append('csrf', csrf);

        fetch('proses/generate.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json().catch(function () {
                    return { ok: false, pesan: 'Balasan server tidak terbaca. Coba lagi.' };
                });
            })
            .then(function (d) {

                if (d.ok && d.status === 'selesai') {
                    window.location.reload();
                } else if (d.ok) {
                    pantau(60);
                } else {
                    tampilkanGalat(d.pesan || 'Artikel belum berhasil ditulis.');
                }
            })
            .catch(function () {
                // Koneksi putus di tengah jalan: server mungkin tetap menyelesaikannya.
                pantau(40);
            });
    }

    btnCoba.addEventListener('click', mulai);

    if (kotak.dataset.gagal === '1') {
        tampilkanGalat(kotak.dataset.pesan);
    } else if (kotak.dataset.status === 'menulis') {
        tampilkanMenunggu();
        pantau(60);
    } else {
        mulai();
    }

})();
