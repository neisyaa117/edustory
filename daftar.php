<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* =========================
           FORM PENDAFTARAN
        ========================= */

        .auth-box {
            max-width: 620px;
        }
        .form-message {
    width: 100%;
    max-width: 620px;
    margin-bottom: 15px;
    padding: 13px 16px;
    border-radius: 12px;
    background: #fff0e5;
    border: 1px solid #f0c5a8;
    color: #8a4e35;
    font-size: 14px;
    font-weight: 700;
}

        .form-section-title {
            margin: 28px 0 15px;
            padding-top: 20px;
            border-top: 1px solid #e8e2d8;
        }

        .form-section-title:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .form-section-title h3 {
            margin: 0 0 4px;
            font-size: 17px;
            color: #18252b;
        }

        .form-section-title p {
            margin: 0;
            font-size: 13px;
            color: #7a817e;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "⌄";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-55%);
            color: #6f7975;
            font-size: 18px;
            pointer-events: none;
        }

        .select-wrapper select {
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-right: 42px;
        }

        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #8a918e;
        }

        .location-loading {
            font-size: 12px;
            color: #4f8f78;
            margin-top: 6px;
            display: none;
        }

        .location-error {
            display: none;
            margin-top: 7px;
            font-size: 12px;
            color: #c85f4d;
        }

        .school-input {
            position: relative;
        }

        .school-input input {
            padding-left: 44px;
        }

        .school-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 17px;
            pointer-events: none;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .auth-box {
                width: calc(100% - 30px);
            }
        }
    </style>
</head>

<body class="auth-page">

<nav class="navbar">
    <div class="nav-container">

        <a href="index.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <a href="index.php" class="back-home">
            ← Beranda
        </a>

    </div>
</nav>


<main class="auth-wrapper">
    <?php if (isset($_SESSION['pesan'])): ?>

    <div class="form-message">
        <?php
        echo htmlspecialchars($_SESSION['pesan']);
        unset($_SESSION['pesan']);
        ?>
    </div>

<?php endif; ?>


    <div class="auth-decoration decoration-left">
        <span>✦</span>
    </div>

    <div class="auth-decoration decoration-right">
        <span>✎</span>
    </div>


    <div class="auth-box">

        <div class="auth-heading">

            <div class="auth-icon">
                ✎
            </div>

            <span class="section-label">
                MULAI DI SINI
            </span>

            <h1>Buat akun baru</h1>

            <p>
                Isi data singkat tentang kamu dan sekolahmu
                untuk mulai menggunakan EduStory.
            </p>

        </div>


        <form action="proses/proses-daftar.php" method="POST" id="formDaftar">

            <!-- =========================
                 DATA AKUN
            ========================== -->

            <div class="form-section-title">
                <h3>Data akun</h3>
                <p>Gunakan data yang bisa kamu gunakan untuk masuk.</p>
            </div>


            <div class="form-group">
                <label for="nama">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Nama kamu"
                    autocomplete="name"
                    required
                >
            </div>


            <div class="form-group">
                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                >
            </div>


            <div class="form-group">
                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimal 6 karakter"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

                <small>Password minimal 6 karakter.</small>
            </div>


            <!-- =========================
                 DATA SEKOLAH
            ========================== -->

            <div class="form-section-title">
                <h3>Tentang sekolahmu</h3>
                <p>Pilih lokasi sekolah dan masukkan nama sekolah.</p>
            </div>


            <!-- PROVINSI -->

            <div class="form-group">

                <label for="provinsi">
                    Provinsi
                </label>

                <div class="select-wrapper">

                    <select
                        id="provinsi"
                        name="provinsi"
                        required
                    >

                        <option value="">
                            Memuat provinsi...
                        </option>

                    </select>

                </div>

                <div id="loadingProvinsi" class="location-loading">
                    Sedang mengambil data provinsi...
                </div>

                <div id="errorProvinsi" class="location-error">
                    Data provinsi gagal dimuat. Coba refresh halaman.
                </div>

            </div>


            <!-- KABUPATEN -->

            <div class="form-group">

                <label for="kabupaten">
                    Kabupaten / Kota
                </label>

                <div class="select-wrapper">

                    <select
                        id="kabupaten"
                        name="kabupaten"
                        required
                        disabled
                    >

                        <option value="">
                            Pilih provinsi terlebih dahulu
                        </option>

                    </select>

                </div>

                <div id="loadingKabupaten" class="location-loading">
                    Sedang mengambil data kabupaten/kota...
                </div>

            </div>


            <!-- KECAMATAN -->

            <div class="form-group">

                <label for="kecamatan">
                    Kecamatan
                </label>

                <div class="select-wrapper">

                    <select
                        id="kecamatan"
                        name="kecamatan"
                        required
                        disabled
                    >

                        <option value="">
                            Pilih kabupaten/kota terlebih dahulu
                        </option>

                    </select>

                </div>

                <div id="loadingKecamatan" class="location-loading">
                    Sedang mengambil data kecamatan...
                </div>

            </div>


            <!-- DESA -->

            <div class="form-group">

                <label for="desa">
                    Desa / Kelurahan
                </label>

                <div class="select-wrapper">

                    <select
                        id="desa"
                        name="desa"
                        required
                        disabled
                    >

                        <option value="">
                            Pilih kecamatan terlebih dahulu
                        </option>

                    </select>

                </div>

                <div id="loadingDesa" class="location-loading">
                    Sedang mengambil data desa/kelurahan...
                </div>

            </div>


            <!-- NAMA SEKOLAH -->

            <div class="form-group">

                <label for="nama_sekolah">
                    Nama Sekolah
                </label>

                <div class="school-input">

                    <span class="school-icon">🏫</span>

                    <input
                        type="text"
                        id="nama_sekolah"
                        name="nama_sekolah"
                        placeholder="Contoh: TK Nusa Indah"
                        autocomplete="organization"
                        required
                    >

                </div>

                <small>
                    Tulis nama sekolah secara lengkap.
                </small>

            </div>


            <!-- KODE WILAYAH (diisi otomatis, untuk klipaa) -->

            <input type="hidden" name="kode_provinsi" id="kode_provinsi">
            <input type="hidden" name="kode_kabupaten" id="kode_kabupaten">
            <input type="hidden" name="kode_kecamatan" id="kode_kecamatan">
            <input type="hidden" name="kode_desa" id="kode_desa">


            <!-- BUTTON -->

            <button type="submit" class="btn-primary btn-full" id="btnDaftar">

                Buat Akun

                <span>→</span>

            </button>

        </form>


        <div class="auth-footer">

            Sudah punya akun?

            <a href="login.php">
                Masuk di sini
            </a>

        </div>

    </div>

