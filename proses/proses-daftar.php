<?php

session_start();

require_once "../config/koneksi.php";


/*
|--------------------------------------------------------------------------
| AMBIL DATA FORM
|--------------------------------------------------------------------------
*/

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$daftarPeran = ['Guru', 'Kepala Sekolah', 'Tenaga Kependidikan', 'Siswa', 'Alumni', 'Orang Tua / Wali', 'Lainnya'];
$peran = trim($_POST['peran'] ?? '');
if (!in_array($peran, $daftarPeran, true)) {
    $peran = 'Guru';
}

$provinsi = trim($_POST['provinsi'] ?? '');
$kabupaten = trim($_POST['kabupaten'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$desa = trim($_POST['desa'] ?? '');
$nama_sekolah = trim($_POST['nama_sekolah'] ?? '');

/* Kode wilayah dari API (dipakai untuk menyambung ke klipaa). */
function kodeWilayah($nilai)
{
    $nilai = preg_replace('/[^0-9]/', '', (string) $nilai);
    return $nilai !== '' ? $nilai : null;
}

$kode_provinsi  = kodeWilayah($_POST['kode_provinsi'] ?? '');
$kode_kabupaten = kodeWilayah($_POST['kode_kabupaten'] ?? '');
$kode_kecamatan = kodeWilayah($_POST['kode_kecamatan'] ?? '');
$kode_desa      = kodeWilayah($_POST['kode_desa'] ?? '');


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if (
    $nama === '' ||
    $email === '' ||
    $password === '' ||
    $provinsi === '' ||
    $kabupaten === '' ||
    $kecamatan === '' ||
    $desa === '' ||
    $nama_sekolah === ''
) {

    $_SESSION['pesan'] = "Semua data wajib diisi.";

    header("Location: ../daftar.php");
    exit;
}


if (strlen($password) < 6) {

    $_SESSION['pesan'] = "Password minimal 6 karakter.";

    header("Location: ../daftar.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CEK EMAIL
|--------------------------------------------------------------------------
*/

$cek = $koneksi->prepare(
    "SELECT id FROM users WHERE email = ?"
);

if (!$cek) {

    $_SESSION['pesan'] =
        "Terjadi kesalahan pada database: " .
        $koneksi->error;

    header("Location: ../daftar.php");
    exit;
}


$cek->bind_param("s", $email);
$cek->execute();

$hasil = $cek->get_result();


if ($hasil->num_rows > 0) {

    $_SESSION['pesan'] =
        "Email tersebut sudah terdaftar.";

    header("Location: ../daftar.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| MULAI TRANSAKSI
|--------------------------------------------------------------------------
|
| Kita menyimpan:
|
| 1. Data akun → users
| 2. Data sekolah → sekolah
|
| Kalau salah satunya gagal, semuanya dibatalkan.
|
*/

$koneksi->begin_transaction();


try {


    /*
    |--------------------------------------------------------------------------
    | SIMPAN USER
    |--------------------------------------------------------------------------
    */

    /* Kolom "peran" ditambahkan belakangan; pakai bila sudah ada di database. */
    $adaPeran = $koneksi->query("SHOW COLUMNS FROM users LIKE 'peran'");
    $adaPeran = $adaPeran && $adaPeran->num_rows > 0;

    $stmtUser = $koneksi->prepare(
        $adaPeran
            ? "INSERT INTO users (nama, peran, email, password) VALUES (?, ?, ?, ?)"
            : "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)"
    );


    if (!$stmtUser) {
        throw new Exception(
            "Query users gagal: " . $koneksi->error
        );
    }


    if ($adaPeran) {
        $stmtUser->bind_param("ssss", $nama, $peran, $email, $password_hash);
    } else {
        $stmtUser->bind_param("sss", $nama, $email, $password_hash);
    }


    if (!$stmtUser->execute()) {
        throw new Exception(
            "Gagal menyimpan akun: " . $stmtUser->error
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ID USER BARU
    |--------------------------------------------------------------------------
    */

    $user_id = $koneksi->insert_id;


    /*
    |--------------------------------------------------------------------------
    | SIMPAN DATA SEKOLAH
    |--------------------------------------------------------------------------
    |
    | alamat dikosongkan dulu karena belum ada di form.
    | jenjang juga dikosongkan dulu karena belum ada di form.
    |
    */

    $alamat = null;
    $jenjang = '';


    $stmtSekolah = $koneksi->prepare(
        "INSERT INTO sekolah
        (
            user_id,
            nama_sekolah,
            provinsi,
            kabupaten,
            kecamatan,
            desa,
            alamat,
            jenjang,
            kode_provinsi,
            kode_kabupaten,
            kode_kecamatan,
            kode_desa
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );


    if (!$stmtSekolah) {
        throw new Exception(
            "Query sekolah gagal: " . $koneksi->error
        );
    }


    $stmtSekolah->bind_param(
        "isssssssssss",
        $user_id,
        $nama_sekolah,
        $provinsi,
        $kabupaten,
        $kecamatan,
        $desa,
        $alamat,
        $jenjang,
        $kode_provinsi,
        $kode_kabupaten,
        $kode_kecamatan,
        $kode_desa
    );


    if (!$stmtSekolah->execute()) {
        throw new Exception(
            "Gagal menyimpan data sekolah: " .
            $stmtSekolah->error
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEMUA BERHASIL
    |--------------------------------------------------------------------------
    */

    $koneksi->commit();


    /*
    |--------------------------------------------------------------------------
    | LOGIN OTOMATIS
    |--------------------------------------------------------------------------
    */

    $_SESSION['user_id'] = $user_id;
    $_SESSION['nama'] = $nama;
    $_SESSION['nama_sekolah'] = $nama_sekolah;
    $_SESSION['provinsi'] = $provinsi;
    $_SESSION['kabupaten'] = $kabupaten;
    $_SESSION['kecamatan'] = $kecamatan;
    $_SESSION['desa'] = $desa;

    session_regenerate_id(true);


    /*
    |--------------------------------------------------------------------------
    | MASUK DASHBOARD
    |--------------------------------------------------------------------------
    */

    header("Location: ../dashboard.php");
    exit;


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | JIKA GAGAL → BATALKAN SEMUA
    |--------------------------------------------------------------------------
    */

    $koneksi->rollback();


    error_log("Pendaftaran gagal: " . $e->getMessage());

    $_SESSION['pesan'] =
        "Pendaftaran belum berhasil. Coba lagi sebentar lagi.";


    header("Location: ../daftar.php");
    exit;
}