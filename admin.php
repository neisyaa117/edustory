<?php

/*
|--------------------------------------------------------------------------
| Halaman admin
|--------------------------------------------------------------------------
|
| Tiga tab terpisah:
|   - Data akun      : akun guru + data sekolahnya (hanya dilihat)
|   - Hasil artikel  : daftar artikel, penanda "sudah dikirim ke klipaa",
|                      lihat isi, dan hapus artikel
|   - Jenis artikel  : tambah, ubah, hapus jenis artikel yang bisa dipilih
|                      guru di beranda (lihat lib/jenis-db.php)
|
| Dilindungi kata sandi TERPISAH dari akun guru (bukan email/password di
| tabel users), supaya tidak perlu login sebagai guru untuk membukanya.
| Isi kata sandinya lewat ADMIN_PASSWORD di config/lokal.php, atau lewat
| Environment Variable di hosting. Kalau belum diisi, halaman ini menolak
| dibuka sama sekali -- jadi aman walau linknya ada di footer halaman login.
*/

require_once __DIR__ . '/lib/helpers.php';

mulaiSesi();

$kataSandiAdmin = envNilai('ADMIN_PASSWORD');

function halamanSederhana(string $judul, string $isiHtml): void
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($judul) ?> - EduStory</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="auth-page">
        <main class="auth-wrapper">
            <div class="auth-box">
                <?= $isiHtml ?>
            </div>
        </main>
    </body>
    </html>
    <?php
}

