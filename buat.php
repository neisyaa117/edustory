<?php

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/gambar.php';

$userId = wajibLogin();

$slug = (string) ($_GET['jenis'] ?? '');
$daftarJenis = daftarJenisArtikel();

if (!isset($daftarJenis[$slug])) {
    header("Location: dashboard.php");
    exit;
}

$jenis   = $daftarJenis[$slug];
$sekolah = ambilSekolah($koneksi, $userId);

if (!$sekolah) {
    header("Location: profil-sekolah.php");
    exit;
}

/* Isian sebelumnya, bila form dikembalikan karena ada yang kurang. */
$lama  = $_SESSION['form_lama'][$slug] ?? [];
$galat = $_SESSION['form_galat'] ?? '';

unset($_SESSION['form_lama'][$slug], $_SESSION['form_galat']);


function tampilkanField(array $f, $nilaiLama): void
{
    $nama   = $f['nama'];
    $id     = 'f-' . $nama;
    $tipe   = $f['tipe'];
    $wajib  = !empty($f['wajib']);
    $petunjuk = $f['placeholder'] ?? '';

    echo '<div class="form-group"><div class="label-baris">';

    if ($tipe === 'pilihan' || $tipe === 'banyak') {
        echo '<label id="' . e($id) . '-label">' . e($f['label']);
    } else {
        echo '<label for="' . e($id) . '">' . e($f['label']);
    }

    if ($wajib) {
        echo ' <span class="wajib" title="Wajib diisi">*</span>';
    }

    echo '</label>';

    if (!empty($f['bantuan'])) {
        echo '<button type="button" class="tanya" aria-expanded="false" aria-label="Lihat penjelasan">'
           . '<i class="fa-regular fa-circle-question" aria-hidden="true"></i></button></div>'
           . '<p class="bantuan tanya-isi" hidden>' . e($f['bantuan']) . '</p>';
    } else {
        echo '</div>';
    }

    switch ($tipe) {

        case 'teks':
            echo '<input type="text" id="' . e($id) . '" name="isi[' . e($nama) . ']"'
               . ' value="' . e((string) $nilaiLama) . '"'
               . ' maxlength="200"'
               . ($petunjuk !== '' ? ' placeholder="' . e($petunjuk) . '"' : '')
               . ($wajib ? ' required' : '') . '>';
            break;

        case 'panjang':
            echo '<textarea id="' . e($id) . '" name="isi[' . e($nama) . ']" rows="4" maxlength="2000"'
               . ($petunjuk !== '' ? ' placeholder="' . e($petunjuk) . '"' : '')
               . ($wajib ? ' required' : '') . '>' . e((string) $nilaiLama) . '</textarea>';
            break;

        case 'tanggal':
            echo '<input type="date" id="' . e($id) . '" name="isi[' . e($nama) . ']"'
               . ' value="' . e((string) $nilaiLama) . '"'
               . ' max="' . date('Y-m-d') . '"'
               . ($wajib ? ' required' : '') . '>';
            break;

        case 'menu':
            echo '<select id="' . e($id) . '" name="isi[' . e($nama) . ']"' . ($wajib ? ' required' : '') . '>';
            echo '<option value="">Pilih salah satu</option>';
            foreach (opsiField($f) as $nilai => $tampil) {
                echo '<option value="' . e($nilai) . '"' . ((string) $nilaiLama === (string) $nilai ? ' selected' : '') . '>'
                   . e($tampil) . '</option>';
            }
            echo '</select>';
            break;

        case 'pilihan':
            echo '<div class="pilihan" role="radiogroup" aria-labelledby="' . e($id) . '-label">';
            foreach (opsiField($f) as $nilai => $tampil) {
                echo '<label class="pilihan-item">'
                   . '<input type="radio" name="isi[' . e($nama) . ']" value="' . e($nilai) . '"'
                   . ((string) $nilaiLama === (string) $nilai ? ' checked' : '')
                   . ($wajib ? ' required' : '') . '>'
                   . '<span>' . e($tampil) . '</span></label>';
            }
            echo '</div>';
            break;

        case 'banyak':
            $terpilih = is_array($nilaiLama) ? $nilaiLama : [];
            echo '<div class="pilihan pilihan-banyak" role="group" aria-labelledby="' . e($id) . '-label"'
               . ($wajib ? ' data-wajib="1"' : '') . '>';
            foreach (opsiField($f) as $nilai => $tampil) {
                echo '<label class="pilihan-item">'
                   . '<input type="checkbox" name="isi[' . e($nama) . '][]" value="' . e($nilai) . '"'
                   . (in_array($nilai, $terpilih, true) ? ' checked' : '') . '>'
                   . '<span>' . e($tampil) . '</span></label>';
            }
            echo '</div>';
            break;
    }

    echo '</div>';
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tulis <?= e($jenis['nama']) ?> - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/artikel.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/css/tanya.css">
</head>


<body class="profile-page">


<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <a href="dashboard.php" class="back-home">← Kembali ke beranda</a>

    </div>

</nav>


<main class="profile-wrapper">


    <div class="profile-intro">

        <div class="profile-badge"><i class="fa-solid <?= e($jenis['ikon']) ?>" aria-hidden="true"></i></div>

        <h1><?= e($jenis['nama']) ?></h1>

        <p><?= e($jenis['deskripsi']) ?></p>

        <p class="buat-sekolah">Untuk <strong><?= e($sekolah['nama_sekolah']) ?></strong></p>

    </div>


    <?php if ($galat): ?>
        <div class="pemberitahuan pemberitahuan-merah" role="alert"><?= e($galat) ?></div>
    <?php endif; ?>


    <div class="profile-card">

        <form
            action="proses/proses-artikel.php"
            method="POST"
            enctype="multipart/form-data"
            id="formArtikel"
        >

            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="jenis" value="<?= e($slug) ?>">


            <?php foreach ($jenis['bagian'] as $urut => $bagian): ?>

                <div class="form-section">

                    <div class="form-section-title">

                        <span><?= str_pad((string) ($urut + 1), 2, '0', STR_PAD_LEFT) ?></span>

                        <div>
                            <div class="label-baris">
                                <h2><?= e($bagian['judul']) ?></h2>
                                <button type="button" class="tanya" aria-expanded="false" aria-label="Lihat penjelasan">
                                    <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="tanya-isi" hidden><?= e($bagian['info']) ?></p>
                        </div>

                    </div>

                    <?php foreach ($bagian['fields'] as $field): ?>
                        <?php tampilkanField($field, $lama[$field['nama']] ?? ''); ?>
                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>


            <div class="form-section">

                <div class="form-section-title">

                    <span><?= str_pad((string) (count($jenis['bagian']) + 1), 2, '0', STR_PAD_LEFT) ?></span>

                    <div>
                        <h2><?= e($jenis['foto_label'] ?? 'Foto pendukung') ?></h2>
                        <p>Boleh dikosongkan. Sampai <?= FOTO_MAKS_JUMLAH ?> foto, ukurannya dikecilkan otomatis.</p>
                    </div>

                </div>

                <div class="foto-unggah" id="fotoUnggah">

                    <input
                        type="file"
                        id="foto"
                        name="foto[]"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        data-maks="<?= FOTO_MAKS_JUMLAH ?>"
                    >

                    <label for="foto" class="foto-pilih">
                        <strong>Pilih foto</strong>
                        <span>JPG, PNG, atau WebP</span>
                    </label>

                    <ul class="foto-pratinjau" id="fotoPratinjau"></ul>

                    <p class="foto-pesan" id="fotoPesan" role="status"></p>

                </div>

            </div>


            <div class="save-info">

                <span>💡</span>

                <p>
                    Setelah dikirim, artikel ditulis di halaman berikutnya.
                    Biasanya butuh sekitar setengah menit.
                </p>

            </div>


            <button type="submit" class="btn-primary btn-full" id="btnKirim">
                Buat artikel
            </button>

        </form>

    </div>

</main>


<footer class="footer">

    <p>© <?= date('Y') ?> EduStory</p>

</footer>


<script src="assets/js/buat.js"></script>

<script src="assets/js/tanya.js"></script>
</body>

</html>