</main>


<footer class="footer">

    <p>
        © <?php echo date('Y'); ?> EduStory · Cerita sekolah, jadi lebih mudah.
    </p>

</footer>


<script src="assets/js/data-wilayah.js?v=<?= filemtime(__DIR__ . '/assets/js/data-wilayah.js') ?>"></script>

<script>
/*
|--------------------------------------------------------------------------
| API WILAYAH INDONESIA
|--------------------------------------------------------------------------
|
| Alur:
| Provinsi
|    ↓
| Kabupaten / Kota
|    ↓
| Kecamatan
|    ↓
| Desa / Kelurahan
|
*/



const provinsiSelect  = document.getElementById("provinsi");
const kabupatenSelect = document.getElementById("kabupaten");
const kecamatanSelect = document.getElementById("kecamatan");
const desaSelect      = document.getElementById("desa");


const loadingProvinsi  = document.getElementById("loadingProvinsi");
const loadingKabupaten = document.getElementById("loadingKabupaten");
const loadingKecamatan = document.getElementById("loadingKecamatan");
const loadingDesa      = document.getElementById("loadingDesa");

const errorProvinsi = document.getElementById("errorProvinsi");


/*
|--------------------------------------------------------------------------
| Fungsi mengambil data
|--------------------------------------------------------------------------
*/

async function ambilData(url) {

    const response = await fetch(url);

    if (!response.ok) {
        throw new Error("Gagal mengambil data.");
    }

    return await response.json();
}


/*
|--------------------------------------------------------------------------
| Reset dropdown
|--------------------------------------------------------------------------
*/

function resetSelect(select, text) {

    select.innerHTML = "";

    const option = document.createElement("option");

    option.value = "";
    option.textContent = text;

    select.appendChild(option);

    select.disabled = true;
}


/*
|--------------------------------------------------------------------------
| Isi dropdown
|--------------------------------------------------------------------------
*/

function isiSelect(select, data) {

    data.forEach(item => {

        const option = document.createElement("option");

        option.value = item.name;
        option.textContent = item.name;

        /*
         * ID wilayah disimpan agar bisa digunakan
         * untuk mengambil wilayah berikutnya.
         */

        option.dataset.id = item.id;

        select.appendChild(option);

    });

    select.disabled = false;
}


/*
|--------------------------------------------------------------------------
| LOAD PROVINSI
|--------------------------------------------------------------------------
*/

async function loadProvinsi() {

    loadingProvinsi.style.display = "block";
    errorProvinsi.style.display = "none";

    try {

        const data = await dataWilayah.provinsi();

        provinsiSelect.innerHTML =
            '<option value="">Pilih provinsi</option>';

        data.forEach(item => {

            const option = document.createElement("option");

            option.value = item.name;
            option.textContent = item.name;
            option.dataset.id = item.id;

            provinsiSelect.appendChild(option);

        });

        provinsiSelect.disabled = false;

    } catch (error) {

        console.error(error);

        provinsiSelect.innerHTML =
            '<option value="">Gagal memuat provinsi</option>';

        errorProvinsi.style.display = "block";

    } finally {

        loadingProvinsi.style.display = "none";

    }
}


