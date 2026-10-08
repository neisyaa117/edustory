<?php

/*
|--------------------------------------------------------------------------
| Kode wilayah Kemendagri dari nama wilayah
|--------------------------------------------------------------------------
|
| Server mencari sendiri kode wilayah (Kemendagri, tanpa titik) berdasarkan
| nama provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan, memakai data
| di assets/wilayah/. Dengan begitu kode yang tersimpan selalu benar, tidak
| bergantung pada browser, dan akun lama yang masih menyimpan kode lama
| (kode BPS dari layanan sebelumnya) diperbaiki otomatis.
|
| Contoh: DESA CIKEMBULAN, KADUNGORA, KABUPATEN GARUT, JAWA BARAT
|         => 32, 3205, 320510, 3205102003
|
*/

/* Samakan penulisan nama supaya nama lama dan nama baru bisa dicocokkan. */
function normalNamaWilayah(string $nama): string
{
    $nama = mb_strtoupper(trim($nama), 'UTF-8');

    $nama = str_replace(
        ['DKI JAKARTA', 'DI YOGYAKARTA', 'D.I. YOGYAKARTA', 'DAERAH KHUSUS JAKARTA'],
        ['DAERAH KHUSUS IBUKOTA JAKARTA', 'DAERAH ISTIMEWA YOGYAKARTA', 'DAERAH ISTIMEWA YOGYAKARTA', 'DAERAH KHUSUS IBUKOTA JAKARTA'],
        $nama
    );

    $nama = preg_replace('/\bKAB\.?(?=\s)/u', 'KABUPATEN', $nama);
    $nama = preg_replace('/\bKEP\.?(?=\s)/u', 'KEPULAUAN', $nama);
    $nama = preg_replace('/\bADM\.?(?=\s|$)/u', 'ADMINISTRASI', $nama);

    $nama = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $nama);

    return trim(preg_replace('/\s+/u', ' ', $nama));
}

function bacaBerkasWilayah(string $berkas): ?array
{
    if (!is_file($berkas)) {
        return null;
    }

    $isi = json_decode((string) file_get_contents($berkas), true);

    return is_array($isi) ? $isi : null;
}

/* Cari satu pasangan [kode, nama, ...] yang namanya cocok. */
function cariWilayah(array $daftar, string $nama): ?array
{
    $dicari = normalNamaWilayah($nama);

    if ($dicari === '') {
        return null;
    }

    foreach ($daftar as $baris) {
        if (normalNamaWilayah((string) $baris[1]) === $dicari) {
            return $baris;
        }
    }

    return null;
}

/*
| Mengembalikan kode yang berhasil dicocokkan, misalnya
| ['provinsi' => '32', 'kabupaten' => '3205', 'kecamatan' => '320510', 'desa' => '3205102003'].
| Berhenti di tingkat pertama yang namanya tidak ditemukan.
*/
function kodeWilayahDariNama(string $provinsi, string $kabupaten, string $kecamatan, string $desa): array
{
    $dir   = __DIR__ . '/../assets/wilayah';
    $hasil = [];

    $indeks = bacaBerkasWilayah($dir . '/index.json');

    if (!$indeks) {
        return $hasil;
    }

    $prov = cariWilayah($indeks, $provinsi);

    if (!$prov) {
        return $hasil;
    }

    $hasil['provinsi'] = $prov[0];

    $kab = cariWilayah($prov[2], $kabupaten);

    if (!$kab) {
        return $hasil;
    }

    $hasil['kabupaten'] = $kab[0];

    if (!preg_match('/^[0-9]{4}$/', $kab[0])) {
        return $hasil;
    }

    $isiKab = bacaBerkasWilayah($dir . '/kab/' . $kab[0] . '.json');

    if (!$isiKab) {
        return $hasil;
    }

    $kec = cariWilayah($isiKab, $kecamatan);

    if (!$kec) {
        return $hasil;
    }

    $hasil['kecamatan'] = $kec[0];

    $des = cariWilayah($kec[2], $desa);

    if ($des) {
        $hasil['desa'] = $des[0];
    }

    return $hasil;
}

