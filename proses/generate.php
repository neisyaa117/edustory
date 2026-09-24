<?php

/*
|--------------------------------------------------------------------------
| Menulis artikel dengan AI
|--------------------------------------------------------------------------
|
| POST  id, csrf, ulang(opsional)  -> mulai menulis, balasan JSON
| GET   cek=1, id                  -> tanyakan status saja
|
| Proses ini bisa berjalan puluhan detik. Sesi ditutup lebih dulu
| supaya halaman lain milik pengguna yang sama tidak ikut menunggu.
|
*/

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/ai.php';

const BATAS_MENULIS_MENIT = 4;   // setelah ini status "menulis" dianggap macet

mulaiSesi();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$csrf   = $_SESSION['csrf'] ?? '';

session_write_close();

if ($userId === 0) {
    kirimJson(['ok' => false, 'pesan' => 'Sesi habis. Silakan masuk lagi.'], 401);
}

$id = (int) ($_REQUEST['id'] ?? 0);


function ambilArtikelMilik(mysqli $k, int $id, int $userId): ?array
{
    $stmt = $k->prepare("SELECT * FROM artikel WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $baris ?: null;
}

$artikel = ambilArtikelMilik($koneksi, $id, $userId);

if (!$artikel) {
    kirimJson(['ok' => false, 'pesan' => 'Artikel tidak ditemukan.'], 404);
}


/* ---------- Cek status saja ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    kirimJson([
        'ok'     => true,
        'status' => $artikel['status'],
        'pesan'  => $artikel['status'] === 'gagal' ? pesanGalatPengguna($artikel['pesan_error']) : '',
    ]);
}


/* ---------- Mulai menulis ---------- */

if ($csrf === '' || !is_string($_POST['csrf'] ?? null) || !hash_equals($csrf, $_POST['csrf'])) {
    kirimJson(['ok' => false, 'pesan' => 'Halaman sudah kedaluwarsa. Muat ulang halaman.'], 419);
}

$ulang = !empty($_POST['ulang']);

if ($artikel['status'] === 'selesai' && !$ulang) {
    kirimJson(['ok' => true, 'status' => 'selesai']);
}

if ((int) $artikel['jumlah_tulis'] >= MAKS_PERCOBAAN_TULIS) {
    kirimJson([
        'ok' => false,
        'status' => $artikel['status'],
        'pesan' => 'Artikel ini sudah dicoba ditulis beberapa kali. Buat artikel baru bila perlu.',
    ]);
}


/*
| Klaim pekerjaan. Hanya satu permintaan yang boleh menang, sehingga
| klik ganda atau dua tab tidak memanggil AI dua kali.
*/

$menit = BATAS_MENULIS_MENIT;
$ulangInt = $ulang ? 1 : 0;

$stmt = $koneksi->prepare(
    "UPDATE artikel
     SET status = 'menulis', jumlah_tulis = jumlah_tulis + 1, diperbarui_pada = NOW()
     WHERE id = ? AND user_id = ?
       AND (
            status IN ('baru', 'gagal')
            OR (status = 'menulis' AND diperbarui_pada < (NOW() - INTERVAL $menit MINUTE))
            OR (status = 'selesai' AND ? = 1)
       )"
);
$stmt->bind_param("iii", $id, $userId, $ulangInt);
$stmt->execute();
$menang = $stmt->affected_rows === 1;
$stmt->close();

if (!$menang) {
    $sekarang = ambilArtikelMilik($koneksi, $id, $userId);
    kirimJson(['ok' => true, 'status' => $sekarang['status'] ?? 'menulis']);
}

ignore_user_abort(true);
set_time_limit(150);

$sekolah = ambilSekolah($koneksi, $userId) ?: [];
$daftarJenis = daftarJenisArtikel();
$jenis = $daftarJenis[$artikel['jenis']] ?? null;

try {

    if (!$jenis) {
        throw new RuntimeException('Jenis artikel tidak dikenal: ' . $artikel['jenis']);
    }

    $data  = json_decode($artikel['input_json'], true);
    $nilai = is_array($data['nilai'] ?? null) ? $data['nilai'] : [];

    [$sistem, $pengguna] = susunPrompt($jenis, $sekolah, $nilai);

    $balasan = panggilAI($sistem, $pengguna);

    [$judul, $isi] = uraiHasilAI($balasan);

    $stmt = $koneksi->prepare(
        "UPDATE artikel
         SET judul = ?, isi = ?, status = 'selesai', pesan_error = NULL,
             diperbarui_pada = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param("ssi", $judul, $isi, $id);
    $stmt->execute();
    $stmt->close();

    kirimJson(['ok' => true, 'status' => 'selesai']);

} catch (Throwable $e) {

    error_log('Gagal menulis artikel #' . $id . ': ' . $e->getMessage());

    // Bila artikel lama masih ada (penulisan ulang), tetap tampilkan.
    $kembaliKe = !empty($artikel['isi']) ? 'selesai' : 'gagal';
    $pesanSimpan = mb_substr($e->getMessage(), 0, 480);

    $stmt = $koneksi->prepare(
        "UPDATE artikel
         SET status = ?, pesan_error = ?, diperbarui_pada = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param("ssi", $kembaliKe, $pesanSimpan, $id);
    $stmt->execute();
    $stmt->close();

    kirimJson([
        'ok'     => false,
        'status' => $kembaliKe,
        'pesan'  => pesanGalatPengguna($pesanSimpan),
    ]);
}
