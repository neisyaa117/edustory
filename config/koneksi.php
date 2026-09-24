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

$host     = envNilai('DB_HOST', 'sql301.infinityfree.com');
$user     = envNilai('DB_USER', 'if0_42968950');
$password = envNilai('DB_PASS');
$database = envNilai('DB_NAME', 'if0_42968950_edustory');
$port     = (int) envNilai('DB_PORT', '3306');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $koneksi = new mysqli($host, $user, $password, $database, $port);
    $koneksi->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log("Koneksi database gagal: " . $e->getMessage());

    http_response_code(500);
    die("Koneksi database gagal. Coba lagi beberapa saat.");
}
