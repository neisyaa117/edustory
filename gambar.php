<?php

/*
| Menampilkan foto artikel dari database.
| Alamat memakai token acak, contoh: gambar.php?t=abc123...
| Tanpa sesi login supaya klipaa nantinya bisa mengambil gambar ini.
*/

require_once __DIR__ . '/config/koneksi.php';

$token = (string) ($_GET['t'] ?? '');

if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(404);
    exit;
}

$stmt = $koneksi->prepare("SELECT mime, data FROM artikel_gambar WHERE token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$foto) {
    http_response_code(404);
    exit;
}

$ekstensi = $foto['mime'] === 'image/png' ? 'png' : ($foto['mime'] === 'image/webp' ? 'webp' : 'jpg');

header('Content-Type: ' . $foto['mime']);
header('Content-Length: ' . strlen($foto['data']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=604800, immutable');

if (isset($_GET['unduh'])) {
    header('Content-Disposition: attachment; filename="foto-' . substr($token, 0, 8) . '.' . $ekstensi . '"');
}

echo $foto['data'];
