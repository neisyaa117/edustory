<?php

/*
|--------------------------------------------------------------------------
| Halaman admin
|--------------------------------------------------------------------------
|
| Ringkasan jumlah akun/sekolah, dan daftar artikel -- judul, foto, status
| penulisan, dan penanda "sudah dikirim ke klipaa". Tidak ada tombol untuk
| mengubah atau menghapus isi artikel; yang bisa diubah dari sini cuma
| penanda klipaa itu sendiri, karena pengirimannya sendiri masih manual
| (salin-tempel oleh gurunya), jadi sistem tidak bisa tahu otomatis.
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

// Tandai/batalkan status kirim-klipaa (hanya setelah masuk).
if ($sudahMasuk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tandai_klipaa'])) {

    $artikelId   = (int) ($_POST['artikel_id'] ?? 0);
    $sudahKirim  = $_POST['tandai_klipaa'] === '1';

    if ($artikelId > 0) {
        setKlipaaArtikel($koneksi, $artikelId, $sudahKirim);
    }

    header('Location: admin.php' . (isset($_GET['hal']) ? '?hal=' . (int) $_GET['hal'] : ''));
    exit;
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
/* Sudah masuk: ringkasan + daftar artikel.                           */
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

// Paginasi sederhana: 20 artikel terbaru per halaman.
$halaman    = max(1, (int) ($_GET['hal'] ?? 1));
$perHalaman = 20;
$offset     = ($halaman - 1) * $perHalaman;

$artikelList  = [];
$galatArtikel = null;
try {
    $stmt = $koneksi->prepare(
        "SELECT a.id, a.jenis_nama, a.judul, a.status, a.pesan_error,
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

// Ambil foto pertama tiap artikel di halaman ini (kalau ada), sekali query.
$fotoPerArtikel = [];
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

</head>
<body>

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

<main class="admin-wrap">

    <h1>Ringkasan</h1>
    <p class="admin-sub">Hanya untuk dilihat. Satu-satunya yang bisa diubah di sini adalah penanda "sudah dikirim ke klipaa".</p>

    <div class="admin-stat-grid">
        <div class="school-mini-card"><strong><?= $jumlahAkun ?></strong><div>Akun guru</div></div>
        <div class="school-mini-card"><strong><?= $jumlahSekolah ?></strong><div>Sekolah</div></div>
        <div class="school-mini-card"><strong><?= $jumlahArtikel ?></strong><div>Artikel</div></div>
        <div class="school-mini-card"><strong><?= $jumlahKlipaa ?></strong><div>Sudah ke klipaa</div></div>
    </div>

    <h2>Artikel</h2>

    <?php if (!$artikelList && $galatArtikel): ?>
        <p style="color:#b00020;">
            Daftar artikel gagal dimuat (database bilang: <?= e($galatArtikel) ?>).
            Kemungkinan besar <code>database/schema-update.sql</code> belum dijalankan
            di phpMyAdmin -- kolom <code>dikirim_klipaa</code> belum ada di tabel <code>artikel</code>.
        </p>
    <?php elseif (!$artikelList): ?>
        <p>Belum ada artikel.</p>
    <?php else: ?>

        <div class="admin-artikel-list">
            <?php foreach ($artikelList as $a): ?>
                <?php
                    [$teksStatus, $kelasStatus] = labelStatus($a['status']);
                    $token = $fotoPerArtikel[(int) $a['id']] ?? null;
                ?>
                <div class="admin-artikel-row">

                    <div class="admin-artikel-foto">
                        <?php if ($token): ?>
                            <img src="gambar.php?t=<?= e($token) ?>" alt="">
                        <?php else: ?>
                            <span class="admin-artikel-foto-kosong">Tanpa foto</span>
                        <?php endif; ?>
                    </div>

                    <div class="admin-artikel-info">
                        <strong><?= e($a['judul'] ?: '(belum ada judul)') ?></strong>
                        <div class="admin-artikel-meta">
                            <?= e($a['jenis_nama']) ?>
                            <?php if ($a['nama_sekolah']): ?> · <?= e($a['nama_sekolah']) ?><?php endif; ?>
                            · <?= e(date('d M Y', strtotime($a['dibuat_pada']))) ?>
                        </div>
                        <span class="status <?= e($kelasStatus) ?>"><?= e($teksStatus) ?></span>
                        <?php if ($a['status'] === 'gagal' && $a['pesan_error']): ?>
                            <div class="admin-artikel-error"><?= e($a['pesan_error']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-artikel-klipaa">
                        <?php if ($a['dikirim_klipaa']): ?>
                            <span class="status status-selesai">✓ Sudah ke klipaa</span>
                            <div class="admin-artikel-meta"><?= e(date('d M Y', strtotime($a['dikirim_pada']))) ?></div>
                            <form method="POST">
                                <input type="hidden" name="artikel_id" value="<?= (int) $a['id'] ?>">
                                <input type="hidden" name="tandai_klipaa" value="0">
                                <button type="submit" class="btn-garis admin-btn-kecil">Batalkan tanda</button>
                            </form>
                        <?php else: ?>
                            <span class="status status-proses">Belum ke klipaa</span>
                            <form method="POST">
                                <input type="hidden" name="artikel_id" value="<?= (int) $a['id'] ?>">
                                <input type="hidden" name="tandai_klipaa" value="1">
                                <button type="submit" class="btn-primary admin-btn-kecil">Tandai sudah</button>
                            </form>
                        <?php endif; ?>
                        <a href="artikel.php?id=<?= (int) $a['id'] ?>" class="admin-artikel-buka">Buka artikel →</a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-paginasi">
            <?php if ($halaman > 1): ?>
                <a href="?hal=<?= $halaman - 1 ?>" class="btn-garis">← Sebelumnya</a>
            <?php endif; ?>
            <?php if (count($artikelList) === $perHalaman): ?>
                <a href="?hal=<?= $halaman + 1 ?>" class="btn-garis">Berikutnya →</a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</main>

<footer class="footer">
    <p>© <?= date('Y') ?> EduStory</p>
</footer>

</body>
</html>
