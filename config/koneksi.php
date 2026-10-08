<?php

/*
|--------------------------------------------------------------------------
| Koneksi database
|--------------------------------------------------------------------------
|
| Password TIDAK ditulis di sini. Isi lewat environment variable
| DB_PASS (Vercel) atau lewat config/lokal.php (lihat lokal.example.php).
|
*/

require_once __DIR__ . '/env.php';

// Tidak ada nilai bawaan untuk host/user/nama database. Isi lewat
// config/lokal.php (komputer sendiri atau hosting) atau environment
// variable (Vercel). Jangan tulis nilai sungguhan di sini; file ini ikut ke GitHub.
$host     = envNilai('DB_HOST');
$user     = envNilai('DB_USER');
$password = envNilai('DB_PASS');
$database = envNilai('DB_NAME');
$port     = (int) envNilai('DB_PORT', '3306');

if ($host === '' || $user === '' || $database === '') {

    error_log('Koneksi database belum diatur: DB_HOST/DB_USER/DB_NAME kosong.');

    http_response_code(500);
    die('Database belum diatur. Isi DB_HOST, DB_USER, DB_PASS, dan DB_NAME di config/lokal.php (lihat lokal.example.php) atau di Environment Variables hosting.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $koneksi = new mysqli($host, $user, $password, $database, $port);
    $koneksi->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log("Koneksi database gagal: " . $e->getMessage());

    http_response_code(500);
    die("Koneksi database gagal. Coba lagi beberapa saat.");
}
