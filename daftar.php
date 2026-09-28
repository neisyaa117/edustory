<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green: #4f8f72;
            --green-dark: #356b53;
            --green-light: #dff1e6;

            --orange: #f49b52;
            --yellow: #ffd76a;

            --cream: #fffaf1;
            --cream-dark: #f7eedf;

            --ink: #26352e;
            --muted: #718078;

            --white: #ffffff;
            --danger: #d85b5b;
        }

        body {
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
            color: var(--ink);

            background:
                radial-gradient(
                    circle at 8% 10%,
                    rgba(255, 215, 106, .28),
                    transparent 25%
                ),
                radial-gradient(
                    circle at 92% 90%,
                    rgba(244, 155, 82, .18),
                    transparent 28%
                ),
                var(--cream);

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 35px 18px;
        }

        .page {
            width: 100%;
            max-width: 1050px;

            display: grid;
            grid-template-columns: .85fr 1.15fr;

            background: rgba(255,255,255,.88);

            border: 1px solid rgba(79,143,114,.12);

            border-radius: 30px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(50, 75, 62, .12);
        }

        /* =========================================
           BAGIAN KIRI
        ========================================= */

        .intro {
            position: relative;

            padding: 55px 45px;

            background:
                linear-gradient(
                    145deg,
                    #e5f4e9 0%,
                    #f7f7d9 52%,
                    #fff0d7 100%
                );

            display: flex;
            flex-direction: column;
            justify-content: center;

            overflow: hidden;
        }

        .intro::before {
            content: "";

            position: absolute;

            width: 220px;
            height: 220px;

            border-radius: 50%;

            background: rgba(255,255,255,.42);

            top: -80px;
            right: -80px;
        }

        .intro::after {
            content: "";

            position: absolute;

            width: 170px;
            height: 170px;

            border-radius: 50%;

            background: rgba(255, 215, 106, .18);

            bottom: -80px;
            left: -70px;
        }

        .logo {
            position: relative;
            z-index: 2;

            display: flex;
            align-items: center;

            gap: 10px;

            margin-bottom: 28px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;

            border-radius: 15px;

            background: var(--green);

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            font-size: 25px;

            box-shadow:
                0 8px 18px rgba(79,143,114,.22);
        }

        .logo-text {
            font-family: 'Baloo 2', sans-serif;

            font-size: 28px;
            font-weight: 800;

            color: var(--green-dark);
        }

        .intro-content {
            position: relative;
            z-index: 2;
        }

        .intro h1 {
            font-family: 'Baloo 2', sans-serif;

            font-size: clamp(38px, 4vw, 58px);

            line-height: .98;

            color: var(--green-dark);

            margin-bottom: 20px;
        }

        .intro h1 span {
            color: var(--orange);
        }

        .intro p {
            max-width: 390px;

            color: #5d6e64;

            font-size: 16px;

            line-height: 1.7;
        }

        .mini-list {
            margin-top: 30px;

            display: flex;
            flex-direction: column;

            gap: 13px;
        }

        .mini-item {
            display: flex;
            align-items: center;

            gap: 12px;

            color: #52645a;

            font-size: 14px;
            font-weight: 700;
        }

        .mini-icon {
            width: 32px;
            height: 32px;

            flex-shrink: 0;

            border-radius: 11px;

            background: rgba(255,255,255,.72);

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--green);
        }

        /* =========================================
           FORM
        ========================================= */

        .form-area {
            padding: 45px 48px;

            background: white;
        }

        .form-title {
            margin-bottom: 28px;
        }

        .form-title h2 {
            font-family: 'Baloo 2', sans-serif;

            font-size: 31px;

            line-height: 1.1;

            color: var(--ink);
        }

        .form-title p {
            margin-top: 6px;

            color: var(--muted);

            font-size: 14px;
        }

        .alert {
            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 13px;

            background: #fff0f0;

            color: var(--danger);

            font-size: 13px;

            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;

            gap: 17px;
        }

        .form-row {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;
        }

        .field {
            display: flex;
            flex-direction: column;

            gap: 7px;
        }

        label {
            font-size: 13px;

            font-weight: 800;

            color: #46574e;
        }

        .required {
            color: var(--orange);
        }

        input,
        select,
        textarea {
            width: 100%;

            border: 1.5px solid #e1e9e4;

            border-radius: 13px;

            padding: 12px 14px;

            font-family: 'Nunito', sans-serif;

            font-size: 14px;

            color: var(--ink);

            background: #fbfdfb;

            outline: none;

            transition: .2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--green);

            background: white;

            box-shadow:
                0 0 0 4px rgba(79,143,114,.08);
        }

        textarea {
            min-height: 90px;

            resize: vertical;
        }

        select {
            cursor: pointer;
        }

        .section-label {
            display: flex;
            align-items: center;

            gap: 8px;

            margin-top: 6px;
            margin-bottom: -3px;

            font-family: 'Baloo 2', sans-serif;

            font-size: 19px;

            font-weight: 700;

            color: var(--green-dark);
        }

        .section-label::before {
            content: "";

            width: 7px;
            height: 23px;

            border-radius: 10px;

            background: var(--orange);
        }

        .location-note {
            margin-top: -4px;

            padding: 10px 13px;

            border-radius: 12px;

            background: #f1f8f3;

            color: #61736a;

            font-size: 12px;

            line-height: 1.5;
        }

        .submit-btn {
            margin-top: 7px;

            border: none;

            border-radius: 14px;

            padding: 14px 20px;

            background: var(--green);

            color: white;

            font-family: 'Nunito', sans-serif;

            font-size: 15px;

            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 9px 20px rgba(79,143,114,.22);

            transition: .2s;
        }

        .submit-btn:hover {
            background: var(--green-dark);

            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;

            margin-top: 2px;

            font-size: 13px;

            color: var(--muted);
        }

        .login-link a {
            color: var(--green-dark);

            font-weight: 800;

            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 850px) {

            body {
                padding: 20px 13px;
            }

            .page {
                grid-template-columns: 1fr;

                max-width: 650px;

                border-radius: 24px;
            }

            .intro {
                padding: 35px 28px;
            }

            .intro h1 {
                font-size: 42px;
            }

            .intro p {
                max-width: none;
            }

            .mini-list {
                display: grid;

                grid-template-columns: 1fr 1fr;
            }

            .form-area {
                padding: 32px 27px;
            }
        }

        @media (max-width: 520px) {

            body {
                padding: 10px;
            }

            .page {
                border-radius: 20px;
            }

            .intro {
                padding: 28px 22px;
            }

            .logo {
                margin-bottom: 20px;
            }

            .logo-text {
                font-size: 25px;
            }

            .intro h1 {
                font-size: 38px;
            }

            .intro p {
                font-size: 14px;
            }

            .mini-list {
                grid-template-columns: 1fr;

                margin-top: 23px;
            }

            .form-area {
                padding: 27px 20px;
            }

            .form-title h2 {
                font-size: 28px;
            }

            .form-row {
                grid-template-columns: 1fr;

                gap: 17px;
            }

            input,
            select,
            textarea {
                font-size: 14px;

                padding: 12px;
            }
        }

    </style>
