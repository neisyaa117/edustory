<?php

/*
|--------------------------------------------------------------------------
| Halaman admin
|--------------------------------------------------------------------------
|
| Ringkasan: jumlah akun, sekolah, dan artikel per status. Tidak mengubah
| atau menghapus data apa pun -- khusus untuk melihat.
|
| Dilindungi kata sandi TERPISAH dari akun guru (bukan email/password di
| tabel users), supaya tidak perlu login sebagai guru untuk membukanya.
| Isi kata sandinya lewat ADMIN_PASSWORD di config/lokal.php, atau lewat
| Environment Variable di hosting. Kalau belum diisi, halaman ini menolak
| dibuka sama sekali -- jadi aman walau linknya ada di footer semua halaman.
*/

require_once __DIR__ . '/lib/helpers.php';

session_start();

$kataSandiAdmin = envNilai('ADMIN_PASSWORD');
$pesanSalah     = '';

// Belum diatur sama sekali -> jangan izinkan siapa pun masuk, termasuk tanpa kata sandi.
if ($kataSandiAdmin === '') {

    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Admin belum diatur - EduStory</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="auth-page">
        <main class="auth-wrapper">
            <div class="auth-box">
                <div class="auth-heading">
                    <h1>Halaman admin belum diatur</h1>
                    <p>Isi <code>ADMIN_PASSWORD</code> di <code>config/lokal.php</code> (komputer sendiri / hosting) atau Environment Variables (Vercel) untuk mengaktifkan halaman ini.</p>
                </div>
                <a href="index.php" class="btn-garis btn-blok">Kembali ke beranda</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Proses logout admin (tidak menyentuh sesi login guru).
if (isset($_GET['keluar'])) {
    unset($_SESSION['admin_masuk']);
    header('Location: admin.php');
    exit;
}

// Proses pengiriman kata sandi.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kata_sandi'])) {

    if (hash_equals($kataSandiAdmin, (string) $_POST['kata_sandi'])) {
        $_SESSION['admin_masuk'] = true;
        header('Location: admin.php');
        exit;
    }

    $pesanSalah = 'Kata sandi salah.';
}

$sudahMasuk = !empty($_SESSION['admin_masuk']);


/* ------------------------------------------------------------------ */
/* Belum masuk: tampilkan form kata sandi saja, tidak ada data apa pun. */
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
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="back-home">← Beranda</a>
                <a href="index.php" class="brand">
                    <span class="brand-icon">✦</span>
                    <span>EduStory</span>
                </a>
            </div>
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

            <?php if ($pesanSalah): ?>
                <p style="color:#b00020; margin: 0 0 16px; text-align:center;"><?= e($pesanSalah) ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Kata sandi admin</label>
                    <input type="password" name="kata_sandi" placeholder="Masukkan kata sandi" required autofocus>
                </div>
                <button type="submit" class="btn-primary btn-full">Masuk <span>→</span></button>
            </form>

        </div>

    </main>

    <footer class="footer">
        <p>© <?= date('Y') ?> EduStory</p>
    </footer>

    </body>
    </html>
    <?php
    exit;
}


/* ------------------------------------------------------------------ */
/* Sudah masuk: ringkasan saja, tidak ada tombol ubah/hapus.           */
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
$perStatus     = [
    'baru'    => hitung($koneksi, "SELECT COUNT(*) FROM artikel WHERE status = 'baru'"),
    'menulis' => hitung($koneksi, "SELECT COUNT(*) FROM artikel WHERE status = 'menulis'"),
    'selesai' => hitung($koneksi, "SELECT COUNT(*) FROM artikel WHERE status = 'selesai'"),
    'gagal'   => hitung($koneksi, "SELECT COUNT(*) FROM artikel WHERE status = 'gagal'"),
];

$sekolahTerbaru = [];
try {
    $hasil = $koneksi->query(
        "SELECT nama_sekolah, provinsi, kabupaten, kecamatan, desa, kode_desa
         FROM sekolah ORDER BY id DESC LIMIT 15"
    );
    $sekolahTerbaru = $hasil ? $hasil->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
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

</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-left">
            <a href="dashboard.php" class="back-home">← Kembali ke beranda</a>
            <a href="dashboard.php" class="brand">
                <span class="brand-icon">✦</span>
                <span>EduStory</span>
            </a>
        </div>
        <a href="admin.php?keluar=1" class="back-home">Keluar admin</a>
    </div>
</nav>

<main style="max-width: 860px; margin: 40px auto; padding: 0 20px;">

    <h1 style="margin-bottom: 4px;">Ringkasan</h1>
    <p style="color: var(--ink-soft); margin-top: 0;">Hanya untuk dilihat -- tidak ada yang bisa diubah dari sini.</p>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px,1fr)); gap:16px; margin: 24px 0;">
        <div class="school-mini-card"><strong style="font-size:28px;"><?= $jumlahAkun ?></strong><div>Akun guru</div></div>
        <div class="school-mini-card"><strong style="font-size:28px;"><?= $jumlahSekolah ?></strong><div>Sekolah</div></div>
        <div class="school-mini-card"><strong style="font-size:28px;"><?= $jumlahArtikel ?></strong><div>Artikel</div></div>
    </div>

    <h2>Artikel per status</h2>
    <ul>
        <li>Baru: <?= $perStatus['baru'] ?></li>
        <li>Sedang ditulis: <?= $perStatus['menulis'] ?></li>
        <li>Selesai: <?= $perStatus['selesai'] ?></li>
        <li>Gagal: <?= $perStatus['gagal'] ?></li>
    </ul>

    <h2>15 sekolah terbaru</h2>
    <?php if (!$sekolahTerbaru): ?>
        <p>Belum ada data.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom: 2px solid var(--line);">
                    <th style="padding:8px;">Sekolah</th>
                    <th style="padding:8px;">Wilayah</th>
                    <th style="padding:8px;">Kode desa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sekolahTerbaru as $s): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding:8px;"><?= e($s['nama_sekolah'] ?? '') ?></td>
                        <td style="padding:8px;">
                            <?= e(rapikanWilayah($s['desa'] ?? '')) ?>,
                            <?= e(rapikanWilayah($s['kecamatan'] ?? '')) ?>,
                            <?= e(rapikanWilayah($s['kabupaten'] ?? '')) ?>
                        </td>
                        <td style="padding:8px;"><code><?= e($s['kode_desa'] ?? '-') ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

</main>

<footer class="footer">
    <p>© <?= date('Y') ?> EduStory</p>
</footer>

</body>
</html>
