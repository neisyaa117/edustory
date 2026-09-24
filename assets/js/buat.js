/*
| Form artikel:
| - foto dikecilkan di browser sebelum dikirim (foto HP bisa sangat besar)
| - pratinjau foto dan tombol hapus
| - pilihan "banyak" yang wajib minimal satu
| - tombol kirim dikunci setelah ditekan
*/
(function () {

    var form = document.getElementById('formArtikel');
    var tombol = document.getElementById('btnKirim');
    var input = document.getElementById('foto');
    var daftar = document.getElementById('fotoPratinjau');
    var pesan = document.getElementById('fotoPesan');

    var MAKS_SISI = 1600;

    /* ---------------- Foto ---------------- */

    if (input && daftar) {

        var maks = parseInt(input.dataset.maks, 10) || 3;
        var berkas = [];

        var kecilkan = function (file) {

            return new Promise(function (selesai) {

                if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
                    selesai(null);
                    return;
                }

                var url = URL.createObjectURL(file);
                var gambar = new Image();

                gambar.onload = function () {

                    URL.revokeObjectURL(url);

                    var skala = Math.min(1, MAKS_SISI / Math.max(gambar.naturalWidth, gambar.naturalHeight));

                    if (skala === 1 && file.type === 'image/jpeg' && file.size < 600 * 1024) {
                        selesai(file);
                        return;
                    }

                    var kanvas = document.createElement('canvas');
                    kanvas.width = Math.round(gambar.naturalWidth * skala);
                    kanvas.height = Math.round(gambar.naturalHeight * skala);

                    var ctx = kanvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, kanvas.width, kanvas.height);
                    ctx.drawImage(gambar, 0, 0, kanvas.width, kanvas.height);

                    kanvas.toBlob(function (blob) {

                        if (!blob) {
                            selesai(file);
                            return;
                        }

                        var nama = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        selesai(new File([blob], nama, { type: 'image/jpeg' }));

                    }, 'image/jpeg', 0.82);
                };

                gambar.onerror = function () {
                    URL.revokeObjectURL(url);
                    selesai(file);
                };

                gambar.src = url;
            });
        };

        var gambarPratinjau = function () {

            daftar.innerHTML = '';

            berkas.forEach(function (f, i) {

                var li = document.createElement('li');
                var img = document.createElement('img');
                var hapus = document.createElement('button');

                img.alt = 'Foto ' + (i + 1);
                img.src = URL.createObjectURL(f);
                img.onload = function () { URL.revokeObjectURL(img.src); };

                hapus.type = 'button';
                hapus.textContent = 'Hapus';
                hapus.setAttribute('aria-label', 'Hapus foto ' + (i + 1));

                hapus.addEventListener('click', function () {
                    berkas.splice(i, 1);
                    sinkron();
                });

                li.appendChild(img);
                li.appendChild(hapus);
                daftar.appendChild(li);
            });
        };

        var sinkron = function () {

            var dt = new DataTransfer();
            berkas.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;

            gambarPratinjau();
        };

        input.addEventListener('change', function () {

            var baru = Array.prototype.slice.call(input.files);
            var sisa = Math.max(maks - berkas.length, 0);
            var catatan = [];

            if (baru.length > sisa) {
                catatan.push('Maksimal ' + maks + ' foto. Foto selebihnya tidak dipakai.');
                baru = baru.slice(0, sisa);
            }

            Promise.all(baru.map(kecilkan)).then(function (hasil) {

                hasil.forEach(function (f, i) {
                    if (f) {
                        berkas.push(f);
                    } else {
                        catatan.push('"' + baru[i].name + '" bukan JPG, PNG, atau WebP.');
                    }
                });

                pesan.textContent = catatan.join(' ');
                sinkron();
            });
        });
    }

    /* ---------------- Kirim ---------------- */

    if (form) {

        form.addEventListener('submit', function (e) {

            var grup = form.querySelectorAll('.pilihan-banyak[data-wajib]');

            for (var i = 0; i < grup.length; i++) {

                var pertama = grup[i].querySelector('input');
                var terpilih = grup[i].querySelector('input:checked');

                if (!terpilih) {
                    e.preventDefault();
                    pertama.setCustomValidity('Pilih minimal satu.');
                    pertama.reportValidity();
                    pertama.addEventListener('change', function () {
                        this.setCustomValidity('');
                    }, { once: true });
                    return;
                }
            }

            if (tombol) {
                tombol.disabled = true;
                tombol.textContent = 'Mengirim...';
            }
        });

        // Bila pengguna kembali dengan tombol back, aktifkan lagi tombolnya.
        window.addEventListener('pageshow', function () {
            if (tombol) {
                tombol.disabled = false;
                tombol.textContent = 'Buat artikel';
            }
        });
    }

})();