</head>

<body>

<div class="page">

    <!-- =========================================
         INTRO
    ========================================== -->

    <section class="intro">

        <div class="logo">

            <div class="logo-icon">
                ✦
            </div>

            <div class="logo-text">
                EduStory
            </div>

        </div>

        <div class="intro-content">

            <h1>
                Ceritakan<br>
                <span>Sekolahmu.</span>
            </h1>

            <p>
                Buat artikel sekolah dengan lebih mudah.
                Lengkapi data sekolahmu sekali, lalu gunakan
                EduStory untuk membuat berbagai cerita dan
                informasi tentang sekolah.
            </p>

            <div class="mini-list">

                <div class="mini-item">
                    <div class="mini-icon">✓</div>
                    Data sekolah tersimpan
                </div>

                <div class="mini-item">
                    <div class="mini-icon">⌂</div>
                    Pilih wilayah sekolah
                </div>

                <div class="mini-item">
                    <div class="mini-icon">✎</div>
                    Buat artikel lebih mudah
                </div>

                <div class="mini-item">
                    <div class="mini-icon">★</div>
                    Cocok untuk berbagai jenjang
                </div>

            </div>

        </div>

    </section>


    <!-- =========================================
         FORM
    ========================================== -->

    <section class="form-area">

        <div class="form-title">

            <h2>
                Buat akun EduStory
            </h2>

            <p>
                Isi data berikut untuk mulai menggunakan EduStory.
            </p>

        </div>


        <?php if (!empty($error)): ?>

            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form
            action="proses/proses-daftar.php"
            method="POST"
            autocomplete="off"
        >

            <!-- AKUN -->

            <div class="section-label">
                Akun
            </div>

            <div class="form-row">

                <div class="field">

                    <label>
                        Nama Lengkap
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama"
                        placeholder="Nama lengkap"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        Email
                        <span class="required">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="nama@email.com"
                        required
                    >

                </div>

            </div>


            <div class="field">

                <label>
                    Password
                    <span class="required">*</span>
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Buat password"
                    minlength="6"
                    required
                >

            </div>


            <!-- SEKOLAH -->

            <div class="section-label">
                Data Sekolah
            </div>


            <div class="field">

                <label>
                    Nama Sekolah
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="nama_sekolah"
                    placeholder="Contoh: TK Nusa Indah"
                    required
                >

            </div>


            <div class="form-row">

                <div class="field">

                    <label>
                        Jenjang Sekolah
                        <span class="required">*</span>
                    </label>

                    <select
                        name="jenjang"
                        required
                    >

                        <option value="">
                            Pilih jenjang
                        </option>

                        <option value="PAUD">PAUD</option>
                        <option value="TK">TK</option>
                        <option value="SD">SD</option>
                        <option value="MI">MI</option>
                        <option value="SMP">SMP</option>
                        <option value="MTs">MTs</option>
                        <option value="SMA">SMA</option>
                        <option value="SMK">SMK</option>
                        <option value="MA">MA</option>

                    </select>

                </div>


                <div class="field">

                    <label>
                        Alamat Sekolah
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="alamat"
                        placeholder="Alamat sekolah"
                        required
                    >

                </div>

            </div>


            <!-- WILAYAH -->

            <div class="section-label">
                Lokasi Sekolah
            </div>

            <div class="location-note">
                Pilih provinsi, kabupaten/kota, kecamatan, lalu
                desa/kelurahan. Kode wilayah desa akan tersimpan
                otomatis.
            </div>


            <div class="form-row">

                <div class="field">

                    <label>
                        Provinsi
                        <span class="required">*</span>
                    </label>

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


                <div class="field">

                    <label>
                        Kabupaten / Kota
                        <span class="required">*</span>
                    </label>

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

            </div>


            <div class="form-row">

                <div class="field">

                    <label>
                        Kecamatan
                        <span class="required">*</span>
                    </label>

                    <select
                        id="kecamatan"
                        name="kecamatan"
                        required
                        disabled
                    >

                        <option value="">
                            Pilih kabupaten terlebih dahulu
                        </option>

                    </select>

                </div>


                <div class="field">

                    <label>
                        Desa / Kelurahan
                        <span class="required">*</span>
                    </label>

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

            </div>


            <!--
                KODE DESA DI SINI AKAN DIISI JAVASCRIPT.

                Contoh:
                32.05.10.2003

                disimpan menjadi:
                3205102003
            -->

            <input
                type="hidden"
                name="kode_desa"
                id="kode_desa"
                value=""
            >


            <button
                type="submit"
                class="submit-btn"
            >
                Buat Akun EduStory
            </button>


            <div class="login-link">

                Sudah punya akun?

                <a href="login.php">
                    Masuk di sini
                </a>

            </div>

        </form>

    </section>

