/*
| Skrip halaman admin:
|   1. konfirmasi sebelum hapus (artikel dan jenis artikel)
|   2. jendela "Lihat isi" artikel
|   3. tombol emoji pada form jenis artikel
|   4. pembangun bagian + pertanyaan untuk form jenis artikel
*/
(function () {

    /* ---------- 1. Konfirmasi hapus ---------- */

    document.querySelectorAll('[data-konfirmasi-hapus]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var judul = form.getAttribute('data-judul') || 'artikel ini';
            if (!confirm('Hapus "' + judul + '"? Foto dan isinya ikut terhapus, tidak bisa dikembalikan.')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-konfirmasi]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.getAttribute('data-konfirmasi'))) {
                e.preventDefault();
            }
        });
    });


    /* ---------- 2. Lihat isi artikel ---------- */

    document.querySelectorAll('.adm-lihat-isi').forEach(function (tombol) {
        var dialog = document.getElementById(tombol.getAttribute('data-modal'));
        if (!dialog) return;

        tombol.addEventListener('click', function () { dialog.showModal(); });

        dialog.querySelectorAll('[data-tutup-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () { dialog.close(); });
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });
    });


    /* ---------- 3. Tombol emoji ---------- */

    document.querySelectorAll('[data-emoji-untuk]').forEach(function (kotak) {
        var input = document.getElementById(kotak.getAttribute('data-emoji-untuk'));
        if (!input) return;

        kotak.querySelectorAll('[data-emoji]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                input.value = btn.getAttribute('data-emoji');
                input.focus();
            });
        });
    });


    /* ---------- 4. Pembangun form jenis artikel ---------- */

    var tempat = document.getElementById('pembangun');
    var form   = document.getElementById('formJenis');
    if (!tempat || !form) return;

    var data = [];
    var tipe = {};

    try {
        data = JSON.parse(document.getElementById('dataBagian').textContent) || [];
        tipe = JSON.parse(document.getElementById('dataTipe').textContent) || {};
    } catch (err) {
        data = [];
    }

    var TIPE_PILIHAN = ['pilihan', 'banyak', 'menu'];
    var TIPE_TEKS    = ['teks', 'panjang'];

    // Semua teks dari admin dimasukkan lewat .value / textContent, bukan innerHTML.
    function buat(nama, atribut, anak) {
        var el = document.createElement(nama);

        Object.keys(atribut || {}).forEach(function (k) {
            if (k === 'class') el.className = atribut[k];
            else if (k === 'text') el.textContent = atribut[k];
            else el.setAttribute(k, atribut[k]);
        });

        (anak || []).forEach(function (a) { el.appendChild(a); });

        return el;
    }

    function tombolAlat(simbol, judul, klik, kelas) {
        var b = buat('button', { type: 'button', title: judul, 'aria-label': judul, text: simbol, 'class': kelas || '' });
        b.addEventListener('click', klik);
        return b;
    }

    function pindah(daftar, i, arah) {
        var j = i + arah;
        if (j < 0 || j >= daftar.length) return;
        var t = daftar[i]; daftar[i] = daftar[j]; daftar[j] = t;
        gambar();
    }

    function pertanyaanBaru() {
        return { nama: '', label: '', tipe: 'teks', wajib: false, placeholder: '', bantuan: '', opsi: '' };
    }

    function bagianBaru() {
        return { judul: '', info: '', fields: [pertanyaanBaru()] };
    }

    function gambarPertanyaan(bagian, i) {
        var f = bagian.fields[i];

        var label = buat('input', { type: 'text', placeholder: 'Teks pertanyaan', maxlength: '160', 'aria-label': 'Teks pertanyaan' });
        label.value = f.label;
        label.addEventListener('input', function () { f.label = label.value; });

        var pilihTipe = buat('select', { 'aria-label': 'Jenis isian' });
        Object.keys(tipe).forEach(function (k) {
            var o = buat('option', { value: k, text: tipe[k] });
            if (k === f.tipe) o.selected = true;
            pilihTipe.appendChild(o);
        });

        var cekWajib = buat('input', { type: 'checkbox' });
        cekWajib.checked = !!f.wajib;
        cekWajib.addEventListener('change', function () { f.wajib = cekWajib.checked; });
        var wajib = buat('label', { 'class': 'adm-wajib' }, [cekWajib, document.createTextNode('Wajib')]);

        var alat = buat('div', { 'class': 'adm-alat' }, [
            tombolAlat('↑', 'Naikkan', function () { pindah(bagian.fields, i, -1); }),
            tombolAlat('↓', 'Turunkan', function () { pindah(bagian.fields, i, 1); }),
            tombolAlat('✕', 'Hapus pertanyaan', function () {
                bagian.fields.splice(i, 1);
                gambar();
            }, 'adm-alat-hapus'),
        ]);
        alat.children[0].disabled = i === 0;
        alat.children[1].disabled = i === bagian.fields.length - 1;

        var kepala = buat('div', { 'class': 'adm-bagian-kepala' }, [label, pilihTipe, wajib, alat]);

        var baris = buat('div', { 'class': 'adm-pertanyaan-baris' });

        var petunjuk = buat('input', { type: 'text', maxlength: '160', placeholder: 'Contoh isian yang pudar di kotak' });
        petunjuk.value = f.placeholder;
        petunjuk.addEventListener('input', function () { f.placeholder = petunjuk.value; });
        var kolomPetunjuk = buat('div', {}, [buat('span', { 'class': 'adm-mini-label', text: 'Contoh isian' }), petunjuk]);

        var bantuan = buat('input', { type: 'text', maxlength: '200', placeholder: 'Tulisan kecil di bawah pertanyaan' });
        bantuan.value = f.bantuan;
        bantuan.addEventListener('input', function () { f.bantuan = bantuan.value; });
        var kolomBantuan = buat('div', {}, [buat('span', { 'class': 'adm-mini-label', text: 'Petunjuk (opsional)' }), bantuan]);

        var opsi = buat('textarea', { rows: '4', placeholder: 'Satu pilihan per baris.\nBoleh tulis "nilai | tampilan".' });
        opsi.value = f.opsi;
        opsi.addEventListener('input', function () { f.opsi = opsi.value; });
        var kolomOpsi = buat('div', { 'class': 'adm-pertanyaan-baris satu' }, [
            buat('div', {}, [buat('span', { 'class': 'adm-mini-label', text: 'Pilihan jawaban (minimal 2)' }), opsi]),
        ]);

        function atur() {
            var pilihan = TIPE_PILIHAN.indexOf(f.tipe) !== -1;
            var teks    = TIPE_TEKS.indexOf(f.tipe) !== -1;

            kolomOpsi.style.display = pilihan ? '' : 'none';
            kolomPetunjuk.style.display = teks ? '' : 'none';
            baris.className = 'adm-pertanyaan-baris' + (teks ? '' : ' satu');
        }

        pilihTipe.addEventListener('change', function () { f.tipe = pilihTipe.value; atur(); });

        baris.appendChild(kolomPetunjuk);
        baris.appendChild(kolomBantuan);
        atur();

        return buat('div', { 'class': 'adm-pertanyaan' }, [kepala, baris, kolomOpsi]);
    }

    function gambarBagian(bagian, i) {
        var judul = buat('input', { type: 'text', maxlength: '80', placeholder: 'Judul bagian, mis. "Tentang kegiatannya"', 'aria-label': 'Judul bagian', 'class': 'adm-bagian-judul' });
        judul.value = bagian.judul;
        judul.addEventListener('input', function () { bagian.judul = judul.value; });

        var info = buat('input', { type: 'text', maxlength: '160', placeholder: 'Keterangan singkat (opsional)', 'aria-label': 'Keterangan bagian' });
        info.value = bagian.info;
        info.addEventListener('input', function () { bagian.info = info.value; });

        var alat = buat('div', { 'class': 'adm-alat' }, [
            tombolAlat('↑', 'Naikkan bagian', function () { pindah(data, i, -1); }),
            tombolAlat('↓', 'Turunkan bagian', function () { pindah(data, i, 1); }),
            tombolAlat('✕', 'Hapus bagian', function () {
                if (bagian.fields.length && !confirm('Hapus bagian ini beserta ' + bagian.fields.length + ' pertanyaannya?')) return;
                data.splice(i, 1);
                gambar();
            }, 'adm-alat-hapus'),
        ]);
        alat.children[0].disabled = i === 0;
        alat.children[1].disabled = i === data.length - 1;

        var daftar = buat('div', { 'class': 'adm-pertanyaan-daftar' });
        bagian.fields.forEach(function (_, n) { daftar.appendChild(gambarPertanyaan(bagian, n)); });

        var tambah = buat('button', { type: 'button', 'class': 'adm-tambah-tanya', text: '+ Tambah pertanyaan' });
        tambah.addEventListener('click', function () {
            bagian.fields.push(pertanyaanBaru());
            gambar();
        });

        return buat('div', { 'class': 'adm-bagian' }, [
            buat('div', { 'class': 'adm-bagian-kepala' }, [judul, info, alat]),
            daftar,
            tambah,
        ]);
    }

    function gambar() {
        tempat.textContent = '';
        data.forEach(function (b, i) { tempat.appendChild(gambarBagian(b, i)); });
    }

    document.getElementById('tambahBagian').addEventListener('click', function () {
        data.push(bagianBaru());
        gambar();
    });

    form.addEventListener('submit', function () {
        document.getElementById('bagianJson').value = JSON.stringify(data);
    });

    if (!data.length) data.push(bagianBaru());
    gambar();

})();