// Belum diatur sama sekali -> jangan izinkan siapa pun masuk, termasuk tanpa kata sandi.
if ($kataSandiAdmin === '') {
    http_response_code(503);
    halamanSederhana('Admin belum diatur', '
        <div class="auth-heading">
            <h1>Halaman admin belum diatur</h1>
            <p>Isi <code>ADMIN_PASSWORD</code> di <code>config/lokal.php</code> (komputer sendiri / hosting) atau Environment Variables (Vercel) untuk mengaktifkan halaman ini.</p>
        </div>
        <a href="index.php" class="btn-garis btn-blok">Kembali ke beranda</a>
    ');
    exit;
}

// Logout admin (tidak menyentuh sesi login guru).
if (isset($_GET['keluar'])) {
    unset($_SESSION['admin_masuk']);
    header('Location: admin.php');
    exit;
}

$sudahMasuk = !empty($_SESSION['admin_masuk']);

// Proses kirim kata sandi.
if (!$sudahMasuk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kata_sandi'])) {

    if (hash_equals($kataSandiAdmin, (string) $_POST['kata_sandi'])) {
        $_SESSION['admin_masuk'] = true;
        header('Location: admin.php');
        exit;
    }

    $pesanSalah = 'Kata sandi salah.';
}

// Tab yang sedang dibuka. Link lama (?hal=...) otomatis membuka tab artikel.
$tab = (string) ($_GET['tab'] ?? (isset($_GET['hal']) ? 'artikel' : 'akun'));
if (!in_array($tab, ['akun', 'artikel', 'jenis'], true)) {
    $tab = 'akun';
}

function urlAdmin(array $q = []): string
{
    $q = array_filter($q, function ($v) {
        return $v !== null && $v !== '';
    });

    return 'admin.php' . ($q ? '?' . http_build_query($q) : '');
}

function adminPesan(string $jenis, string $teks): void
{
    $_SESSION['admin_pesan'] = [$jenis, $teks];
}

// Tandai/batalkan status kirim-klipaa (hanya setelah masuk).
if ($sudahMasuk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tandai_klipaa'])
    && csrfValid($_POST['csrf'] ?? '')) {

    $artikelId   = (int) ($_POST['artikel_id'] ?? 0);
    $sudahKirim  = $_POST['tandai_klipaa'] === '1';

    if ($artikelId > 0) {
        setKlipaaArtikel($koneksi, $artikelId, $sudahKirim);
    }

    header('Location: ' . urlAdmin(['tab' => 'artikel', 'hal' => isset($_GET['hal']) ? (int) $_GET['hal'] : null]));
    exit;
}

// Hapus artikel (hanya setelah masuk, dan hanya lewat form -- bukan link biasa).
if ($sudahMasuk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_artikel'])
    && csrfValid($_POST['csrf'] ?? '')) {

    $artikelId = (int) ($_POST['artikel_id'] ?? 0);

    if ($artikelId > 0) {
        hapusArtikel($koneksi, $artikelId);
    }

    header('Location: ' . urlAdmin(['tab' => 'artikel', 'hal' => isset($_GET['hal']) ? (int) $_GET['hal'] : null]));
    exit;
}


/* ------------------------------------------------------------------ */
/* Kelola jenis artikel (tambah, ubah, hapus) -- hanya setelah masuk. */
/* ------------------------------------------------------------------ */

require_once __DIR__ . '/lib/jenis-db.php';

// Buat tabel jenis artikel kalau belum ada (hanya untuk admin yang sudah masuk).
// Harus sebelum daftarJenisArtikel() dipanggil, karena hasilnya di-cache per permintaan.
if ($sudahMasuk) {
    jenisTabelSiap($koneksi);
}

$formJenis  = null;   // isi editor (kalau sedang menambah/mengubah)
$galatJenis = [];
$slugEdit   = '';     // '' = jenis baru

if ($sudahMasuk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_jenis'])) {

    $kembaliJenis = urlAdmin(['tab' => 'jenis']);

    if (!csrfValid($_POST['csrf'] ?? '')) {
        adminPesan('galat', 'Halaman sudah kedaluwarsa. Muat ulang lalu coba lagi.');
        header('Location: ' . $kembaliJenis);
        exit;
    }

    $aksi       = (string) $_POST['aksi_jenis'];
    $slug       = (string) ($_POST['slug'] ?? '');
    $semuaJenis = daftarJenisArtikel(true);
    $jenisIni   = $semuaJenis[$slug] ?? null;

    try {

        if ($aksi === 'simpan') {

            [$def, $galat] = jenisBersihkanKiriman($_POST);

            if ($slug !== '' && !$jenisIni) {
                $galat[] = 'Jenis yang mau diubah tidak ditemukan.';
            }

            if (!$galat) {
                $slugSimpan = $slug !== '' ? $slug : jenisBuatSlug($def['nama'], array_keys($semuaJenis));

                jenisSimpan($koneksi, $slugSimpan, $def);
                adminPesan('ok', $slug !== ''
                    ? 'Jenis "' . $def['nama'] . '" berhasil diperbarui.'
                    : 'Jenis "' . $def['nama'] . '" berhasil ditambahkan. Guru bisa langsung memilihnya di beranda.');

                header('Location: ' . $kembaliJenis);
                exit;
            }

            // Ada yang kurang: tampilkan form lagi dengan isian tadi.
            $formJenis  = $def;
            $galatJenis = $galat;
            $slugEdit   = $slug;

        } elseif ($aksi === 'hapus' && $jenisIni) {

            $namaJenis = $jenisIni['nama'];

            if ($jenisIni['_sumber'] === 'bawaan') {

                jenisSetAktif($koneksi, $slug, false);
                adminPesan('ok', 'Jenis "' . $namaJenis . '" dihapus dari pilihan guru. Ini jenis bawaan, jadi masih bisa ditampilkan lagi kapan saja.');

            } else {

                $dipakai = jenisJumlahArtikel($koneksi)[$slug] ?? 0;

                if ($dipakai === 0) {
                    jenisHapusCatatan($koneksi, $slug);
                    adminPesan('ok', 'Jenis "' . $namaJenis . '" dihapus.');
                } elseif ($jenisIni['_aktif']) {
                    jenisSetAktif($koneksi, $slug, false);
                    adminPesan('ok', 'Jenis "' . $namaJenis . '" sudah dipakai ' . $dipakai . ' artikel, jadi hanya dinonaktifkan (tidak lagi muncul di pilihan guru). Artikel lamanya aman.');
                } else {
                    adminPesan('galat', 'Jenis "' . $namaJenis . '" tidak bisa dihapus permanen karena masih dipakai ' . $dipakai . ' artikel.');
                }
            }

        } elseif ($aksi === 'tampil' && $jenisIni) {

            jenisPulihkan($koneksi, $slug);
            adminPesan('ok', 'Jenis "' . $jenisIni['nama'] . '" tampil lagi di pilihan guru.');

        } elseif ($aksi === 'reset' && $jenisIni && $jenisIni['_sumber'] === 'bawaan') {

            jenisResetKeBawaan($koneksi, $slug);
            adminPesan('ok', 'Jenis "' . $jenisIni['nama'] . '" dikembalikan ke isi bawaan.');
        }

    } catch (Throwable $e) {
        error_log('Admin gagal mengelola jenis artikel: ' . $e->getMessage());

        if ($aksi === 'simpan') {
            $formJenis  = $def ?? [];
            $galatJenis = ['Gagal menyimpan ke database. Coba lagi sebentar.'];
            $slugEdit   = $slug;
        } else {
            adminPesan('galat', 'Perubahan gagal disimpan. Coba lagi sebentar.');
        }
    }

    if ($formJenis === null) {
        header('Location: ' . $kembaliJenis);
        exit;
    }

    $tab = 'jenis';
}

// Membuka form tambah / ubah lewat link.
$aksiForm = (string) ($_GET['aksi'] ?? '');

if ($sudahMasuk && $tab === 'jenis' && $formJenis === null && ($aksiForm === 'baru' || $aksiForm === 'ubah')) {

    if ($aksiForm === 'baru') {

        $formJenis = [
            'nama' => '', 'ikon' => '📝', 'warna' => 'green-bg', 'ringkas' => '',
            'deskripsi' => '', 'cocok' => '', 'siapkan' => [], 'contoh' => [], 'panduan' => '',
            'foto_label' => 'Foto pendukung',
            'bagian' => [[
                'judul' => 'Tentang ceritanya', 'info' => '',
                'fields' => [['nama' => '', 'label' => '', 'tipe' => 'teks', 'wajib' => true]],
            ]],
        ];
        $slugEdit = '';

    } else {

        $slugEdit  = (string) ($_GET['slug'] ?? '');
        $formJenis = daftarJenisArtikel(true)[$slugEdit] ?? null;

        if ($formJenis === null) {
            adminPesan('galat', 'Jenis yang mau diubah tidak ditemukan.');
            header('Location: ' . urlAdmin(['tab' => 'jenis']));
            exit;
        }
    }
}


/* ------------------------------------------------------------------ */
/* Belum masuk: form kata sandi saja, tidak ada data apa pun.         */
/* ------------------------------------------------------------------ */

if (!$sudahMasuk) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - EduStory</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">

    </head>
    <body class="auth-page">

    <nav class="navbar">
        <div class="nav-container nav-container--center">
            <a href="index.php" class="back-home">← <span>Beranda</span></a>
            <a href="index.php" class="brand">
                <span class="brand-icon">✦</span>
                <span>EduStory</span>
            </a>
            <span class="nav-spacer" aria-hidden="true"></span>
        </div>
    </nav>

    <main class="auth-wrapper">

        <div class="auth-box">

            <div class="auth-heading">
                <div class="auth-icon">🔒</div>
                <span class="section-label">KHUSUS PENGELOLA</span>
                <h1>Masuk admin</h1>
                <p>Halaman ini terpisah dari akun guru.</p>
            </div>

            <?php if (!empty($pesanSalah)): ?>
                <p style="color:#b00020; margin: 0 0 16px; text-align:center;"><?= e($pesanSalah) ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Kata sandi admin</label>
                    <div class="input-sandi">
                        <input type="password" name="kata_sandi" id="kataSandiAdmin" placeholder="Masukkan kata sandi" required autofocus>
                        <button type="button" class="toggle-sandi" aria-label="Tampilkan kata sandi" aria-pressed="false">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-full">Masuk <span>→</span></button>
            </form>

        </div>

    </main>

    <footer class="footer">
        <p>© <?= date('Y') ?> EduStory</p>
    </footer>

    <script src="assets/js/toggle-sandi.js"></script>

    </body>
    </html>
    <?php
    exit;
}


