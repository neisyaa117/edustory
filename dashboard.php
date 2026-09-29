<?php

require_once __DIR__ . '/lib/helpers.php';

$userId = wajibLogin();

$user    = ambilUser($koneksi, $userId);
$sekolah = ambilSekolah($koneksi, $userId);

if (!$sekolah) {
    header("Location: profil-sekolah.php");
    exit;
}

$namaUser    = $user['nama'] ?? 'Teman';
$namaSekolah = $sekolah['nama_sekolah'] ?? 'Sekolahmu';
$jenjang     = $sekolah['jenjang'] ?? '';
$kecamatan   = rapikanWilayah($sekolah['kecamatan'] ?? '');
$kodeDesaAda = !empty($sekolah['kode_desa']);

$daftarJenis = daftarJenisArtikel();


/*
| Riwayat artikel. Bila tabel belum dibuat (schema.sql belum dijalankan),
| dashboard tetap terbuka dan memberi tahu apa yang perlu dilakukan.
*/

$riwayat = [];
$databaseBelumSiap = false;

try {

    $stmt = $koneksi->prepare(
        "SELECT id, jenis_nama, judul, status, dibuat_pada
         FROM artikel
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 12"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (mysqli_sql_exception $e) {

    error_log('Riwayat artikel gagal dimuat: ' . $e->getMessage());
    $databaseBelumSiap = true;
}

$jenisPertama = array_key_first($daftarJenis);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Beranda - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/artikel.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>


<body class="dashboard-page">


<nav class="navbar dashboard-nav">

    <div class="nav-container">

        <a href="dashboard.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <div class="dashboard-nav-right">

            <div class="user-mini">
                <div class="user-avatar">
                    <?= e(strtoupper(mb_substr($namaUser, 0, 1))) ?>
                </div>

                <div class="user-name">
                    <?= e($namaUser) ?>
                </div>
            </div>

            <a href="logout.php" class="logout-link">Keluar</a>

        </div>

    </div>

</nav>


<main class="dashboard-wrapper">


    <section class="welcome-area">

        <div>

            <span class="section-label">BERANDA SEKOLAH</span>

            <h1>Halo, <?= e($namaUser) ?>! 👋</h1>

            <p>
                Mau menceritakan apa dari
                <strong><?= e($namaSekolah) ?></strong>
                hari ini?
            </p>

        </div>


        <a href="profil-sekolah.php" class="school-mini-card" title="Ubah data sekolah">

            <div class="school-mini-icon">🏫</div>

            <div>
                <strong><?= e($namaSekolah) ?></strong>

                <span>
                    <?= e($jenjang) ?>
                    <?php if ($kecamatan): ?>
                        <?= $jenjang ? '· ' : '' ?>Kec. <?= e($kecamatan) ?>
                    <?php endif; ?>
                </span>
            </div>

            <span class="school-mini-edit">Ubah</span>

        </a>

    </section>


    <?php if (!$kodeDesaAda): ?>
        <div class="pemberitahuan">
            <strong>Kode desa belum tersimpan.</strong>
            Pilih ulang provinsi sampai desa di
            <a href="profil-sekolah.php">data sekolah</a>
            supaya artikel bisa dihubungkan ke klipaa.
        </div>
    <?php endif; ?>

    <?php if ($databaseBelumSiap): ?>
        <div class="pemberitahuan pemberitahuan-merah">
            <strong>Database belum diperbarui.</strong>
            Jalankan file <code>database/schema.sql</code> di phpMyAdmin,
            lalu muat ulang halaman ini.
        </div>
    <?php endif; ?>


    <section class="pilih-section">

        <div class="pilih-judul">
            <h2>Mau menulis artikel apa?</h2>
            <p>Pilih jenisnya di daftar, baca deskripsinya, lalu mulai menulis.</p>
        </div>

        <div class="pilih" id="pilihJenis">

            <div class="pilih-daftar" role="tablist" aria-label="Jenis artikel">

                <?php foreach ($daftarJenis as $slug => $j): ?>

                    <button
                        type="button"
                        role="tab"
                        class="pilih-item"
                        id="tab-<?= e($slug) ?>"
                        aria-controls="panel-<?= e($slug) ?>"
                        aria-selected="<?= $slug === $jenisPertama ? 'true' : 'false' ?>"
                        tabindex="<?= $slug === $jenisPertama ? '0' : '-1' ?>"
                        data-target="<?= e($slug) ?>"
                    >
                        <span class="pilih-ikon <?= e($j['warna']) ?>"><i class="fa-solid <?= e($j['ikon']) ?>" aria-hidden="true"></i></span>

                        <span class="pilih-teks">
                            <span class="pilih-nama"><?= e($j['nama']) ?></span>
                            <span class="pilih-ringkas"><?= e($j['ringkas']) ?></span>
                        </span>
                    </button>

                <?php endforeach; ?>

            </div>


            <div class="pilih-halaman">

                <?php foreach ($daftarJenis as $slug => $j): ?>

                    <article
                        class="buku"
                        role="tabpanel"
                        id="panel-<?= e($slug) ?>"
                        aria-labelledby="tab-<?= e($slug) ?>"
                        <?= $slug === $jenisPertama ? '' : 'hidden' ?>
                    >

                        <h3><?= e($j['nama']) ?></h3>

                        <p class="buku-lead"><?= e($j['deskripsi']) ?></p>

                        <h4>Cocok untuk</h4>
                        <p><?= e($j['cocok']) ?></p>

                        <h4>Yang perlu disiapkan</h4>
                        <ul>
                            <?php foreach ($j['siapkan'] as $butir): ?>
                                <li><?= e($butir) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <h4>Contoh judul</h4>
                        <ul class="buku-contoh">
                            <?php foreach ($j['contoh'] as $contoh): ?>
                                <li><?= e($contoh) ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="buku-aksi">
                            <a href="buat.php?jenis=<?= e(urlencode($slug)) ?>" class="btn-primary">
                                Tulis artikel <?= e($j['nama']) ?>
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </div>

    </section>


    <section class="riwayat-section">

        <div class="pilih-judul">
            <h2>Artikel kamu</h2>
            <p>Yang sudah dibuat, dari yang terbaru.</p>
        </div>

        <?php if (!$riwayat): ?>

            <div class="riwayat-kosong">
                <p>Belum ada artikel. Pilih satu jenis di atas, isi formnya, dan hasilnya akan muncul di sini.</p>
            </div>

        <?php else: ?>

            <ul class="riwayat">

                <?php foreach ($riwayat as $r): ?>

                    <?php [$labelStatus, $kelasStatus] = labelStatus($r['status']); ?>

                    <li>
                        <a href="artikel.php?id=<?= (int) $r['id'] ?>" class="riwayat-baris">

                            <span class="riwayat-utama">
                                <span class="riwayat-judul">
                                    <?= $r['judul'] ? e($r['judul']) : 'Artikel ' . e($r['jenis_nama']) . ' (belum ada judul)' ?>
                                </span>
                                <span class="riwayat-meta">
                                    <?= e($r['jenis_nama']) ?>, <?= e(tanggalIndo($r['dibuat_pada'])) ?>
                                </span>
                            </span>

                            <span class="status <?= e($kelasStatus) ?>"><?= e($labelStatus) ?></span>

                        </a>
                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>

    </section>


    <section class="dashboard-note">

        <div class="note-icon">💛</div>

        <div>

            <strong>Setiap cerita punya arti.</strong>

            <p>
                Ceritakan pengalaman sederhana dari sekolahmu.
                Siapa tahu bisa menginspirasi sekolah lain.
            </p>

        </div>

    </section>


</main>


<footer class="footer">

    <p>
        © <?= date('Y') ?> EduStory
        · Cerita sekolah, jadi lebih mudah.
    </p>

</footer>


<script src="assets/js/dashboard.js"></script>

</body>

</html>
