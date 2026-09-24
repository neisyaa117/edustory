<?php

session_start();

require_once "../config/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}


$user_id = $_SESSION['user_id'];

$nama_sekolah = trim($_POST['nama_sekolah'] ?? '');
$provinsi     = trim($_POST['provinsi'] ?? '');
$kabupaten    = trim($_POST['kabupaten'] ?? '');
$kecamatan    = trim($_POST['kecamatan'] ?? '');
$desa         = trim($_POST['desa'] ?? '');
$alamat       = trim($_POST['alamat'] ?? '');
$jenjang      = trim($_POST['jenjang'] ?? '');


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


if (
    empty($nama_sekolah) ||
    empty($provinsi) ||
    empty($kabupaten) ||
    empty($kecamatan) ||
    empty($desa) ||
    empty($jenjang)
) {

    echo "<script>
        alert('Data sekolah belum lengkap.');
        history.back();
    </script>";

    exit;
}


/* Cek apakah profil sudah ada */

$cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM sekolah WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $cek,
    "i",
    $user_id
);

mysqli_stmt_execute($cek);

$hasil = mysqli_stmt_get_result($cek);


/* Kalau sudah ada -> UPDATE */

if (mysqli_num_rows($hasil) > 0) {

    $data = mysqli_fetch_assoc($hasil);

    $id_sekolah = $data['id'];


    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE sekolah SET
        nama_sekolah = ?,
        provinsi = ?,
        kabupaten = ?,
        kecamatan = ?,
        desa = ?,
        alamat = ?,
        jenjang = ?,
        kode_provinsi = ?,
        kode_kabupaten = ?,
        kode_kecamatan = ?,
        kode_desa = ?
        WHERE id = ?"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssi",
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
        $kode_desa,
        $id_sekolah
    );


    mysqli_stmt_execute($stmt);

}


/* Kalau belum ada -> INSERT */

else {

    $stmt = mysqli_prepare(
        $koneksi,
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


    mysqli_stmt_bind_param(
        $stmt,
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


    mysqli_stmt_execute($stmt);

}


header("Location: ../dashboard.php");

exit;
