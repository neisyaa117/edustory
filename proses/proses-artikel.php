<?php

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/gambar.php';

$userId = wajibLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$slug = (string) ($_POST['jenis'] ?? '');
$daftarJenis = daftarJenisArtikel();

if (!isset($daftarJenis[$slug])) {
    header('Location: ../dashboard.php');
    exit;
}

$jenis = $daftarJenis[$slug];
$kembali = '../buat.php?jenis=' . urlencode($slug);


function balikDenganGalat(string $pesan, string $kembali, string $slug, array $lama = []): void
{
    $_SESSION['form_galat'] = $pesan;
    $_SESSION['form_lama'][$slug] = $lama;

    header('Location: ' . $kembali);
    exit;
}


/*
|--------------------------------------------------------------------------
| Cek permintaan
|--------------------------------------------------------------------------
|
| Kalau total kiriman melebihi post_max_size, PHP mengosongkan $_POST.
|
*/

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    balikDenganGalat(
        'Data tidak terkirim. Ukuran foto kemungkinan terlalu besar; coba pilih foto yang lebih kecil.',
        $kembali,
        $slug
    );
}

if (!csrfValid($_POST['csrf'] ?? null)) {
    balikDenganGalat('Halaman sudah kedaluwarsa. Isi ulang dan kirim lagi.', $kembali, $slug);
}


/*
|--------------------------------------------------------------------------
| Baca dan periksa isian
|--------------------------------------------------------------------------
*/

$kiriman = $_POST['isi'] ?? [];
$kiriman = is_array($kiriman) ? $kiriman : [];

$nilai   = [];
$lama    = [];
$kosong  = [];
$rusak   = [];

foreach (semuaField($jenis) as $field) {

    $nama  = $field['nama'];
    $tipe  = $field['tipe'];
    $wajib = !empty($field['wajib']);
    $mentah = $kiriman[$nama] ?? null;

    if ($tipe === 'banyak') {

        $pilihan = is_array($mentah) ? $mentah : [];
        $opsi    = array_keys(opsiField($field));
        $bersih  = array_values(array_intersect($opsi, array_map('strval', $pilihan)));

        $lama[$nama]  = $bersih;
        $nilai[$nama] = $bersih;

        if ($wajib && !$bersih) {
            $kosong[] = $field['label'];
        }

        continue;
    }

    $teks = is_string($mentah) ? trim($mentah) : '';
    $teks = str_replace("\r\n", "\n", $teks);

    if ($tipe === 'teks') {
        $teks = mb_substr(preg_replace('/\s+/u', ' ', $teks), 0, 200);
    } elseif ($tipe === 'panjang') {
        $teks = mb_substr($teks, 0, 2000);
    } elseif ($tipe === 'pilihan' || $tipe === 'menu') {
        if ($teks !== '' && !array_key_exists($teks, opsiField($field))) {
            $rusak[] = $field['label'];
            $teks = '';
        }
    } elseif ($tipe === 'tanggal') {
        $d = DateTime::createFromFormat('Y-m-d', $teks);
        if (!$d || $d->format('Y-m-d') !== $teks) {
            $teks = '';
        }
    }

    $lama[$nama]  = $teks;
    $nilai[$nama] = $teks;

    if ($wajib && $teks === '') {
        $kosong[] = $field['label'];
    }
}

if ($kosong) {
    balikDenganGalat(
        'Bagian ini belum diisi: ' . implode(', ', $kosong) . '.',
        $kembali,
        $slug,
        $lama
    );
}


/*
|--------------------------------------------------------------------------
| Batas per hari
|--------------------------------------------------------------------------
*/

$sekolah = ambilSekolah($koneksi, $userId);

if (!$sekolah) {
    header('Location: ../profil-sekolah.php');
    exit;
}

$batas = max(1, (int) envNilai('BATAS_ARTIKEL_HARIAN', '10'));

$stmt = $koneksi->prepare(
    "SELECT COUNT(*) AS jumlah FROM artikel
     WHERE user_id = ? AND dibuat_pada >= CURDATE()"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$hariIni = (int) $stmt->get_result()->fetch_assoc()['jumlah'];
$stmt->close();

if ($hariIni >= $batas) {
    balikDenganGalat(
        "Batas $batas artikel per hari sudah tercapai. Coba lagi besok.",
        $kembali,
        $slug,
        $lama
    );
}


/*
|--------------------------------------------------------------------------
| Simpan
|--------------------------------------------------------------------------
*/

$json = json_encode(['nilai' => $nilai], JSON_UNESCAPED_UNICODE);

$sekolahId = (int) ($sekolah['id'] ?? 0);
$namaJenis = $jenis['nama'];

$stmt = $koneksi->prepare(
    "INSERT INTO artikel (user_id, sekolah_id, jenis, jenis_nama, input_json, status)
     VALUES (?, ?, ?, ?, ?, 'baru')"
);
$stmt->bind_param("iisss", $userId, $sekolahId, $slug, $namaJenis, $json);
$stmt->execute();

$artikelId = (int) $koneksi->insert_id;
$stmt->close();

$foto = simpanFotoUnggahan($koneksi, $artikelId, $_FILES['foto'] ?? null);

if ($foto['masalah']) {
    $_SESSION['info_artikel'][$artikelId] = implode(' ', $foto['masalah']);
}

unset($_SESSION['form_lama'][$slug]);

header('Location: ../artikel.php?id=' . $artikelId);
exit;