</div>


<script>

/*
|--------------------------------------------------------------------------
| API EMSIFA V2
|--------------------------------------------------------------------------
|
| V2 menggunakan kode wilayah administrasi bertingkat.
|
| Contoh:
|
| Provinsi  : 32
| Kabupaten : 32.05
| Kecamatan : 32.05.10
| Desa      : 32.05.10.2003
|
|--------------------------------------------------------------------------
*/

const API = "https://www.emsifa.com/api-wilayah-indonesia/v2";


const provinsi = document.getElementById("provinsi");
const kabupaten = document.getElementById("kabupaten");
const kecamatan = document.getElementById("kecamatan");
const desa = document.getElementById("desa");

const kodeDesa = document.getElementById("kode_desa");


/*
|--------------------------------------------------------------------------
| BERSIHKAN KODE
|--------------------------------------------------------------------------
|
| 32.05.10.2003
|        ↓
| 3205102003
|
|--------------------------------------------------------------------------
*/

function cleanKodeWilayah(kode) {

    if (!kode) {
        return "";
    }

    return String(kode).replace(/\D/g, "");
}


/*
|--------------------------------------------------------------------------
| RESET SELECT
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
| AMBIL DATA API
|--------------------------------------------------------------------------
*/

async function getWilayah(url) {

    const response = await fetch(url, {
        method: "GET",
        headers: {
            "Accept": "application/json"
        }
    });

    if (!response.ok) {
        throw new Error(
            "Gagal mengambil data wilayah. HTTP " +
            response.status
        );
    }

    const data = await response.json();

    return data;
}


/*
|--------------------------------------------------------------------------
| MASUKKAN DATA KE SELECT
|--------------------------------------------------------------------------
*/

