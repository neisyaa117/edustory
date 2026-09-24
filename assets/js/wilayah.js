/*
| Dropdown wilayah (provinsi > kabupaten/kota > kecamatan > desa/kelurahan)
| untuk halaman data sekolah.
|
| - Memilih ulang nilai yang sudah tersimpan (atribut data-nilai)
| - Menyimpan kode wilayah ke kolom tersembunyi kode_*
|   (kode desa dipakai untuk menyambung ke klipaa)
|
| Data dibaca dari assets/js/data-wilayah.js (kode Kemendagri).
| Halaman yang memakai berkas ini harus memuat data-wilayah.js lebih dulu.
*/
(function () {

    var tingkat = [
        {
            nama: 'provinsi',
            kosong: 'Pilih provinsi',
            tunggu: 'Memuat provinsi...',
            muat: function () { return window.dataWilayah.provinsi(); }
        },
        {
            nama: 'kabupaten',
            kosong: 'Pilih kabupaten / kota',
            tunggu: 'Pilih provinsi terlebih dahulu',
            muat: function (id) { return window.dataWilayah.kabupaten(id); }
        },
        {
            nama: 'kecamatan',
            kosong: 'Pilih kecamatan',
            tunggu: 'Pilih kabupaten / kota terlebih dahulu',
            muat: function (id) { return window.dataWilayah.kecamatan(id); }
        },
        {
            nama: 'desa',
            kosong: 'Pilih desa / kelurahan',
            tunggu: 'Pilih kecamatan terlebih dahulu',
            muat: function (id) { return window.dataWilayah.desa(id); }
        }
    ];

    var el = tingkat.map(function (t) { return document.getElementById(t.nama); });
    var kode = tingkat.map(function (t) { return document.getElementById('kode_' + t.nama); });

    for (var k = 0; k < tingkat.length; k++) {
        if (!el[k] || !kode[k]) return;
    }

    // Penanda permintaan terbaru per tingkat, supaya balasan lama tidak menimpa.
    var urutan = [0, 0, 0, 0];

    function reset(i, teks) {
        el[i].innerHTML = '';

        var o = document.createElement('option');
        o.value = '';
        o.textContent = teks;
        el[i].appendChild(o);

        el[i].disabled = true;
        kode[i].value = '';
    }

    function isi(i, data, nilai) {
        reset(i, tingkat[i].kosong);

        var cocok = null;

        data.forEach(function (item) {
            var o = document.createElement('option');
            o.value = item.name;
            o.textContent = item.name;
            o.dataset.id = item.id;
            el[i].appendChild(o);

            if (nilai && item.name === nilai) cocok = o;
        });

        el[i].disabled = false;

        if (cocok) {
            cocok.selected = true;
            kode[i].value = cocok.dataset.id;
            return cocok.dataset.id;
        }

        return '';
    }

    function muat(i, induk, nilai) {
        var n = ++urutan[i];

        reset(i, 'Memuat...');

        return tingkat[i].muat(induk)
            .then(function (data) {
                if (n !== urutan[i]) return '';
                return isi(i, data, nilai);
            })
            .catch(function () {
                if (n === urutan[i]) reset(i, 'Gagal memuat data. Muat ulang halaman.');
                return '';
            });
    }

    function kosongkanDari(mulai) {
        for (var j = mulai; j < tingkat.length; j++) {
            urutan[j]++;
            reset(j, tingkat[j].tunggu);
        }
    }

    // Pilihan pengguna
    tingkat.forEach(function (t, i) {
        el[i].addEventListener('change', function () {
            var opsi = el[i].options[el[i].selectedIndex];
            var id = opsi && opsi.dataset.id ? opsi.dataset.id : '';

            kode[i].value = id;
            kosongkanDari(i + 1);

            if (id && i < tingkat.length - 1) {
                muat(i + 1, id, '');
            }
        });
    });

    // Muat awal, sekalian memilih ulang data yang sudah tersimpan.
    muat(0, '', el[0].dataset.nilai).then(function (idProv) {
        if (!idProv) return;

        return muat(1, idProv, el[1].dataset.nilai).then(function (idKab) {
            if (!idKab) return;

            return muat(2, idKab, el[2].dataset.nilai).then(function (idKec) {
                if (!idKec) return;

                return muat(3, idKec, el[3].dataset.nilai);
            });
        });
    });

})();