/*
| Kode Kemendagri: provinsi 2 angka, kabupaten/kota 4, kecamatan 6, desa 10.
| Kode lama (BPS) memakai 7 angka untuk kecamatan.
*/
function kodeSekolahSudahBenar(array $s): bool
{
    return strlen((string) ($s['kode_provinsi'] ?? '')) === 2
        && strlen((string) ($s['kode_kabupaten'] ?? '')) === 4
        && strlen((string) ($s['kode_kecamatan'] ?? '')) === 6
        && strlen((string) ($s['kode_desa'] ?? '')) === 10;
}

/*
| Kebalikan dari kodeWilayahDariNama(): dari kode Kemendagri, cari nama dan
| kode di setiap tingkat di atasnya. Dipakai saat kolom nama (provinsi,
| kabupaten, kecamatan, atau desa) ternyata berisi kode, bukan nama -- ini
| bisa terjadi pada data lama yang sempat tersimpan salah.
|
| $tingkat: 'provinsi' | 'kabupaten' | 'kecamatan' | 'desa'
| Mengembalikan baris nama+kode selengkap mungkin, atau [] bila kode tidak
| ditemukan di data.
*/
function namaWilayahDariKode(string $tingkat, string $kodeAwal): array
{
    $dir   = __DIR__ . '/../assets/wilayah';
    $kode  = preg_replace('/[^0-9]/', '', $kodeAwal);
    $hasil = [];

    if ($kode === '') {
        return $hasil;
    }

    $indeks = bacaBerkasWilayah($dir . '/index.json');

    if (!$indeks) {
        return $hasil;
    }

    foreach ($indeks as $prov) {

        if ($tingkat === 'provinsi' && $prov[0] === $kode) {
            $hasil['provinsi'] = ['kode' => $prov[0], 'nama' => $prov[1]];
            return $hasil;
        }

        if (!str_starts_with($kode, $prov[0]) || $tingkat === 'provinsi') {
            continue;
        }

        foreach ($prov[2] as $kab) {

            if ($tingkat === 'kabupaten' && $kab[0] === $kode) {
                $hasil['provinsi']  = ['kode' => $prov[0], 'nama' => $prov[1]];
                $hasil['kabupaten'] = ['kode' => $kab[0], 'nama' => $kab[1]];
                return $hasil;
            }

            if ($tingkat === 'kabupaten' || !str_starts_with($kode, $kab[0])) {
                continue;
            }

            $isiKab = bacaBerkasWilayah($dir . '/kab/' . $kab[0] . '.json');

            if (!$isiKab) {
                continue;
            }

            foreach ($isiKab as $kec) {

                if ($tingkat === 'kecamatan' && $kec[0] === $kode) {
                    $hasil['provinsi']  = ['kode' => $prov[0], 'nama' => $prov[1]];
                    $hasil['kabupaten'] = ['kode' => $kab[0], 'nama' => $kab[1]];
                    $hasil['kecamatan'] = ['kode' => $kec[0], 'nama' => $kec[1]];
                    return $hasil;
                }

                if ($tingkat !== 'desa' || !str_starts_with($kode, $kec[0])) {
                    continue;
                }

                foreach ($kec[2] as $desa) {
                    if ($desa[0] === $kode) {
                        $hasil['provinsi']  = ['kode' => $prov[0], 'nama' => $prov[1]];
                        $hasil['kabupaten'] = ['kode' => $kab[0], 'nama' => $kab[1]];
                        $hasil['kecamatan'] = ['kode' => $kec[0], 'nama' => $kec[1]];
                        $hasil['desa']      = ['kode' => $desa[0], 'nama' => $desa[1]];
                        return $hasil;
                    }
                }
            }
        }
    }

    return [];
}

/*
| Benarkah $nilai sebenarnya sebuah kode wilayah, bukan nama tempat?
| Nama wilayah di Indonesia tidak pernah berupa angka semata.
*/
function sepertiKodeWilayah(string $nilai): bool
{
    return ctype_digit(trim($nilai)) && trim($nilai) !== '';
}

