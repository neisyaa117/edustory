<?php

require_once __DIR__ . '/lib/helpers.php';

$userId = wajibLogin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $koneksi->prepare("SELECT * FROM artikel WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$artikel = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$artikel) {
    header("Location: dashboard.php");
    exit;
}

$sekolah = ambilSekolah($koneksi, $userId) ?: [];

$stmt = $koneksi->prepare(
    "SELECT id, token FROM artikel_gambar WHERE artikel_id = ? ORDER BY urutan, id"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$fotoDaftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$info = $_SESSION['info_artikel'][$id] ?? '';
unset($_SESSION['info_artikel'][$id]);

$status   = $artikel['status'];
$selesai  = $status === 'selesai' && !empty($artikel['isi']);

/* Status "menulis" yang sudah terlalu lama dianggap macet dan boleh diulang. */
$macet = false;

if ($status === 'menulis' && !empty($artikel['diperbarui_pada'])) {
    $macet = (time() - strtotime($artikel['diperbarui_pada'])) > 4 * 60;
}

$sedangMenulis = $status === 'menulis' && !$macet;

$sisaCoba = MAKS_PERCOBAAN_TULIS - (int) $artikel['jumlah_tulis'];

$lokasi = array_filter([
    !empty($sekolah['desa'])      ? 'Desa/Kel. ' . rapikanWilayah($sekolah['desa']) : '',
    !empty($sekolah['kecamatan']) ? 'Kec. ' . rapikanWilayah($sekolah['kecamatan']) : '',
    !empty($sekolah['kabupaten']) ? rapikanWilayah($sekolah['kabupaten']) : '',
]);

$kodeDesa  = $sekolah['kode_desa'] ?? '';
$linkKlipaa = klipaaLinkArtikel($kodeDesa);

$judulHalaman = $selesai ? $artikel['judul'] : 'Artikel ' . $artikel['jenis_nama'];

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($judulHalaman) ?> - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/artikel.css">

</head>


<body class="artikel-page">


<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <a href="dashboard.php" class="back-home">← Kembali ke beranda</a>

    </div>

</nav>


<main class="artikel-wrap">


<?php if ($selesai): ?>


    <?php if ($info): ?>
        <div class="pemberitahuan"><?= e($info) ?></div>
    <?php endif; ?>


    <div class="artikel-tata">


        <article class="lembar">

            <span class="lembar-pin" aria-hidden="true"></span>

            <header class="lembar-kepala">

                <p class="lembar-jenis"><?= e($artikel['jenis_nama']) ?></p>

                <h1 id="judulArtikel"><?= e($artikel['judul']) ?></h1>

                <div class="lembar-meta">
                    <strong><?= e($sekolah['nama_sekolah'] ?? '') ?></strong>
                    <?php if ($lokasi): ?>
                        <span><?= e(implode(', ', $lokasi)) ?></span>
                    <?php endif; ?>
                    <span><?= e(tanggalIndo($artikel['dibuat_pada'])) ?></span>
                </div>

            </header>


            <?php if ($fotoDaftar): ?>

                <figure class="foto-utama">
                    <img
                        src="gambar.php?t=<?= e($fotoDaftar[0]['token']) ?>"
                        alt="Foto pendukung artikel <?= e($artikel['judul']) ?>"
                    >
                </figure>

            <?php endif; ?>


            <div class="isi-artikel" id="isiArtikel">
                <?= renderIsiArtikel($artikel['isi']) ?>
            </div>


            <?php if (count($fotoDaftar) > 1): ?>

                <div class="foto-lain">
                    <?php foreach (array_slice($fotoDaftar, 1) as $urut => $foto): ?>
                        <figure>
                            <img
                                src="gambar.php?t=<?= e($foto['token']) ?>"
                                alt="Foto pendukung <?= $urut + 2 ?> artikel <?= e($artikel['judul']) ?>"
                                loading="lazy"
                            >
                        </figure>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </article>


        <aside class="aksi" aria-label="Pindahkan artikel">

            <h2>Pindahkan ke klipaa</h2>
            <p class="aksi-catatan">Untuk sementara, salin dulu lalu tempel di klipaa. Sambungan otomatis belum tersedia.</p>

            <div class="aksi-tombol">

                <button type="button" class="btn-primary" data-salin="isi">
                    Salin isi artikel
                </button>

                <button type="button" class="btn-garis" data-salin="judul">
                    Salin judul
                </button>

            </div>

            <?php if ($fotoDaftar): ?>

                <div class="aksi-blok">

                    <h3>Foto</h3>

                    <ul class="aksi-foto">
                        <?php foreach ($fotoDaftar as $urut => $foto): ?>
                            <li>
                                <a href="gambar.php?t=<?= e($foto['token']) ?>&amp;unduh=1" download>
                                    Unduh foto <?= $urut + 1 ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>

            <?php endif; ?>

            <div class="aksi-blok">

                <h3>Kode desa</h3>

                <?php if ($kodeDesa): ?>

                    <p class="kode-desa">
                        <code id="kodeDesa"><?= e($kodeDesa) ?></code>
                        <button type="button" class="btn-kecil" data-salin="kode">Salin</button>
                    </p>

                <?php else: ?>

                    <p class="aksi-catatan">
                        Belum tersimpan. Pilih ulang wilayah di
                        <a href="profil-sekolah.php">data sekolah</a>.
                    </p>

                <?php endif; ?>

                <?php if ($linkKlipaa): ?>

                    <a
                        href="<?= e($linkKlipaa) ?>"
                        class="btn-garis btn-blok"
                        target="_blank"
                        rel="noopener"
                    >
                        Buka menu artikel di klipaa
                    </a>

                <?php endif; ?>

            </div>

            <div class="aksi-blok aksi-lanjut">

                <a href="dashboard.php" class="btn-garis btn-blok">Kembali ke beranda</a>

                <?php if ($sisaCoba > 0): ?>
                    <button
                        type="button"
                        class="btn-teks"
                        id="btnUlang"
                        data-id="<?= (int) $id ?>"
                        data-csrf="<?= e(csrfToken()) ?>"
                    >
                        Tulis ulang artikel ini
                    </button>
                <?php endif; ?>

            </div>

            <p class="aksi-pesan" id="aksiPesan" role="status"></p>

        </aside>


    </div>


<?php else: ?>


    <section
        class="menulis"
        id="menulis"
        data-id="<?= (int) $id ?>"
        data-csrf="<?= e(csrfToken()) ?>"
        data-status="<?= $sedangMenulis ? 'menulis' : 'mulai' ?>"
        data-gagal="<?= $status === 'gagal' ? '1' : '0' ?>"
        data-pesan="<?= e(pesanGalatPengguna($artikel['pesan_error'])) ?>"
    >

        <div class="menulis-garis" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>

        <h1 id="menulisJudul">Artikel <?= e($artikel['jenis_nama']) ?> sedang ditulis</h1>

        <p id="menulisTeks">
            Biasanya butuh sekitar setengah menit. Tetap di halaman ini; hasilnya muncul sendiri.
        </p>

        <div class="menulis-galat" id="menulisGalat" role="alert" hidden>
            <p id="menulisGalatTeks"></p>
            <button type="button" class="btn-primary" id="btnCoba">Coba lagi</button>
        </div>

        <p class="menulis-catatan">
            Kalau halaman tertutup, isianmu tetap aman. Artikel ini akan ada di
            <a href="dashboard.php">beranda</a>.
        </p>

    </section>


<?php endif; ?>


</main>


<footer class="footer">

    <p>© <?= date('Y') ?> EduStory</p>

</footer>


<script src="assets/js/artikel.js"></script>

</body>

</html>