/* ------------------------------------------------------------------ */
/* Sudah masuk: ringkasan + tab akun / artikel / jenis artikel.       */
/* ------------------------------------------------------------------ */

function hitung(mysqli $k, string $sql): int
{
    try {
        $hasil = $k->query($sql);
        return $hasil ? (int) ($hasil->fetch_row()[0] ?? 0) : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

$jumlahAkun    = hitung($koneksi, "SELECT COUNT(*) FROM users");
$jumlahSekolah = hitung($koneksi, "SELECT COUNT(*) FROM sekolah");
$jumlahArtikel = hitung($koneksi, "SELECT COUNT(*) FROM artikel");
$jumlahKlipaa  = hitung($koneksi, "SELECT COUNT(*) FROM artikel WHERE dikirim_klipaa = 1");

$semuaJenis  = daftarJenisArtikel(true);
$jumlahJenis = count(array_filter($semuaJenis, function ($j) {
    return !empty($j['_aktif']);
}));

$pesanAdmin = $_SESSION['admin_pesan'] ?? null;
unset($_SESSION['admin_pesan']);


// --- Tab: data akun (dipaginasi sendiri, 20 per halaman) ---

$halAkun       = max(1, (int) ($_GET['hal_akun'] ?? 1));
$perHalAkun    = 20;
$jumlahHalAkun = max(1, (int) ceil($jumlahAkun / $perHalAkun));
$akunList      = [];
$galatAkun     = null;

if ($tab === 'akun') {
    $offsetAkun = ($halAkun - 1) * $perHalAkun;

    try {
        $stmt = $koneksi->prepare(
            "SELECT u.id, u.nama, u.email, u.dibuat_pada,
                    s.nama_sekolah, s.jenjang, s.provinsi, s.kabupaten, s.kecamatan, s.desa
             FROM users u
             LEFT JOIN sekolah s ON s.user_id = u.id
             ORDER BY u.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ii", $perHalAkun, $offsetAkun);
        $stmt->execute();
        $akunList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (Throwable $e) {
        $galatAkun = $e->getMessage();
        error_log('Admin gagal mengambil daftar akun: ' . $galatAkun);
    }
}


// --- Tab: hasil artikel (20 terbaru per halaman) ---

$halaman        = max(1, (int) ($_GET['hal'] ?? 1));
$perHalaman     = 20;
$artikelList    = [];
$galatArtikel   = null;
$fotoPerArtikel = [];

if ($tab === 'artikel') {
    $offset = ($halaman - 1) * $perHalaman;

    try {
        $stmt = $koneksi->prepare(
            "SELECT a.id, a.jenis_nama, a.judul, a.isi, a.status, a.pesan_error,
                    a.dikirim_klipaa, a.dikirim_pada, a.dibuat_pada,
                    s.nama_sekolah
             FROM artikel a
             LEFT JOIN sekolah s ON s.id = a.sekolah_id
             ORDER BY a.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ii", $perHalaman, $offset);
        $stmt->execute();
        $artikelList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (Throwable $e) {
        $galatArtikel = $e->getMessage();
        error_log('Admin gagal mengambil daftar artikel: ' . $galatArtikel);
    }

    // Foto pertama tiap artikel di halaman ini (kalau ada), sekali query.
    if ($artikelList) {
        $ids = array_column($artikelList, 'id');
        $placeholder = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $koneksi->prepare(
                "SELECT artikel_id, MIN(token) AS token
                 FROM (
                     SELECT artikel_id, token
                     FROM artikel_gambar
                     WHERE artikel_id IN ($placeholder)
                     ORDER BY urutan, id
                 ) t
                 GROUP BY artikel_id"
            );
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $baris) {
                $fotoPerArtikel[(int) $baris['artikel_id']] = $baris['token'];
            }
            $stmt->close();
        } catch (Throwable $e) {
        }
    }
}


// --- Tab: jenis artikel ---

$artikelPerJenis = ($tab === 'jenis') ? jenisJumlahArtikel($koneksi) : [];


// Isian editor, dilengkapi supaya aman kalau ada bagian yang kosong.
$F = null;
$dataBagianJson = '[]';
$dataTipeJson   = '{}';

if ($formJenis !== null) {
    $F = array_merge([
        'nama' => '', 'ikon' => '📝', 'warna' => 'green-bg', 'ringkas' => '',
        'deskripsi' => '', 'cocok' => '', 'siapkan' => [], 'contoh' => [], 'panduan' => '',
        'foto_label' => 'Foto pendukung', 'bagian' => [],
    ], $formJenis);

    // Data untuk pembangun form (JS). Opsi diubah jadi teks "nilai | tampilan".
    $dataBagian = [];
    foreach ((array) $F['bagian'] as $b) {
        $fieldsJs = [];
        foreach ((array) ($b['fields'] ?? []) as $f) {
            $fieldsJs[] = [
                'nama'        => (string) ($f['nama'] ?? ''),
                'label'       => (string) ($f['label'] ?? ''),
                'tipe'        => (string) ($f['tipe'] ?? 'teks'),
                'wajib'       => !empty($f['wajib']),
                'placeholder' => (string) ($f['placeholder'] ?? ''),
                'bantuan'     => (string) ($f['bantuan'] ?? ''),
                'opsi'        => jenisOpsiKeTeks($f),
            ];
        }
        $dataBagian[] = [
            'judul'  => (string) ($b['judul'] ?? ''),
            'info'   => (string) ($b['info'] ?? ''),
            'fields' => $fieldsJs,
        ];
    }

    $bendera = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $dataBagianJson = json_encode($dataBagian, $bendera);
    $dataTipeJson   = json_encode(JENIS_TIPE_LABEL, $bendera);
}

$jenisUntukForm = $F !== null && $tab === 'jenis';
$emojiSaran = ['📝', '📰', '🎉', '🏆', '📚', '🌱', '🎨', '⚽', '🎤', '🤝', '🏫', '💡', '🌟', '🧒'];

?>
<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/artikel.css">
    <link rel="stylesheet" href="assets/css/admin.css">

</head>
<body class="adm-body">

<nav class="navbar">
    <div class="nav-container nav-container--center">
        <a href="dashboard.php" class="back-home">← <span>Kembali ke beranda</span></a>
        <a href="dashboard.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>
        <a href="admin.php?keluar=1" class="back-home"><span>Keluar admin</span></a>
    </div>
</nav>

<main class="adm-wrap">

    <header class="adm-hero">
        <span class="section-label">PANEL PENGELOLA</span>
        <h1>Admin EduStory</h1>
        <p>Pantau akun guru, tinjau artikel yang dihasilkan, dan atur jenis artikel yang bisa dipilih guru.</p>
    </header>

    <section class="adm-stats" aria-label="Ringkasan">
        <a class="adm-stat" href="<?= e(urlAdmin(['tab' => 'akun'])) ?>">
            <span class="adm-stat-ikon adm-hijau">👩‍🏫</span>
            <span><strong><?= $jumlahAkun ?></strong><small>Akun guru</small></span>
        </a>
        <a class="adm-stat" href="<?= e(urlAdmin(['tab' => 'akun'])) ?>">
            <span class="adm-stat-ikon adm-biru">🏫</span>
            <span><strong><?= $jumlahSekolah ?></strong><small>Sekolah</small></span>
        </a>
        <a class="adm-stat" href="<?= e(urlAdmin(['tab' => 'artikel'])) ?>">
            <span class="adm-stat-ikon adm-kuning">📝</span>
            <span><strong><?= $jumlahArtikel ?></strong><small>Artikel</small></span>
        </a>
        <a class="adm-stat" href="<?= e(urlAdmin(['tab' => 'artikel'])) ?>">
            <span class="adm-stat-ikon adm-jingga">✅</span>
            <span><strong><?= $jumlahKlipaa ?></strong><small>Sudah ke klipaa</small></span>
        </a>
    </section>

    <nav class="adm-tabs" aria-label="Bagian admin">
        <a href="<?= e(urlAdmin(['tab' => 'akun'])) ?>" class="adm-tab<?= $tab === 'akun' ? ' aktif' : '' ?>"<?= $tab === 'akun' ? ' aria-current="page"' : '' ?>>
            <span>👥</span> Data akun <em><?= $jumlahAkun ?></em>
        </a>
        <a href="<?= e(urlAdmin(['tab' => 'artikel'])) ?>" class="adm-tab<?= $tab === 'artikel' ? ' aktif' : '' ?>"<?= $tab === 'artikel' ? ' aria-current="page"' : '' ?>>
            <span>📰</span> Hasil artikel <em><?= $jumlahArtikel ?></em>
        </a>
        <a href="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" class="adm-tab<?= $tab === 'jenis' ? ' aktif' : '' ?>"<?= $tab === 'jenis' ? ' aria-current="page"' : '' ?>>
            <span>🧩</span> Jenis artikel <em><?= $jumlahJenis ?></em>
        </a>
    </nav>

    <?php if ($pesanAdmin): ?>
        <div class="adm-pesan <?= $pesanAdmin[0] === 'ok' ? 'adm-pesan-ok' : 'adm-pesan-galat' ?>" role="status">
            <?= e($pesanAdmin[1]) ?>
        </div>
    <?php endif; ?>


<?php /* ================================================================ */ ?>
<?php if ($tab === 'akun'): ?>
<?php /* ================================================================ */ ?>

    <section class="adm-kartu" id="akun">

        <div class="adm-kartu-kepala">
            <div>
                <h2>Data akun &amp; sekolah</h2>
                <p>Guru yang sudah mendaftar beserta data sekolahnya. Hanya untuk dilihat.</p>
            </div>
        </div>

        <?php if (!$akunList && $galatAkun): ?>
            <p class="adm-galat-teks">Daftar akun gagal dimuat (database bilang: <?= e($galatAkun) ?>).</p>
        <?php elseif (!$akunList): ?>
            <div class="adm-kosong"><span>👥</span><p>Belum ada akun yang daftar.</p></div>
        <?php else: ?>

            <div class="adm-tabel-bungkus">
                <table class="adm-tabel">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Sekolah</th>
                            <th>Jenjang</th>
                            <th>Wilayah</th>
                            <th>Daftar sejak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($akunList as $u): ?>
                            <?php
                                $bagianWilayah = array_filter([
                                    rapikanWilayah($u['kecamatan'] ?? ''),
                                    rapikanWilayah($u['kabupaten'] ?? ''),
                                    rapikanWilayah($u['provinsi'] ?? ''),
                                ]);
                            ?>
                            <tr>
                                <td>
                                    <div class="adm-orang">
                                        <span class="adm-avatar"><?= e(strtoupper(mb_substr((string) $u['nama'], 0, 1))) ?></span>
                                        <span>
                                            <strong><?= e($u['nama']) ?></strong>
                                            <small><?= e($u['email']) ?></small>
                                        </span>
                                    </div>
                                </td>
                                <td><?= e($u['nama_sekolah'] ?: '-') ?></td>
                                <td><?php if ($u['jenjang']): ?><span class="adm-lencana"><?= e($u['jenjang']) ?></span><?php else: ?>-<?php endif; ?></td>
                                <td class="adm-wilayah"><?= $bagianWilayah ? e(implode(', ', $bagianWilayah)) : '-' ?></td>
                                <td><?= e(date('d M Y', strtotime($u['dibuat_pada']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="adm-paginasi">
                <?php if ($halAkun > 1): ?>
                    <a href="<?= e(urlAdmin(['tab' => 'akun', 'hal_akun' => $halAkun - 1])) ?>" class="btn-garis">← Sebelumnya</a>
                <?php else: ?><span></span><?php endif; ?>

                <span class="adm-paginasi-info">Halaman <?= $halAkun ?> dari <?= $jumlahHalAkun ?></span>

                <?php if ($halAkun < $jumlahHalAkun): ?>
                    <a href="<?= e(urlAdmin(['tab' => 'akun', 'hal_akun' => $halAkun + 1])) ?>" class="btn-garis">Berikutnya →</a>
                <?php else: ?><span></span><?php endif; ?>
            </div>

        <?php endif; ?>

    </section>


<?php /* ================================================================ */ ?>
<?php elseif ($tab === 'artikel'): ?>
<?php /* ================================================================ */ ?>

    <section class="adm-kartu" id="artikel">

        <div class="adm-kartu-kepala">
            <div>
                <h2>Hasil artikel</h2>
                <p>Artikel dari semua guru, yang terbaru di atas. Isinya tidak bisa diubah dari sini; yang bisa diatur hanya penanda "sudah dikirim ke klipaa" dan hapus artikel.</p>
            </div>
        </div>

        <?php if (!$artikelList && $galatArtikel): ?>
            <p class="adm-galat-teks">
                Daftar artikel gagal dimuat (database bilang: <?= e($galatArtikel) ?>).
                Kemungkinan besar <code>database/schema-update.sql</code> belum dijalankan
                di phpMyAdmin -- kolom <code>dikirim_klipaa</code> belum ada di tabel <code>artikel</code>.
            </p>
        <?php elseif (!$artikelList): ?>
            <div class="adm-kosong"><span>📰</span><p>Belum ada artikel.</p></div>
        <?php else: ?>

            <div class="adm-artikel-daftar">
                <?php foreach ($artikelList as $a): ?>
                    <?php
                        [$teksStatus, $kelasStatus] = labelStatus($a['status']);
                        $token = $fotoPerArtikel[(int) $a['id']] ?? null;
                    ?>
                    <article class="adm-artikel">

                        <div class="adm-artikel-foto">
                            <?php if ($token): ?>
                                <img src="gambar.php?t=<?= e($token) ?>" alt="">
                            <?php else: ?>
                                <span>Tanpa foto</span>
                            <?php endif; ?>
                        </div>

                        <div class="adm-artikel-info">
                            <strong><?= e($a['judul'] ?: '(belum ada judul)') ?></strong>
                            <div class="adm-meta">
                                <span class="adm-lencana"><?= e($a['jenis_nama']) ?></span>
                                <?php if ($a['nama_sekolah']): ?><span><?= e($a['nama_sekolah']) ?></span><?php endif; ?>
                                <span><?= e(date('d M Y', strtotime($a['dibuat_pada']))) ?></span>
                            </div>
                            <span class="status <?= e($kelasStatus) ?>"><?= e($teksStatus) ?></span>
                            <?php if ($a['status'] === 'gagal' && $a['pesan_error']): ?>
                                <div class="adm-artikel-error"><?= e($a['pesan_error']) ?></div>
                            <?php endif; ?>

                            <?php if (!empty($a['isi'])): ?>
                                <button type="button" class="adm-lihat-isi" data-modal="isi-<?= (int) $a['id'] ?>">Lihat isi →</button>
                            <?php endif; ?>
                        </div>

                        <div class="adm-artikel-aksi">
                            <?php if ($a['dikirim_klipaa']): ?>
                                <span class="status status-selesai">✓ Sudah ke klipaa</span>
                                <small><?= e(date('d M Y', strtotime($a['dikirim_pada']))) ?></small>
                                <form method="POST">
                                    <input type="hidden" name="artikel_id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="tandai_klipaa" value="0">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <button type="submit" class="btn-garis adm-btn-kecil">Batalkan tanda</button>
                                </form>
                            <?php else: ?>
                                <span class="status status-proses">Belum ke klipaa</span>
                                <form method="POST">
                                    <input type="hidden" name="artikel_id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="tandai_klipaa" value="1">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <button type="submit" class="btn-primary adm-btn-kecil">Tandai sudah</button>
                                </form>
                            <?php endif; ?>

                            <div class="adm-aksi-baris">
                                <a href="artikel.php?id=<?= (int) $a['id'] ?>" class="adm-tautan">Buka artikel →</a>

                                <form method="POST" data-konfirmasi-hapus data-judul="<?= e($a['judul'] ?: 'artikel ini') ?>">
                                    <input type="hidden" name="artikel_id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="hapus_artikel" value="1">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <button type="submit" class="adm-btn-hapus">Hapus artikel</button>
                                </form>
                            </div>
                        </div>

                    </article>

                    <?php if (!empty($a['isi'])): ?>
                        <dialog class="buku-modal admin-isi-modal" id="isi-<?= (int) $a['id'] ?>">
                            <article class="buku">
                                <button type="button" class="buku-tutup" data-tutup-modal aria-label="Tutup">✕</button>
                                <h3><?= e($a['judul'] ?: '(belum ada judul)') ?></h3>
                                <?php if ($token): ?>
                                    <img src="gambar.php?t=<?= e($token) ?>" alt="" class="admin-isi-foto">
                                <?php endif; ?>
                                <?= renderIsiArtikel($a['isi']) ?>
                            </article>
                        </dialog>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="adm-paginasi">
                <?php if ($halaman > 1): ?>
                    <a href="<?= e(urlAdmin(['tab' => 'artikel', 'hal' => $halaman - 1])) ?>" class="btn-garis">← Sebelumnya</a>
                <?php else: ?><span></span><?php endif; ?>

                <span class="adm-paginasi-info">Halaman <?= $halaman ?></span>

                <?php if (count($artikelList) === $perHalaman): ?>
                    <a href="<?= e(urlAdmin(['tab' => 'artikel', 'hal' => $halaman + 1])) ?>" class="btn-garis">Berikutnya →</a>
                <?php else: ?><span></span><?php endif; ?>
            </div>

        <?php endif; ?>

    </section>


<?php /* ================================================================ */ ?>
<?php elseif ($jenisUntukForm): ?>
<?php /* ======================  EDITOR JENIS  ========================== */ ?>

    <?php $editBawaan = $slugEdit !== '' && (($semuaJenis[$slugEdit]['_sumber'] ?? '') === 'bawaan'); ?>

    <section class="adm-kartu">

        <div class="adm-kartu-kepala">
            <div>
                <h2><?= $slugEdit === '' ? 'Tambah jenis artikel' : 'Ubah jenis artikel' ?></h2>
                <p>
                    <?php if ($editBawaan): ?>
                        Ini jenis bawaan. Perubahan disimpan di database tanpa mengubah file aslinya, dan bisa dikembalikan lewat tombol "Kembalikan ke bawaan" di daftar.
                    <?php else: ?>
                        Isi seperti menyiapkan satu jenis di beranda guru: tampilannya, penjelasannya, lalu pertanyaan yang harus dijawab guru.
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" class="btn-garis">← Batal</a>
        </div>

        <?php if ($galatJenis): ?>
            <div class="adm-pesan adm-pesan-galat" role="alert">
                <strong>Belum bisa disimpan:</strong>
                <ul>
                    <?php foreach (array_unique($galatJenis) as $g): ?>
                        <li><?= e($g) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" id="formJenis" class="adm-form">
            <input type="hidden" name="aksi_jenis" value="simpan">
            <input type="hidden" name="slug" value="<?= e($slugEdit) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="bagian_json" id="bagianJson" value="[]">

            <fieldset class="adm-set">
                <legend><span>1</span> Tampilan di beranda guru</legend>

                <div class="adm-baris-2">
                    <div class="form-group">
                        <label for="j-nama">Nama jenis <span class="wajib">*</span></label>
                        <input type="text" id="j-nama" name="nama" maxlength="60" value="<?= e($F['nama']) ?>" placeholder="Contoh: Prestasi" required>
                    </div>
                    <div class="form-group">
                        <label for="j-ikon">Ikon (emoji)</label>
                        <input type="text" id="j-ikon" name="ikon" maxlength="8" value="<?= e($F['ikon']) ?>">
                        <div class="adm-emoji" data-emoji-untuk="j-ikon">
                            <?php foreach ($emojiSaran as $em): ?>
                                <button type="button" class="adm-emoji-btn" data-emoji="<?= e($em) ?>"><?= e($em) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Warna latar ikon</label>
                    <div class="adm-warna">
                        <?php foreach (JENIS_WARNA_LABEL as $kelas => $namaWarna): ?>
                            <label class="adm-warna-item" title="<?= e($namaWarna) ?>">
                                <input type="radio" name="warna" value="<?= e($kelas) ?>"<?= $F['warna'] === $kelas ? ' checked' : '' ?>>
                                <span class="adm-warna-kotak <?= e($kelas) ?>"></span>
                                <small><?= e($namaWarna) ?></small>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="j-ringkas">Kalimat ringkas <span class="wajib">*</span></label>
                    <input type="text" id="j-ringkas" name="ringkas" maxlength="160" value="<?= e($F['ringkas']) ?>" placeholder="Contoh: Bagikan kebanggaan dari sekolahmu.">
                </div>
            </fieldset>

            <fieldset class="adm-set">
                <legend><span>2</span> Penjelasan (tombol "?")</legend>

                <div class="form-group">
                    <label for="j-deskripsi">Penjelasan jenis ini <span class="wajib">*</span></label>
                    <textarea id="j-deskripsi" name="deskripsi" rows="3" maxlength="700"><?= e($F['deskripsi']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="j-cocok">Cocok untuk <span class="wajib">*</span></label>
                    <textarea id="j-cocok" name="cocok" rows="2" maxlength="400"><?= e($F['cocok']) ?></textarea>
                </div>
                <div class="adm-baris-2">
                    <div class="form-group">
                        <label for="j-siapkan">Yang perlu disiapkan</label>
                        <p class="bantuan">Satu butir per baris.</p>
                        <textarea id="j-siapkan" name="siapkan" rows="4"><?= e(implode("\n", (array) $F['siapkan'])) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="j-contoh">Contoh judul</label>
                        <p class="bantuan">Satu judul per baris.</p>
                        <textarea id="j-contoh" name="contoh" rows="4"><?= e(implode("\n", (array) $F['contoh'])) ?></textarea>
                    </div>
                </div>
            </fieldset>

            <fieldset class="adm-set">
                <legend><span>3</span> Arahan untuk AI</legend>
                <div class="form-group">
                    <label for="j-panduan">Bagaimana artikel jenis ini harus ditulis? <span class="wajib">*</span></label>
                    <p class="bantuan">Dikirim ke AI bersama jawaban guru. Contoh: "Tulis sebagai berita yang enak dibaca, mulai dari satu kejadian, jangan menambah fakta."</p>
                    <textarea id="j-panduan" name="panduan" rows="3" maxlength="900"><?= e($F['panduan']) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="adm-set">
                <legend><span>4</span> Pertanyaan untuk guru</legend>
                <p class="adm-set-info">Bagian-bagian ini muncul sebagai langkah pada formulir guru. Pertanyaan lama sebaiknya tidak dihapus kalau sudah ada artikel yang memakainya.</p>

                <div id="pembangun" class="adm-pembangun"></div>

                <button type="button" class="btn-garis" id="tambahBagian">+ Tambah bagian</button>

                <div class="form-group adm-foto">
                    <label for="j-foto">Judul kotak unggah foto</label>
                    <input type="text" id="j-foto" name="foto_label" maxlength="60" value="<?= e($F['foto_label']) ?>" placeholder="Foto pendukung">
                </div>
            </fieldset>

            <div class="adm-form-aksi">
                <button type="submit" class="btn-primary"><?= $slugEdit === '' ? 'Tambahkan jenis' : 'Simpan perubahan' ?> <span>→</span></button>
                <a href="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" class="btn-garis">Batal</a>
            </div>
        </form>

        <script type="application/json" id="dataBagian"><?= $dataBagianJson ?></script>
        <script type="application/json" id="dataTipe"><?= $dataTipeJson ?></script>

    </section>


<?php else: /* ====================  DAFTAR JENIS  ====================== */ ?>

    <section class="adm-kartu" id="jenis">

        <div class="adm-kartu-kepala">
            <div>
                <h2>Jenis artikel</h2>
                <p>Daftar jenis yang muncul pada pilihan "Mau menulis artikel apa?" di beranda guru. Tambah, ubah, atau hapus di sini.</p>
            </div>
            <a href="<?= e(urlAdmin(['tab' => 'jenis', 'aksi' => 'baru'])) ?>" class="btn-primary adm-btn-tambah">+ Tambah jenis</a>
        </div>

        <div class="adm-jenis-grid">
            <?php foreach ($semuaJenis as $slug => $j): ?>
                <?php
                    $dipakai   = (int) ($artikelPerJenis[$slug] ?? 0);
                    $aktif     = !empty($j['_aktif']);
                    $bawaan    = ($j['_sumber'] ?? '') === 'bawaan';
                    $jmlBagian = count($j['bagian'] ?? []);
                    $jmlTanya  = count(semuaField($j));

                    if ($bawaan) {
                        $konfirmasi = 'Hapus "' . $j['nama'] . '" dari pilihan guru? Ini jenis bawaan, jadi hanya disembunyikan dan bisa ditampilkan lagi.';
                    } elseif ($dipakai === 0) {
                        $konfirmasi = 'Hapus "' . $j['nama'] . '" secara permanen? Tidak bisa dikembalikan.';
                    } else {
                        $konfirmasi = '"' . $j['nama'] . '" sudah dipakai ' . $dipakai . ' artikel, jadi tidak dihapus tapi disembunyikan dari pilihan guru. Lanjutkan?';
                    }

                    // Jenis buatan admin yang sudah nonaktif dan masih punya artikel tidak bisa dihapus lagi.
                    $bisaHapus = $aktif || (!$bawaan && $dipakai === 0);
                ?>
                <article class="adm-jenis<?= $aktif ? '' : ' nonaktif' ?>">

                    <div class="adm-jenis-atas">
                        <span class="adm-jenis-ikon <?= e($j['warna'] ?? '') ?>"><?= e($j['ikon'] ?? '📝') ?></span>
                        <div class="adm-jenis-judul">
                            <strong><?= e($j['nama']) ?></strong>
                            <div class="adm-lencana-baris">
                                <span class="adm-lencana"><?= $bawaan ? 'Bawaan' : 'Buatan admin' ?></span>
                                <?php if (!empty($j['_diubah'])): ?><span class="adm-lencana adm-lencana-kuning">Diubah</span><?php endif; ?>
                                <?php if (!$aktif): ?><span class="adm-lencana adm-lencana-merah">Tidak tampil</span><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <p class="adm-jenis-ringkas"><?= e($j['ringkas'] ?? '') ?></p>

                    <div class="adm-jenis-info">
                        <span><?= $jmlBagian ?> bagian · <?= $jmlTanya ?> pertanyaan</span>
                        <span><?= $dipakai ?> artikel</span>
                    </div>

                    <div class="adm-jenis-aksi">

                        <a href="<?= e(urlAdmin(['tab' => 'jenis', 'aksi' => 'ubah', 'slug' => $slug])) ?>" class="btn-garis adm-btn-kecil">✎ Ubah</a>

                        <?php if (!$aktif): ?>
                            <form method="POST" action="<?= e(urlAdmin(['tab' => 'jenis'])) ?>">
                                <input type="hidden" name="aksi_jenis" value="tampil">
                                <input type="hidden" name="slug" value="<?= e($slug) ?>">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                <button type="submit" class="btn-primary adm-btn-kecil">Tampilkan lagi</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($bawaan && !empty($j['_diubah'])): ?>
                            <form method="POST" action="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" data-konfirmasi="<?= e('Kembalikan "' . $j['nama'] . '" ke isi bawaan? Semua ubahanmu pada jenis ini hilang.') ?>">
                                <input type="hidden" name="aksi_jenis" value="reset">
                                <input type="hidden" name="slug" value="<?= e($slug) ?>">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                <button type="submit" class="adm-btn-teks">Kembalikan ke bawaan</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($bisaHapus): ?>
                            <form method="POST" action="<?= e(urlAdmin(['tab' => 'jenis'])) ?>" data-konfirmasi="<?= e($konfirmasi) ?>">
                                <input type="hidden" name="aksi_jenis" value="hapus">
                                <input type="hidden" name="slug" value="<?= e($slug) ?>">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                <button type="submit" class="adm-btn-hapus">Hapus</button>
                            </form>
                        <?php endif; ?>

                    </div>

                </article>
            <?php endforeach; ?>
        </div>

    </section>

<?php endif; ?>

</main>

<footer class="footer">
    <p>© <?= date('Y') ?> EduStory</p>
</footer>

<script src="assets/js/admin.js"></script>

</body>
</html>
