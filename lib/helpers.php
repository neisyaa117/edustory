<?php

require_once __DIR__ . '/../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

/* Berapa kali satu artikel boleh dicoba ditulis (termasuk yang gagal). */
const MAKS_PERCOBAAN_TULIS = 5;


/*
|--------------------------------------------------------------------------
| Umum
|--------------------------------------------------------------------------
*/

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mulaiSesi(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function kirimJson(array $data, int $kode = 200): void
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}


/*
|--------------------------------------------------------------------------
| Login dan CSRF
|--------------------------------------------------------------------------
*/

function wajibLogin(string $keLogin = 'login.php'): int
{
    mulaiSesi();

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $keLogin);
        exit;
    }

    return (int) $_SESSION['user_id'];
}

function csrfToken(): string
{
    mulaiSesi();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['csrf'];
}

function csrfValid($token): bool
{
    mulaiSesi();

    return !empty($_SESSION['csrf'])
        && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

function ambilUser(mysqli $k, int $userId): ?array
{
    $stmt = $k->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $baris ?: null;
}

function ambilSekolah(mysqli $k, int $userId): ?array
{
    $stmt = $k->prepare("SELECT * FROM sekolah WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $baris ?: null;
}

function daftarJenisArtikel(): array
{
    static $daftar = null;

    if ($daftar === null) {
        $daftar = require __DIR__ . '/../config/jenis-artikel.php';
    }

    return $daftar;
}

/* Semua isian pada satu jenis artikel, dalam satu daftar rata. */
function semuaField(array $jenis): array
{
    $hasil = [];

    foreach ($jenis['bagian'] as $bagian) {
        foreach ($bagian['fields'] as $field) {
            $hasil[] = $field;
        }
    }

    return $hasil;
}

/* Opsi bisa berupa daftar biasa atau pasangan nilai => tampilan. */
function opsiField(array $field): array
{
    $opsi = $field['opsi'] ?? [];
    $hasil = [];

    foreach ($opsi as $kunci => $tampil) {
        if (is_int($kunci)) {
            $hasil[$tampil] = $tampil;
        } else {
            $hasil[$kunci] = $tampil;
        }
    }

    return $hasil;
}


/*
|--------------------------------------------------------------------------
| Klipaa
|--------------------------------------------------------------------------
|
| Isi KLIPAA_URL_ARTIKEL (environment variable atau config/lokal.php).
| Tulis {kode_desa} pada bagian yang harus diganti kode desa.
|
*/

function klipaaLinkArtikel(?string $kodeDesa): ?string
{
    $templat = envNilai('KLIPAA_URL_ARTIKEL');

    if ($templat === '' || !$kodeDesa) {
        return null;
    }

    return str_replace('{kode_desa}', rawurlencode($kodeDesa), $templat);
}


/*
|--------------------------------------------------------------------------
| Tampilan
|--------------------------------------------------------------------------
*/

function tanggalIndo(?string $waktu): string
{
    if (!$waktu) {
        return '';
    }

    $t = strtotime($waktu);

    if (!$t) {
        return '';
    }

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

/* Nama wilayah dari API berupa huruf kapital semua. Rapikan untuk tampilan. */
function rapikanWilayah(?string $nama): string
{
    $nama = trim((string) $nama);

    if ($nama === '') {
        return '';
    }

    $hasil = mb_convert_case(mb_strtolower($nama, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

    return preg_replace('/\bDki\b/u', 'DKI', $hasil);
}

function labelStatus(string $status): array
{
    switch ($status) {
        case 'selesai':
            return ['Selesai', 'status-selesai'];
        case 'menulis':
            return ['Sedang ditulis', 'status-proses'];
        case 'gagal':
            return ['Belum berhasil', 'status-gagal'];
        default:
            return ['Menunggu', 'status-proses'];
    }
}

/* Buang tanda markdown yang kadang terselip dari AI. */
function bersihkanTanda(string $teks): string
{
    return trim(preg_replace('/[*_`]+/u', '', $teks));
}

/*
| Ubah teks artikel (paragraf, subjudul "## ", daftar "- " atau "1. ")
| menjadi HTML yang aman.
*/
function renderIsiArtikel(string $teks): string
{
    $teks = str_replace(["\r\n", "\r"], "\n", trim($teks));

    // Subjudul selalu berdiri sendiri sebagai blok.
    $teks = preg_replace('/^(#{1,4}[ \t]+.+)$/m', "\n\n$1\n\n", $teks);

    $blok = preg_split('/\n{2,}/', $teks);
    $html = '';

    foreach ($blok as $b) {

        $b = trim($b);

        if ($b === '') {
            continue;
        }

        $baris = array_values(array_filter(
            array_map('trim', explode("\n", $b)),
            fn($x) => $x !== ''
        ));

        if (preg_match('/^#{1,4}[ \t]+(.+)$/u', $baris[0], $m)) {
            $html .= '<h2>' . e(bersihkanTanda($m[1])) . '</h2>';
            continue;
        }

        $semuaBullet = true;
        $semuaAngka  = true;

        foreach ($baris as $x) {
            if (!preg_match('/^[-•]\s+/u', $x)) {
                $semuaBullet = false;
            }
            if (!preg_match('/^\d+[.)]\s+/u', $x)) {
                $semuaAngka = false;
            }
        }

        if ($semuaBullet || $semuaAngka) {

            $tag = $semuaAngka ? 'ol' : 'ul';
            $html .= "<$tag>";

            foreach ($baris as $x) {
                $isi = preg_replace('/^([-•]|\d+[.)])\s+/u', '', $x);
                $html .= '<li>' . e(bersihkanTanda($isi)) . '</li>';
            }

            $html .= "</$tag>";
            continue;
        }

        $html .= '<p>' . e(bersihkanTanda(implode(' ', $baris))) . '</p>';
    }

    return $html;
}


/*
| Pesan untuk pengguna. Detail teknis hanya masuk log, bukan layar.
*/
function pesanGalatPengguna(?string $teknis): string
{
    $teknis = (string) $teknis;

    if (stripos($teknis, 'API_KEY belum diisi') !== false) {
        $pesan = 'Penulis artikel belum disiapkan. Hubungi pengelola aplikasi.';
    } elseif (stripos($teknis, 'HTTP 429') !== false || stripos($teknis, 'quota') !== false) {
        $pesan = 'Penulis artikel sedang sibuk. Tunggu semenit, lalu coba lagi.';
    } else {
        $pesan = 'Artikel belum berhasil ditulis. Isianmu aman tersimpan, coba lagi sebentar lagi.';
    }

    // Mode pengembang (komputer sendiri): tampilkan penyebab sebenarnya.
    // Aktifkan dengan APP_DEBUG = 1 di config/lokal.php. Jangan dipakai di situs umum.
    if ($teknis !== '' && envNilai('APP_DEBUG') === '1') {
        $pesan .= ' [Detail teknis: ' . $teknis . ']';
    }

    return $pesan;
}

/*
| Tampilkan kode wilayah dengan titik seperti penulisan Kemendagri.
| Disimpan tanpa titik (3205102003), ditampilkan 32.05.10.2003.
*/
function formatKodeWilayah(?string $kode): string
{
    $kode = preg_replace('/[^0-9]/', '', (string) $kode);

    if (strlen($kode) === 10) {
        return substr($kode, 0, 2) . '.' . substr($kode, 2, 2) . '.' . substr($kode, 4, 2) . '.' . substr($kode, 6, 4);
    }

    return $kode;
}