/*
|--------------------------------------------------------------------------
| PROVINSI → KABUPATEN
|--------------------------------------------------------------------------
*/

provinsiSelect.addEventListener("change", async function() {

    const idProvinsi =
        this.options[this.selectedIndex].dataset.id;


    resetSelect(
        kabupatenSelect,
        "Memuat kabupaten/kota..."
    );

    resetSelect(
        kecamatanSelect,
        "Pilih kabupaten/kota terlebih dahulu"
    );

    resetSelect(
        desaSelect,
        "Pilih kecamatan terlebih dahulu"
    );


    if (!idProvinsi) {

        resetSelect(
            kabupatenSelect,
            "Pilih provinsi terlebih dahulu"
        );

        return;

    }


    loadingKabupaten.style.display = "block";


    try {

        const data = await dataWilayah.kabupaten(idProvinsi);

        kabupatenSelect.innerHTML =
            '<option value="">Pilih kabupaten/kota</option>';

        isiSelect(
            kabupatenSelect,
            data
        );

    } catch (error) {

        console.error(error);

        kabupatenSelect.innerHTML =
            '<option value="">Gagal memuat data</option>';

    } finally {

        loadingKabupaten.style.display = "none";

    }

});


/*
|--------------------------------------------------------------------------
| KABUPATEN → KECAMATAN
|--------------------------------------------------------------------------
*/

kabupatenSelect.addEventListener("change", async function() {

    const idKabupaten =
        this.options[this.selectedIndex].dataset.id;


    resetSelect(
        kecamatanSelect,
        "Memuat kecamatan..."
    );

    resetSelect(
        desaSelect,
        "Pilih kecamatan terlebih dahulu"
    );


    if (!idKabupaten) {

        resetSelect(
            kecamatanSelect,
            "Pilih kabupaten/kota terlebih dahulu"
        );

        return;

    }


    loadingKecamatan.style.display = "block";


    try {

        const data = await dataWilayah.kecamatan(idKabupaten);

        kecamatanSelect.innerHTML =
            '<option value="">Pilih kecamatan</option>';

        isiSelect(
            kecamatanSelect,
            data
        );

    } catch (error) {

        console.error(error);

        kecamatanSelect.innerHTML =
            '<option value="">Gagal memuat data</option>';

    } finally {

        loadingKecamatan.style.display = "none";

    }

});


/*
|--------------------------------------------------------------------------
| KECAMATAN → DESA / KELURAHAN
|--------------------------------------------------------------------------
*/

kecamatanSelect.addEventListener("change", async function() {

    const idKecamatan =
        this.options[this.selectedIndex].dataset.id;


    resetSelect(
        desaSelect,
        "Memuat desa/kelurahan..."
    );


    if (!idKecamatan) {

        resetSelect(
            desaSelect,
            "Pilih kecamatan terlebih dahulu"
        );

        return;

    }


    loadingDesa.style.display = "block";


    try {

        const data = await dataWilayah.desa(idKecamatan);

        desaSelect.innerHTML =
            '<option value="">Pilih desa/kelurahan</option>';

        isiSelect(
            desaSelect,
            data
        );

    } catch (error) {

        console.error(error);

        desaSelect.innerHTML =
            '<option value="">Gagal memuat data</option>';

    } finally {

        loadingDesa.style.display = "none";

    }

});


/*
|--------------------------------------------------------------------------
| Jalankan saat halaman dibuka
|--------------------------------------------------------------------------
*/

loadProvinsi();


/*
|--------------------------------------------------------------------------
| Cegah submit kalau wilayah belum lengkap
|--------------------------------------------------------------------------
*/

document.getElementById("formDaftar").addEventListener("submit", function(event) {

    if (
        !provinsiSelect.value ||
        !kabupatenSelect.value ||
        !kecamatanSelect.value ||
        !desaSelect.value
    ) {

        event.preventDefault();

        alert(
            "Silakan lengkapi Provinsi, Kabupaten/Kota, Kecamatan, dan Desa/Kelurahan terlebih dahulu."
        );

    }

});
</script>


<script>
/*
| Simpan kode wilayah dari pilihan dropdown ke kolom tersembunyi.
| Kode ini dipakai untuk menyambung artikel ke klipaa.
*/
(function () {

    var urutan = ['provinsi', 'kabupaten', 'kecamatan', 'desa'];

    urutan.forEach(function (nama, i) {

        var pilihan = document.getElementById(nama);
        var kode = document.getElementById('kode_' + nama);

        if (!pilihan || !kode) return;

        pilihan.addEventListener('change', function () {

            var opsi = pilihan.options[pilihan.selectedIndex];
            kode.value = (opsi && opsi.dataset.id) ? opsi.dataset.id : '';

            // Pilihan di atasnya berubah, kode di bawahnya tidak berlaku lagi.
            for (var j = i + 1; j < urutan.length; j++) {
                document.getElementById('kode_' + urutan[j]).value = '';
            }
        });
    });

})();
</script>

</body>
</html>