function fillSelect(select, data, placeholder) {

    select.innerHTML = "";

    const firstOption = document.createElement("option");

    firstOption.value = "";
    firstOption.textContent = placeholder;

    select.appendChild(firstOption);


    if (!Array.isArray(data)) {

        throw new Error(
            "Format data wilayah tidak valid."
        );

    }


    data.forEach(item => {

        const option = document.createElement("option");

        /*
         * EMSIFA V2:
         *
         * item.id
         * item.name
         *
         * ID sudah berupa kode wilayah bertingkat.
         */

        option.value = item.id;

        option.textContent = item.name;

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

    try {

        provinsi.disabled = true;

        provinsi.innerHTML =
            '<option value="">Memuat provinsi...</option>';


        const data = await getWilayah(
            `${API}/provinces.json`
        );


        fillSelect(
            provinsi,
            data,
            "Pilih provinsi"
        );


    } catch (error) {

        console.error(
            "ERROR PROVINSI:",
            error
        );


        provinsi.innerHTML =
            '<option value="">Gagal memuat provinsi</option>';

        provinsi.disabled = true;

    }

}


/*
|--------------------------------------------------------------------------
| PROVINSI → KABUPATEN
|--------------------------------------------------------------------------
*/

provinsi.addEventListener(
    "change",
    async function () {

        const kodeProvinsi = this.value;


        resetSelect(
            kabupaten,
            "Memuat kabupaten / kota..."
        );


        resetSelect(
            kecamatan,
            "Pilih kabupaten / kota terlebih dahulu"
        );


        resetSelect(
            desa,
            "Pilih kecamatan terlebih dahulu"
        );


        kodeDesa.value = "";


        if (!kodeProvinsi) {

            resetSelect(
                kabupaten,
                "Pilih provinsi terlebih dahulu"
            );

            return;

        }


        try {

            const data = await getWilayah(
                `${API}/regencies/${kodeProvinsi}.json`
            );


            fillSelect(
                kabupaten,
                data,
                "Pilih kabupaten / kota"
            );


        } catch (error) {

            console.error(
                "ERROR KABUPATEN:",
                error
            );


            resetSelect(
                kabupaten,
                "Gagal memuat kabupaten / kota"
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| KABUPATEN → KECAMATAN
|--------------------------------------------------------------------------
*/

kabupaten.addEventListener(
    "change",
    async function () {

        const kodeKabupaten = this.value;


        resetSelect(
            kecamatan,
            "Memuat kecamatan..."
        );


        resetSelect(
            desa,
            "Pilih kecamatan terlebih dahulu"
        );


        kodeDesa.value = "";


        if (!kodeKabupaten) {

            resetSelect(
                kecamatan,
                "Pilih kabupaten / kota terlebih dahulu"
            );

            return;

        }


        try {

            const data = await getWilayah(
                `${API}/districts/${kodeKabupaten}.json`
            );


            fillSelect(
                kecamatan,
                data,
                "Pilih kecamatan"
            );


        } catch (error) {

            console.error(
                "ERROR KECAMATAN:",
                error
            );


            resetSelect(
                kecamatan,
                "Gagal memuat kecamatan"
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| KECAMATAN → DESA
|--------------------------------------------------------------------------
*/

kecamatan.addEventListener(
    "change",
    async function () {

        const kodeKecamatan = this.value;


        resetSelect(
            desa,
            "Memuat desa / kelurahan..."
        );


        kodeDesa.value = "";


        if (!kodeKecamatan) {

            resetSelect(
                desa,
                "Pilih kecamatan terlebih dahulu"
            );

            return;

        }


        try {

            const data = await getWilayah(
                `${API}/villages/${kodeKecamatan}.json`
            );


            fillSelect(
                desa,
                data,
                "Pilih desa / kelurahan"
            );


        } catch (error) {

            console.error(
                "ERROR DESA:",
                error
            );


            resetSelect(
                desa,
                "Gagal memuat desa / kelurahan"
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| DESA DIPILIH
|--------------------------------------------------------------------------
*/

desa.addEventListener(
    "change",
    function () {

        const kode = this.value;


        /*
         * Contoh:
         *
         * 32.05.10.2003
         *
         * menjadi:
         *
         * 3205102003
         */

        kodeDesa.value =
            cleanKodeWilayah(kode);


        console.log(
            "Kode desa terpilih:",
            kode
        );


        console.log(
            "Kode desa untuk database:",
            kodeDesa.value
        );

    }
);


/*
|--------------------------------------------------------------------------
| VALIDASI SEBELUM SUBMIT
|--------------------------------------------------------------------------
*/

document
    .querySelector("form")
    .addEventListener(
        "submit",
        function (event) {

            const kode =
                kodeDesa.value.trim();


            if (!kode) {

                event.preventDefault();

                alert(
                    "Silakan pilih desa / kelurahan terlebih dahulu."
                );

                desa.focus();

                return;

            }


            if (!/^\d{10}$/.test(kode)) {

                event.preventDefault();

                alert(
                    "Kode wilayah desa tidak valid. Silakan pilih desa / kelurahan kembali."
                );

                desa.focus();

                return;

            }

        }
    );


/*
|--------------------------------------------------------------------------
| MULAI LOAD PROVINSI
|--------------------------------------------------------------------------
*/

loadProvinsi();

</script>

</body>
</html>