/*
| Perbaiki kode wilayah sekolah yang masih kosong atau memakai kode lama.
| Bila kolom nama (provinsi/kabupaten/kecamatan/desa) ternyata berisi kode
| wilayah -- bekas data yang tersimpan salah pada versi aplikasi sebelumnya --
| nama dan kode di setiap tingkat ditulis ulang dari data Kemendagri.
| Tidak pernah membuat halaman gagal: bila ada masalah, data dikembalikan apa adanya.
*/
function perbaikiKodeWilayah(mysqli $k, array $s): array
{
    if (empty($s['id'])) {
        return $s;
    }

    try {

        $namaBaru = [];

        // Kolom nama yang ternyata berisi angka: tulis ulang dari kodenya.
        foreach (['desa', 'kecamatan', 'kabupaten', 'provinsi'] as $tingkat) {

            $nilai = (string) ($s[$tingkat] ?? '');

            if ($nilai === '' || !sepertiKodeWilayah($nilai)) {
                continue;
            }

            $temuan = namaWilayahDariKode($tingkat, $nilai);

            if ($temuan) {
                $namaBaru = $temuan;
                break;
            }
        }

        if ($namaBaru) {

            foreach ($namaBaru as $tingkat => $baris) {
                $s[$tingkat]            = $baris['nama'];
                $s['kode_' . $tingkat]  = $baris['kode'];
            }

            // Kode diperbaiki sampai tingkat tertentu, tapi desa/kelurahan
            // belum ikut terpecahkan (mis. yang salah ada di kecamatan,
            // sedangkan nama desanya sendiri sudah benar). Lengkapi lewat
            // jalur nama -> kode seperti biasa, memakai nama yang sudah dibetulkan.
            if (!isset($namaBaru['desa']) && !sepertiKodeWilayah((string) ($s['desa'] ?? ''))) {

                $lanjut = kodeWilayahDariNama(
                    (string) $s['provinsi'],
                    (string) $s['kabupaten'],
                    (string) $s['kecamatan'],
                    (string) ($s['desa'] ?? '')
                );

                if (isset($lanjut['desa'])) {
                    $s['kode_desa'] = $lanjut['desa'];
                }
            }

            $stmt = $k->prepare(
                "UPDATE sekolah
                 SET provinsi = ?, kabupaten = ?, kecamatan = ?, desa = ?,
                     kode_provinsi = ?, kode_kabupaten = ?, kode_kecamatan = ?, kode_desa = ?
                 WHERE id = ?"
            );

            $id = (int) $s['id'];

            $stmt->bind_param(
                "ssssssssi",
                $s['provinsi'], $s['kabupaten'], $s['kecamatan'], $s['desa'],
                $s['kode_provinsi'], $s['kode_kabupaten'], $s['kode_kecamatan'], $s['kode_desa'],
                $id
            );

            $stmt->execute();
            $stmt->close();

            return $s;
        }

        if (kodeSekolahSudahBenar($s) || empty($s['desa'])) {
            return $s;
        }

        // Nama sudah benar, tinggal kode_* yang kosong atau kode lama (BPS).
        $kode = kodeWilayahDariNama(
            (string) ($s['provinsi'] ?? ''),
            (string) ($s['kabupaten'] ?? ''),
            (string) ($s['kecamatan'] ?? ''),
            (string) ($s['desa'] ?? '')
        );

        if (count($kode) < 4) {
            return $s;
        }

        $stmt = $k->prepare(
            "UPDATE sekolah
             SET kode_provinsi = ?, kode_kabupaten = ?, kode_kecamatan = ?, kode_desa = ?
             WHERE id = ?"
        );

        $id = (int) $s['id'];

        $stmt->bind_param(
            "ssssi",
            $kode['provinsi'],
            $kode['kabupaten'],
            $kode['kecamatan'],
            $kode['desa'],
            $id
        );

        $stmt->execute();
        $stmt->close();

        $s['kode_provinsi']  = $kode['provinsi'];
        $s['kode_kabupaten'] = $kode['kabupaten'];
        $s['kode_kecamatan'] = $kode['kecamatan'];
        $s['kode_desa']      = $kode['desa'];

    } catch (Throwable $e) {

        error_log('Kode wilayah sekolah gagal diperbarui: ' . $e->getMessage());
    }

    return $s;
}
