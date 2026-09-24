<?php

/*
|--------------------------------------------------------------------------
| Foto artikel
|--------------------------------------------------------------------------
|
| Vercel tidak menyimpan file permanen, jadi foto disimpan di database
| (tabel artikel_gambar) setelah dikecilkan, lalu ditampilkan lewat
| gambar.php. Cara ini juga memberi alamat gambar yang bisa dibuka
| klipaa saat nanti tersambung lewat API.
|
*/

const FOTO_MAKS_JUMLAH   = 3;
const FOTO_MAKS_UNGGAH   = 12 * 1024 * 1024;   // batas file yang diterima
const FOTO_MAKS_SISI     = 1600;               // piksel, sisi terpanjang
const FOTO_MAKS_TANPA_GD = 2 * 1024 * 1024;    // bila GD tidak tersedia


function olahFoto(string $lokasi, string $mime): array
{
    if (!function_exists('imagecreatetruecolor')) {

        if (filesize($lokasi) > FOTO_MAKS_TANPA_GD) {
            throw new RuntimeException('Foto terlalu besar.');
        }

        return [file_get_contents($lokasi), $mime];
    }

    switch ($mime) {
        case 'image/jpeg':
            $gambar = @imagecreatefromjpeg($lokasi);
            break;
        case 'image/png':
            $gambar = @imagecreatefrompng($lokasi);
            break;
        case 'image/webp':
            $gambar = function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($lokasi)
                : false;
            break;
        default:
            $gambar = false;
    }

    if (!$gambar) {
        throw new RuntimeException('Foto tidak bisa dibaca.');
    }

    // Putar sesuai arah foto dari kamera HP.
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {

        $exif = @exif_read_data($lokasi);
        $arah = $exif['Orientation'] ?? 1;

        $sudut = [3 => 180, 6 => -90, 8 => 90][$arah] ?? 0;

        if ($sudut !== 0) {
            $diputar = imagerotate($gambar, $sudut, 0);
            if ($diputar) {
                $gambar = $diputar;
            }
        }
    }

    $lebar  = imagesx($gambar);
    $tinggi = imagesy($gambar);
    $skala  = min(1, FOTO_MAKS_SISI / max($lebar, $tinggi));

    $lebarBaru  = max(1, (int) round($lebar * $skala));
    $tinggiBaru = max(1, (int) round($tinggi * $skala));

    // Latar putih supaya PNG transparan tidak menjadi hitam.
    $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);
    imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
    imagecopyresampled($kanvas, $gambar, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);

    ob_start();
    imagejpeg($kanvas, null, 82);
    $data = ob_get_clean();

    imagedestroy($gambar);
    imagedestroy($kanvas);

    return [$data, 'image/jpeg'];
}


/*
| Simpan foto dari $_FILES['foto'] ke tabel artikel_gambar.
| Mengembalikan jumlah foto yang tersimpan dan daftar masalah.
*/
function simpanFotoUnggahan(mysqli $k, int $artikelId, ?array $berkas): array
{
    $hasil = ['tersimpan' => 0, 'masalah' => []];

    if (!$berkas || !isset($berkas['name']) || !is_array($berkas['name'])) {
        return $hasil;
    }

    $izin  = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $urutan = 0;

    foreach ($berkas['name'] as $i => $nama) {

        if ($urutan >= FOTO_MAKS_JUMLAH) {
            break;
        }

        $galat = $berkas['error'][$i] ?? UPLOAD_ERR_NO_FILE;

        if ($galat === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($galat !== UPLOAD_ERR_OK) {
            $hasil['masalah'][] = "Foto \"$nama\" gagal diunggah (kode $galat).";
            continue;
        }

        $tmp = $berkas['tmp_name'][$i];

        if (!is_uploaded_file($tmp) || filesize($tmp) > FOTO_MAKS_UNGGAH) {
            $hasil['masalah'][] = "Foto \"$nama\" terlalu besar.";
            continue;
        }

        $mime = $finfo->file($tmp);

        if (!in_array($mime, $izin, true)) {
            $hasil['masalah'][] = "Foto \"$nama\" bukan JPG, PNG, atau WebP.";
            continue;
        }

        try {

            [$data, $mimeBaru] = olahFoto($tmp, $mime);

            $token = bin2hex(random_bytes(16));

            $stmt = $k->prepare(
                "INSERT INTO artikel_gambar (artikel_id, token, mime, data, urutan)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param("isssi", $artikelId, $token, $mimeBaru, $data, $urutan);
            $stmt->execute();
            $stmt->close();

            $urutan++;
            $hasil['tersimpan']++;

        } catch (Throwable $e) {

            error_log('Foto gagal disimpan: ' . $e->getMessage());
            $hasil['masalah'][] = "Foto \"$nama\" tidak bisa diproses.";
        }
    }

    return $hasil;
}
