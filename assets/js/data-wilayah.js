/*
| Data wilayah (provinsi > kabupaten/kota > kecamatan > desa/kelurahan)
| dengan kode Kemendagri, dibaca dari berkas di assets/wilayah/.
|
| Sumber: Kepmendagri No. 300.2.2-2138 Tahun 2025 (dan pembaruannya),
| lewat https://github.com/cahyadsn/wilayah (lisensi MIT).
|
| Kode ditulis tanpa titik: 32 > 3205 > 320510 > 3205102003
| (Kemendagri menulisnya 32 > 32.05 > 32.05.10 > 32.05.10.2003).
|
| Setiap fungsi mengembalikan Promise berisi daftar [{id, name}].
*/
window.dataWilayah = (function () {

    var DASAR = 'assets/wilayah/';

    var indeks = null;     // seluruh provinsi beserta kabupaten/kota
    var perKab = {};       // isi satu kabupaten/kota: kecamatan beserta desanya

    function ambil(url) {
        return fetch(url).then(function (r) {
            if (!r.ok) throw new Error('Gagal mengambil data wilayah.');
            return r.json();
        });
    }

    function daftar(pasangan) {
        return pasangan.map(function (p) {
            return { id: p[0], name: p[1] };
        });
    }

    function muatIndeks() {

        if (indeks) return Promise.resolve(indeks);

        return ambil(DASAR + 'index.json').then(function (d) {
            indeks = d;
            return d;
        });
    }

    function muatKab(idKab) {

        if (perKab[idKab]) return Promise.resolve(perKab[idKab]);

        return ambil(DASAR + 'kab/' + encodeURIComponent(idKab) + '.json').then(function (d) {
            perKab[idKab] = d;
            return d;
        });
    }

    return {

        provinsi: function () {
            return muatIndeks().then(daftar);
        },

        kabupaten: function (idProvinsi) {
            return muatIndeks().then(function (d) {
                for (var i = 0; i < d.length; i++) {
                    if (d[i][0] === String(idProvinsi)) return daftar(d[i][2]);
                }
                return [];
            });
        },

        kecamatan: function (idKabupaten) {
            return muatKab(String(idKabupaten)).then(daftar);
        },

        desa: function (idKecamatan) {

            idKecamatan = String(idKecamatan);

            // Empat angka pertama kode kecamatan adalah kode kabupaten/kota.
            return muatKab(idKecamatan.substring(0, 4)).then(function (d) {
                for (var i = 0; i < d.length; i++) {
                    if (d[i][0] === idKecamatan) return daftar(d[i][2]);
                }
                return [];
            });
        }
    };

})();
