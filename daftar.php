```php
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

    <title>Daftar — EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green: #4f8f68;
            --green-dark: #326b4b;
            --green-soft: #e8f4ec;

            --orange: #f29b50;
            --orange-soft: #fff0df;

            --yellow: #f6d66d;
            --yellow-soft: #fff8d9;

            --cream: #fffaf0;

            --ink: #26342d;
            --muted: #718078;

            --white: #ffffff;
            --border: #e4e9e4;

            --shadow: 0 18px 45px rgba(52, 76, 61, .10);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: "Nunito", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 8% 10%, rgba(246, 214, 109, .30), transparent 20%),
                radial-gradient(circle at 95% 15%, rgba(242, 155, 80, .18), transparent 22%),
                linear-gradient(135deg, #fffdf7 0%, #f5f8f1 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select {
            font: inherit;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            width: 100%;
            padding: 20px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 5;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 43px;
            height: 43px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--green);
            color: white;
            font-size: 21px;
            box-shadow: 0 8px 18px rgba(79, 143, 104, .22);
        }

        .brand-name {
            font-family: "Baloo 2", cursive;
            font-size: 29px;
            font-weight: 800;
            line-height: 1;
            color: var(--green-dark);
        }

        .login-link {
            color: var(--green-dark);
            font-weight: 800;
            font-size: 14px;
        }

        .login-link span {
            color: var(--orange);
        }

        /* =========================
           PAGE
        ========================= */

        .page {
            width: min(1120px, 92%);
            margin: 20px auto 60px;
            display: grid;
            grid-template-columns: .82fr 1.18fr;
            gap: 35px;
            align-items: center;
        }

        /* =========================
           LEFT
        ========================= */

        .intro {
            padding: 25px 10px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--yellow-soft);
            color: #8c6d16;
            padding: 9px 15px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .intro h1 {
            font-family: "Baloo 2", cursive;
            font-size: clamp(42px, 5vw, 68px);
            line-height: .95;
            margin-bottom: 20px;
            color: var(--green-dark);
        }

        .intro h1 span {
            color: var(--orange);
        }

        .intro p {
            max-width: 470px;
            font-size: 16px;
            line-height: 1.8;
            color: var(--muted);
        }

        .features {
            margin-top: 28px;
            display: grid;
            gap: 13px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 700;
            color: #526158;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: white;
            box-shadow: 0 7px 20px rgba(50, 80, 60, .08);
        }

        .feature:nth-child(1) .feature-icon {
            background: var(--green-soft);
        }

        .feature:nth-child(2) .feature-icon {
            background: var(--orange-soft);
        }

        .feature:nth-child(3) .feature-icon {
            background: var(--yellow-soft);
        }

        /* =========================
           FORM CARD
        ========================= */

        .card {
            background: rgba(255, 255, 255, .95);
            border: 1px solid rgba(228, 233, 228, .9);
            border-radius: 30px;
            padding: 35px;
            box-shadow: var(--shadow);
        }

        .card-heading {
            margin-bottom: 25px;
        }

        .card-heading h2 {
            font-family: "Baloo 2", cursive;
            font-size: 32px;
            line-height: 1;
            color: var(--green-dark);
            margin-bottom: 7px;
        }

        .card-heading p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 800;
            color: #44524a;
        }

        .required {
            color: #e77d55;
        }

        .input,
        .select {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border-radius: 13px;
            border: 1.5px solid var(--border);
            outline: none;
            background: #fff;
            color: var(--ink);
            transition: .2s ease;
        }

        .input:focus,
        .select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 4px rgba(79, 143, 104, .10);
        }

        .select:disabled {
            background: #f5f6f4;
            color: #a2aaa5;
            cursor: not-allowed;
        }

        .location-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 25px 0 14px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dce4de;
        }

        .location-title-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--green-soft);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .location-title strong {
            font-size: 14px;
            color: var(--green-dark);
        }

        .grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 13px;
        }

        .hint {
            margin-top: 6px;
            color: #8b9790;
            font-size: 11px;
            line-height: 1.5;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .input {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #7e8982;
            font-size: 16px;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 20px 0;
            color: #68756d;
            font-size: 12px;
            line-height: 1.5;
        }

        .terms input {
            margin-top: 3px;
            accent-color: var(--green);
        }

        .terms a {
            color: var(--green-dark);
            font-weight: 800;
        }

        .submit-btn {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 15px;
            background: var(--green);
            color: white;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: .2s ease;
            box-shadow: 0 9px 22px rgba(79, 143, 104, .20);
        }

        .submit-btn:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            opacity: .6;
            cursor: wait;
            transform: none;
        }

        .bottom-login {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: var(--muted);
        }

        .bottom-login a {
            color: var(--green-dark);
            font-weight: 800;
        }

        .error-box {
            display: none;
            margin-bottom: 17px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fff0ed;
            border: 1px solid #f4c5ba;
            color: #a84f40;
            font-size: 13px;
            line-height: 1.5;
        }

        /* =========================
           KODE WILAYAH
        ========================= */

        .kode-info {
            display: none;
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 11px;
            background: #f2f8f3;
            color: #4c6957;
            font-size: 11px;
            line-height: 1.5;
        }

        .kode-info strong {
            color: var(--green-dark);
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {
            .page {
                grid-template-columns: 1fr;
                max-width: 650px;
                margin-top: 5px;
            }

            .intro {
                text-align: center;
                padding-bottom: 0;
            }

            .intro p {
                margin: auto;
            }

            .features {
                max-width: 430px;
                margin-left: auto;
                margin-right: auto;
                text-align: left;
            }
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 17px 5%;
            }

            .brand-name {
                font-size: 25px;
            }

            .brand-icon {
                width: 39px;
                height: 39px;
            }

            .login-link {
                font-size: 12px;
            }

            .page {
                width: 92%;
                margin-bottom: 35px;
                gap: 18px;
            }

            .intro {
                padding: 10px 2px;
            }

            .intro h1 {
                font-size: 45px;
            }

            .intro p {
                font-size: 14px;
                line-height: 1.7;
            }

            .features {
                margin-top: 20px;
            }

            .feature {
                font-size: 12px;
            }

            .feature-icon {
                width: 32px;
                height: 32px;
            }

            .card {
                padding: 23px 18px;
                border-radius: 23px;
            }

            .card-heading h2 {
                font-size: 28px;
            }

            .grid-two {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .location-title {
                margin-top: 21px;
            }

            .input,
            .select {
                height: 46px;
                font-size: 14px;
            }
        }

        @media (max-width: 360px) {
            .intro h1 {
                font-size: 39px;
            }

            .card {
                padding: 20px 15px;
            }

            .brand-name {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="brand">
        <div class="brand-icon">✦</div>
        <div class="brand-name">EduStory</div>
    </a>

    <a href="login.php" class="login-link">
        Sudah punya akun? <span>Masuk</span>
    </a>
</nav>


<main class="page">

    <!-- =========================
         INTRO
    ========================== -->

    <section class="intro">

        <div class="badge">
            ✨ Mulai dari sini
        </div>

        <h1>
            Ceritakan<br>
            <span>Sekolahmu.</span>
        </h1>

        <p>
            Buat akun EduStory dan simpan profil sekolahmu.
            Setelah terdaftar, kamu bisa membuat artikel sekolah
            dengan lebih mudah tanpa perlu mengisi data sekolah berulang kali.
        </p>

        <div class="features">

            <div class="feature">
                <div class="feature-icon">🏫</div>
                <span>Profil sekolah tersimpan di akunmu</span>
            </div>

            <div class="feature">
                <div class="feature-icon">📍</div>
                <span>Wilayah sekolah tercatat dengan kode desa</span>
            </div>

            <div class="feature">
                <div class="feature-icon">✨</div>
                <span>Siap membuat berbagai cerita sekolah</span>
            </div>

        </div>

    </section>


    <!-- =========================
         FORM DAFTAR
    ========================== -->

    <section class="card">

        <div class="card-heading">
            <h2>Buat Akun</h2>
            <p>Isi data berikut untuk mulai menggunakan EduStory.</p>
        </div>

        <div id="errorBox" class="error-box"></div>


        <form
            action="proses/proses-daftar.php"
            method="POST"
            id="registerForm"
            autocomplete="on"
        >

            <!-- =========================
                 DATA AKUN
            ========================== -->

            <div class="form-group">
                <label for="nama">
                    Nama Lengkap <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    class="input"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    required
                >
            </div>


            <div class="form-group">
                <label for="email">
                    Email <span class="required">*</span>
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="input"
                    placeholder="contoh@email.com"
                    autocomplete="email"
                    required
                >
            </div>


            <div class="form-group">
                <label for="password">
                    Password <span class="required">*</span>
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input"
                        placeholder="Minimal 6 karakter"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('password', this)"
                        aria-label="Tampilkan password"
                    >
                        👁
                    </button>

                </div>
            </div>


            <!-- =========================
                 DATA SEKOLAH
            ========================== -->

            <div class="location-title">
                <div class="location-title-icon">🏫</div>
                <strong>Data Sekolah & Wilayah</strong>
            </div>


            <div class="form-group">

                <label for="nama_sekolah">
                    Nama Sekolah <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="nama_sekolah"
                    name="nama_sekolah"
                    class="input"
                    placeholder="Contoh: TK Nusa Indah"
                    required
                >

                <div class="hint">
                    Bisa berupa nama TK, PAUD, SD, atau sekolah lainnya.
                </div>

            </div>


            <!-- =========================
                 PROVINSI
            ========================== -->

            <div class="grid-two">

                <div class="form-group">

                    <label for="provinsi">
                        Provinsi <span class="required">*</span>
                    </label>

                    <select
                        id="provinsi"
                        name="provinsi"
                        class="select"
                        required
                    >
                        <option value="">Memuat provinsi...</option>
                    </select>

                </div>


                <!-- KABUPATEN -->

                <div class="form-group">

                    <label for="kabupaten">
                        Kabupaten/Kota <span class="required">*</span>
                    </label>

                    <select
                        id="kabupaten"
                        name="kabupaten"
                        class="select"
                        disabled
                        required
                    >
                        <option value="">Pilih provinsi dahulu</option>
                    </select>

                </div>

            </div>


            <!-- =========================
                 KECAMATAN & DESA
            ========================== -->

            <div class="grid-two">

                <div class="form-group">

                    <label for="kecamatan">
                        Kecamatan <span class="required">*</span>
                    </label>

                    <select
                        id="kecamatan"
                        name="kecamatan"
                        class="select"
                        disabled
                        required
                    >
                        <option value="">Pilih kabupaten dahulu</option>
                    </select>

                </div>


                <div class="form-group">

                    <label for="desa">
                        Desa/Kelurahan <span class="required">*</span>
                    </label>

                    <select
                        id="desa"
                        name="desa"
                        class="select"
                        disabled
                        required
                    >
                        <option value="">Pilih kecamatan dahulu</option>
                    </select>

                    <!--
                        PENTING:
                        Ini menyimpan kode desa asli dari API.
                        Kode akan dibersihkan menjadi angka saja.
                        Contoh:
                        32.05.10.2003 -> 3205102003
                    -->

                    <input
                        type="hidden"
                        id="kode_desa"
                        name="kode_desa"
                        value=""
                    >

                </div>

            </div>


            <div id="kodeInfo" class="kode-info">
                Kode wilayah desa:
                <strong id="kodeInfoText">-</strong>
            </div>


            <!-- =========================
                 ALAMAT
            ========================== -->

            <div class="form-group">

                <label for="alamat">
                    Alamat Sekolah <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="alamat"
                    name="alamat"
                    class="input"
                    placeholder="Contoh: Jl. Raya Cikembulan No. 10"
                    required
                >

            </div>


            <!-- =========================
                 JENJANG
            ========================== -->

            <div class="form-group">

                <label for="jenjang">
                    Jenjang Sekolah <span class="required">*</span>
                </label>

                <select
                    id="jenjang"
                    name="jenjang"
                    class="select"
                    required
                >

                    <option value="">
                        Pilih jenjang
                    </option>

                    <option value="PAUD">
                        PAUD
                    </option>

                    <option value="TK">
                        TK
                    </option>

                    <option value="SD">
                        SD
                    </option>

                    <option value="MI">
                        MI
                    </option>

                    <option value="SMP">
                        SMP
                    </option>

                    <option value="MTs">
                        MTs
                    </option>

                    <option value="SMA">
                        SMA
                    </option>

                    <option value="SMK">
                        SMK
                    </option>

                    <option value="MA">
                        MA
                    </option>

                </select>

            </div>


            <!-- =========================
                 TERMS
            ========================== -->

            <label class="terms">

                <input
                    type="checkbox"
                    id="setuju"
                    required
                >

                <span>
                    Saya memastikan data yang saya masukkan sudah benar
                    dan dapat digunakan untuk membuat profil serta artikel
                    sekolah di EduStory.
                </span>

            </label>


            <!-- =========================
                 SUBMIT
            ========================== -->

            <button
                type="submit"
                class="submit-btn"
                id="submitBtn"
            >
                Buat Akun EduStory ✨
            </button>


            <div class="bottom-login">
                Sudah memiliki akun?
                <a href="login.php">Masuk di sini</a>
            </div>

        </form>

    </section>

</main>


<script>

    /*
    |--------------------------------------------------------------------------
    | API WILAYAH INDONESIA
    |--------------------------------------------------------------------------
    |
    | Struktur:
    | Provinsi
    |     ↓
    | Kabupaten/Kota
    |     ↓
    | Kecamatan
    |     ↓
    | Desa/Kelurahan
    |
    */

    const API =
        "https://www.emsifa.com/api-wilayah-indonesia/api";


    const provinsi  = document.getElementById("provinsi");
    const kabupaten = document.getElementById("kabupaten");
    const kecamatan = document.getElementById("kecamatan");
    const desa      = document.getElementById("desa");

    const kodeDesa  = document.getElementById("kode_desa");

    const kodeInfo     = document.getElementById("kodeInfo");
    const kodeInfoText = document.getElementById("kodeInfoText");


    /*
    |--------------------------------------------------------------------------
    | Helper
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


    function addOption(select, value, text) {

        const option = document.createElement("option");

        option.value = value;
        option.textContent = text;

        select.appendChild(option);
    }


    /*
    |--------------------------------------------------------------------------
    | Bersihkan kode wilayah
    |--------------------------------------------------------------------------
    |
    | API bisa memberikan kode dalam bentuk:
    |
    | 32.05.10.2003
    |
    | Sedangkan Klipaa membutuhkan:
    |
    | 3205102003
    |
    */

    function cleanKodeWilayah(kode) {

        if (!kode) {
            return "";
        }

        return String(kode).replace(/\D/g, "");
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil data JSON
    |--------------------------------------------------------------------------
    */

    async function getData(url) {

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error("Gagal mengambil data wilayah.");
        }

        return await response.json();
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD PROVINSI
    |--------------------------------------------------------------------------
    */

    async function loadProvinsi() {

        try {

            const data = await getData(
                `${API}/provinces.json`
            );

            resetSelect(
                provinsi,
                "Pilih provinsi"
            );

            data.forEach(item => {

                addOption(
                    provinsi,
                    item.id,
                    item.name
                );

            });

            provinsi.disabled = false;

        } catch (error) {

            provinsi.innerHTML =
                '<option value="">Gagal memuat provinsi</option>';

            console.error(error);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | PROVINSI → KABUPATEN
    |--------------------------------------------------------------------------
    */

    provinsi.addEventListener("change", async function () {

        const id = this.value;

        resetSelect(
            kabupaten,
            "Memuat kabupaten/kota..."
        );

        resetSelect(
            kecamatan,
            "Pilih kabupaten dahulu"
        );

        resetSelect(
            desa,
            "Pilih kecamatan dahulu"
        );

        kodeDesa.value = "";

        kodeInfo.style.display = "none";

        if (!id) {
            resetSelect(
                kabupaten,
                "Pilih provinsi dahulu"
            );

            return;
        }


        try {

            const data = await getData(
                `${API}/regencies/${id}.json`
            );

            resetSelect(
                kabupaten,
                "Pilih kabupaten/kota"
            );

            data.forEach(item => {

                addOption(
                    kabupaten,
                    item.id,
                    item.name
                );

            });

            kabupaten.disabled = false;

        } catch (error) {

            resetSelect(
                kabupaten,
                "Gagal memuat kabupaten/kota"
            );

            console.error(error);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | KABUPATEN → KECAMATAN
    |--------------------------------------------------------------------------
    */

    kabupaten.addEventListener("change", async function () {

        const id = this.value;

        resetSelect(
            kecamatan,
            "Memuat kecamatan..."
        );

        resetSelect(
            desa,
            "Pilih kecamatan dahulu"
        );

        kodeDesa.value = "";

        kodeInfo.style.display = "none";

        if (!id) {

            resetSelect(
                kecamatan,
                "Pilih kabupaten dahulu"
            );

            return;
        }


        try {

            const data = await getData(
                `${API}/districts/${id}.json`
            );

            resetSelect(
                kecamatan,
                "Pilih kecamatan"
            );

            data.forEach(item => {

                addOption(
                    kecamatan,
                    item.id,
                    item.name
                );

            });

            kecamatan.disabled = false;

        } catch (error) {

            resetSelect(
                kecamatan,
                "Gagal memuat kecamatan"
            );

            console.error(error);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | KECAMATAN → DESA
    |--------------------------------------------------------------------------
    */

    kecamatan.addEventListener("change", async function () {

        const id = this.value;

        resetSelect(
            desa,
            "Memuat desa/kelurahan..."
        );

        kodeDesa.value = "";

        kodeInfo.style.display = "none";


        if (!id) {

            resetSelect(
                desa,
                "Pilih kecamatan dahulu"
            );

            return;
        }


        try {

            const data = await getData(
                `${API}/villages/${id}.json`
            );

            resetSelect(
                desa,
                "Pilih desa/kelurahan"
            );


            data.forEach(item => {

                /*
                |--------------------------------------------------------------------------
                | VALUE OPTION
                |--------------------------------------------------------------------------
                |
                | Value tetap ID asli dari API.
                | Nama desa ditampilkan kepada pengguna.
                |
                */

                addOption(
                    desa,
                    item.id,
                    item.name
                );

            });


            desa.disabled = false;

        } catch (error) {

            resetSelect(
                desa,
                "Gagal memuat desa/kelurahan"
            );

            console.error(error);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | DESA DIPILIH
    |--------------------------------------------------------------------------
    */

    desa.addEventListener("change", function () {

        const selectedOption =
            this.options[this.selectedIndex];

        if (!selectedOption || !selectedOption.value) {

            kodeDesa.value = "";

            kodeInfo.style.display = "none";

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil ID desa dari option
        |--------------------------------------------------------------------------
        */

        const kodeAsli = selectedOption.value;


        /*
        |--------------------------------------------------------------------------
        | Bersihkan titik / karakter lain
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | 32.05.10.2003
        |
        | menjadi:
        |
        | 3205102003
        |
        */

        const kodeBersih =
            cleanKodeWilayah(kodeAsli);


        /*
        |--------------------------------------------------------------------------
        | Simpan ke hidden input
        |--------------------------------------------------------------------------
        */

        kodeDesa.value = kodeBersih;


        /*
        |--------------------------------------------------------------------------
        | Tampilkan untuk pengecekan pengguna
        |--------------------------------------------------------------------------
        */

        kodeInfoText.textContent =
            kodeBersih || "-";

        kodeInfo.style.display =
            kodeBersih ? "block" : "none";

    });


    /*
    |--------------------------------------------------------------------------
    | TOGGLE PASSWORD
    |--------------------------------------------------------------------------
    */

    function togglePassword(id, button) {

        const input =
            document.getElementById(id);

        if (input.type === "password") {

            input.type = "text";

            button.textContent = "🙈";

            button.setAttribute(
                "aria-label",
                "Sembunyikan password"
            );

        } else {

            input.type = "password";

            button.textContent = "👁";

            button.setAttribute(
                "aria-label",
                "Tampilkan password"
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI SEBELUM SUBMIT
    |--------------------------------------------------------------------------
    */

    document
        .getElementById("registerForm")
        .addEventListener("submit", function (event) {

            const errorBox =
                document.getElementById("errorBox");

            errorBox.style.display = "none";
            errorBox.textContent = "";


            /*
            |--------------------------------------------------------------------------
            | Pastikan kode desa ada
            |--------------------------------------------------------------------------
            */

            if (!kodeDesa.value) {

                event.preventDefault();

                errorBox.textContent =
                    "Silakan pilih Desa/Kelurahan terlebih dahulu.";

                errorBox.style.display =
                    "block";

                desa.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Pastikan kode hanya angka
            |--------------------------------------------------------------------------
            */

            if (!/^\d+$/.test(kodeDesa.value)) {

                event.preventDefault();

                errorBox.textContent =
                    "Kode desa tidak valid. Silakan pilih kembali Desa/Kelurahan.";

                errorBox.style.display =
                    "block";

                desa.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            const password =
                document.getElementById("password").value;

            if (password.length < 6) {

                event.preventDefault();

                errorBox.textContent =
                    "Password minimal 6 karakter.";

                errorBox.style.display =
                    "block";

                document
                    .getElementById("password")
                    .focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Loading button
            |--------------------------------------------------------------------------
            */

            const submitBtn =
                document.getElementById("submitBtn");

            submitBtn.disabled = true;

            submitBtn.textContent =
                "Membuat akun...";

        });


    /*
    |--------------------------------------------------------------------------
    | MULAI LOAD PROVINSI
    |--------------------------------------------------------------------------
    */

    loadProvinsi();

</script>

</body>
</html>
```
