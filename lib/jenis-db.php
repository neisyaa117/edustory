<?php

/*
|--------------------------------------------------------------------------
| Jenis artikel yang bisa dikelola dari halaman admin (CRUD)
|--------------------------------------------------------------------------
|
| config/jenis-artikel.php TIDAK diubah dan tetap jadi daftar bawaan.
| Tabel jenis_artikel_db hanya menyimpan "catatan tambahan" di atasnya:
|
|   - slug yang ada di config + data terisi  -> bawaan yang sudah DIUBAH
|   - slug yang ada di config + aktif = 0    -> bawaan yang DIHAPUS (disembunyikan)
|   - slug yang tidak ada di config          -> jenis BARU buatan admin
|
| Menghapus catatan (reset) mengembalikan jenis bawaan ke isi config.
*/

const JENIS_TIPE  = ['teks', 'panjang', 'pilihan', 'banyak', 'menu', 'tanggal'];
const JENIS_WARNA = ['purple-bg', 'orange-bg', 'green-bg', 'yellow-bg', 'blue-bg', 'coral-bg', 'pink-bg'];

const JENIS_TIPE_LABEL = [
    'teks'    => 'Teks satu baris',
    'panjang' => 'Teks panjang',
    'pilihan' => 'Pilih satu',
    'banyak'  => 'Pilih beberapa',
    'menu'    => 'Menu dropdown',
    'tanggal' => 'Tanggal',
];

const JENIS_WARNA_LABEL = [
    'purple-bg' => 'Hijau muda',
    'orange-bg' => 'Jingga',
    'green-bg'  => 'Hijau',
    'yellow-bg' => 'Kuning',
    'blue-bg'   => 'Biru kehijauan',
    'coral-bg'  => 'Koral',
    'pink-bg'   => 'Merah muda',
];


/* Buat tabelnya kalau belum ada. Gagal? Diam saja, daftar bawaan tetap jalan. */
function jenisTabelSiap(mysqli $k): bool
{
    static $siap = null;

    if ($siap !== null) {
        return $siap;
    }

    try {
        $k->query(
            "CREATE TABLE IF NOT EXISTS jenis_artikel_db (
                slug            VARCHAR(40) NOT NULL,
                aktif           TINYINT(1)  NOT NULL DEFAULT 1,
                data            MEDIUMTEXT  NULL,
                dibuat_pada     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                diperbarui_pada DATETIME    NULL,
                PRIMARY KEY (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $siap = true;
    } catch (Throwable $e) {
        error_log('Tabel jenis_artikel_db tidak bisa disiapkan: ' . $e->getMessage());
        $siap = false;
    }

    return $siap;
}

/* Semua catatan di tabel: slug => ['aktif' => bool, 'data' => array|null] */
function jenisBacaCatatan(mysqli $k): array
{
    // Tabel dibuat oleh admin.php (jenisTabelSiap). Sebelum itu, halaman guru
    // cukup memakai config: tidak ada perintah CREATE pada setiap kunjungan.
    $hasil = [];

    try {
        $res = $k->query("SELECT slug, aktif, data FROM jenis_artikel_db ORDER BY dibuat_pada, slug");

        foreach ($res->fetch_all(MYSQLI_ASSOC) as $b) {
            $data = null;

            if ($b['data'] !== null && $b['data'] !== '') {
                $dekode = json_decode($b['data'], true);
                $data = is_array($dekode) ? $dekode : null;
            }

            $hasil[$b['slug']] = ['aktif' => (int) $b['aktif'] === 1, 'data' => $data];
        }
    } catch (Throwable $e) {
        // 1146 = tabel belum ada. Itu normal sebelum admin membuka tab Jenis artikel.
        if ((int) $e->getCode() !== 1146) {
            error_log('Gagal membaca jenis_artikel_db: ' . $e->getMessage());
        }
    }

    return $hasil;
}

/*
| Gabungkan config bawaan dengan catatan di database.
| $semua = true  -> sertakan yang nonaktif (dipakai admin & proses generate
|                   supaya artikel lama dari jenis yang dihapus tetap bisa ditulis ulang).
| $semua = false -> hanya yang aktif (dashboard guru, form buat artikel).
|
| Tiap jenis diberi penanda tambahan: _sumber ('bawaan'|'kustom'),
| _diubah (bool), _aktif (bool).
*/
function gabungJenisArtikel(array $bawaan, mysqli $k, bool $semua): array
{
    $catatan = jenisBacaCatatan($k);
    $hasil   = [];

    foreach ($bawaan as $slug => $def) {
        $c = $catatan[$slug] ?? null;

        if ($c && is_array($c['data'])) {
            $def = array_merge($def, $c['data']);
        }

        $def['_sumber'] = 'bawaan';
        $def['_diubah'] = (bool) ($c && is_array($c['data']));
        $def['_aktif']  = $c ? $c['aktif'] : true;

        if ($def['_aktif'] || $semua) {
            $hasil[$slug] = $def;
        }
    }

    foreach ($catatan as $slug => $c) {
        if (isset($bawaan[$slug]) || !is_array($c['data'])) {
            continue;
        }

        $def = $c['data'];
        $def['_sumber'] = 'kustom';
        $def['_diubah'] = false;
        $def['_aktif']  = $c['aktif'];

        if ($def['_aktif'] || $semua) {
            $hasil[$slug] = $def;
        }
    }

    return $hasil;
}

/* Jumlah artikel per jenis: slug => jumlah */
function jenisJumlahArtikel(mysqli $k): array
{
    $hasil = [];

    try {
        $res = $k->query("SELECT jenis, COUNT(*) AS n FROM artikel GROUP BY jenis");

        foreach ($res->fetch_all(MYSQLI_ASSOC) as $b) {
            $hasil[$b['jenis']] = (int) $b['n'];
        }
    } catch (Throwable $e) {
    }

    return $hasil;
}


/*
|--------------------------------------------------------------------------
| Tulis ke database
|--------------------------------------------------------------------------
*/

function jenisSimpan(mysqli $k, string $slug, array $def): bool
{
    $json = json_encode($def, JSON_UNESCAPED_UNICODE);

    $stmt = $k->prepare(
        "INSERT INTO jenis_artikel_db (slug, aktif, data, diperbarui_pada)
         VALUES (?, 1, ?, NOW())
         ON DUPLICATE KEY UPDATE data = VALUES(data), diperbarui_pada = NOW()"
    );
    $stmt->bind_param("ss", $slug, $json);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function jenisSetAktif(mysqli $k, string $slug, bool $aktif): bool
{
    $nilai = $aktif ? 1 : 0;

    $stmt = $k->prepare(
        "INSERT INTO jenis_artikel_db (slug, aktif, diperbarui_pada)
         VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE aktif = VALUES(aktif), diperbarui_pada = NOW()"
    );
    $stmt->bind_param("si", $slug, $nilai);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/* Buang catatan: jenis kustom hilang total, jenis bawaan kembali ke config. */
function jenisHapusCatatan(mysqli $k, string $slug): bool
{
    $stmt = $k->prepare("DELETE FROM jenis_artikel_db WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/* Bawaan yang hanya disembunyikan (data kosong): cukup aktifkan lagi, jangan hapus catatan ubahannya. */
function jenisPulihkan(mysqli $k, string $slug): bool
{
    return jenisSetAktif($k, $slug, true);
}

/* Reset jenis bawaan ke isi config tapi pertahankan status aktif/nonaktif-nya. */
function jenisResetKeBawaan(mysqli $k, string $slug): bool
{
    $stmt = $k->prepare("UPDATE jenis_artikel_db SET data = NULL, diperbarui_pada = NOW() WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}


/*
|--------------------------------------------------------------------------
| Bantu: slug, isian form -> definisi jenis
|--------------------------------------------------------------------------
*/

function jenisBuatSlug(string $nama, array $sudahAda): string
{
    $dasar = strtolower($nama);
    $dasar = preg_replace('/[^a-z0-9]+/', '-', $dasar);
    $dasar = trim((string) $dasar, '-');
    $dasar = substr($dasar, 0, 34);

    if ($dasar === '') {
        $dasar = 'jenis';
    }

    $slug = $dasar;
    $i = 2;

    while (in_array($slug, $sudahAda, true)) {
        $slug = $dasar . '-' . $i++;
    }

    return $slug;
}

function jenisNamaField(string $label, array &$terpakai): string
{
    $dasar = strtolower($label);
    $dasar = preg_replace('/[^a-z0-9]+/', '_', $dasar);
    $dasar = trim((string) $dasar, '_');
    $dasar = substr($dasar, 0, 30);

    if ($dasar === '') {
        $dasar = 'isian';
    }

    $nama = $dasar;
    $i = 2;

    while (in_array($nama, $terpakai, true)) {
        $nama = $dasar . '_' . $i++;
    }

    $terpakai[] = $nama;

    return $nama;
}

function jenisPotong($teks, int $maks): string
{
    $teks = trim((string) $teks);
    $teks = str_replace("\r\n", "\n", $teks);

    return mb_substr($teks, 0, $maks);
}

/* Satu baris satu butir -> daftar. */
function jenisBarisKeDaftar($teks, int $maksButir, int $maksPanjang): array
{
    $hasil = [];

    foreach (preg_split('/\R/u', (string) $teks) as $baris) {
        $baris = mb_substr(trim(preg_replace('/\s+/u', ' ', $baris)), 0, $maksPanjang);

        if ($baris !== '') {
            $hasil[] = $baris;
        }
    }

    return array_slice($hasil, 0, $maksButir);
}

/* "nilai | tampilan" per baris -> daftar biasa atau pasangan nilai => tampilan. */
function jenisOpsiDariTeks($teks): array
{
    $hasil = [];
    $beda  = false;

    foreach (preg_split('/\R/u', (string) $teks) as $baris) {
        $bagian = explode('|', $baris, 2);
        $nilai  = mb_substr(trim($bagian[0]), 0, 100);
        $tampil = isset($bagian[1]) ? mb_substr(trim($bagian[1]), 0, 120) : $nilai;

        if ($nilai === '' || isset($hasil[$nilai])) {
            continue;
        }

        if ($tampil === '') {
            $tampil = $nilai;
        }

        if ($tampil !== $nilai) {
            $beda = true;
        }

        $hasil[$nilai] = $tampil;

        if (count($hasil) >= 20) {
            break;
        }
    }

    return $beda ? $hasil : array_keys($hasil);
}

/* Kebalikannya, untuk mengisi kotak teks di editor. */
function jenisOpsiKeTeks(array $field): string
{
    $baris = [];

    foreach (opsiField($field) as $nilai => $tampil) {
        $baris[] = ((string) $nilai === (string) $tampil) ? (string) $nilai : $nilai . ' | ' . $tampil;
    }

    return implode("\n", $baris);
}

/*
| Ubah kiriman form admin menjadi definisi jenis yang rapi.
| Mengembalikan [definisi, daftarGalat]. Definisi selalu dikembalikan
| (sebisanya) supaya form bisa ditampilkan ulang tanpa kehilangan isian.
*/
function jenisBersihkanKiriman(array $post): array
{
    $galat = [];

    $nama = jenisPotong($post['nama'] ?? '', 60);
    $ikon = jenisPotong(preg_replace('/[<>&"\'\s]+/u', '', (string) ($post['ikon'] ?? '')), 8);
    $warna = (string) ($post['warna'] ?? '');

    $def = [
        'nama'      => $nama,
        'ikon'      => $ikon !== '' ? $ikon : '📝',
        'warna'     => in_array($warna, JENIS_WARNA, true) ? $warna : JENIS_WARNA[0],
        'ringkas'   => jenisPotong($post['ringkas'] ?? '', 160),
        'deskripsi' => jenisPotong($post['deskripsi'] ?? '', 700),
        'cocok'     => jenisPotong($post['cocok'] ?? '', 400),
        'siapkan'   => jenisBarisKeDaftar($post['siapkan'] ?? '', 8, 160),
        'contoh'    => jenisBarisKeDaftar($post['contoh'] ?? '', 6, 160),
        'panduan'   => jenisPotong($post['panduan'] ?? '', 900),
    ];

    $fotoLabel = jenisPotong($post['foto_label'] ?? '', 60);
    $def['foto_label'] = $fotoLabel !== '' ? $fotoLabel : 'Foto pendukung';

    foreach (['nama' => 'Nama jenis', 'ringkas' => 'Kalimat ringkas', 'deskripsi' => 'Penjelasan', 'cocok' => 'Cocok untuk', 'panduan' => 'Arahan untuk AI'] as $kunci => $label) {
        if ($def[$kunci] === '') {
            $galat[] = $label . ' belum diisi.';
        }
    }

    $mentah = json_decode((string) ($post['bagian_json'] ?? '[]'), true);
    $mentah = is_array($mentah) ? array_slice($mentah, 0, 10) : [];

    $bagianBersih = [];
    $namaTerpakai = [];

    foreach ($mentah as $b) {
        if (!is_array($b)) {
            continue;
        }

        $judul = jenisPotong($b['judul'] ?? '', 80);
        $fields = [];

        foreach (array_slice(is_array($b['fields'] ?? null) ? $b['fields'] : [], 0, 15) as $f) {
            if (!is_array($f)) {
                continue;
            }

            $label = jenisPotong($f['label'] ?? '', 160);

            if ($label === '') {
                continue;
            }

            $tipe = (string) ($f['tipe'] ?? 'teks');
            $tipe = in_array($tipe, JENIS_TIPE, true) ? $tipe : 'teks';

            $namaField = (string) ($f['nama'] ?? '');

            if (!preg_match('/^[a-z0-9_]{1,40}$/', $namaField) || in_array($namaField, $namaTerpakai, true)) {
                $namaField = jenisNamaField($label, $namaTerpakai);
            } else {
                $namaTerpakai[] = $namaField;
            }

            $baris = ['nama' => $namaField, 'label' => $label, 'tipe' => $tipe];

            if (!empty($f['wajib'])) {
                $baris['wajib'] = true;
            }

            $petunjuk = jenisPotong($f['placeholder'] ?? '', 160);
            if ($petunjuk !== '' && ($tipe === 'teks' || $tipe === 'panjang')) {
                $baris['placeholder'] = $petunjuk;
            }

            $bantuan = jenisPotong($f['bantuan'] ?? '', 200);
            if ($bantuan !== '') {
                $baris['bantuan'] = $bantuan;
            }

            if (in_array($tipe, ['pilihan', 'banyak', 'menu'], true)) {
                $opsi = jenisOpsiDariTeks($f['opsi'] ?? '');

                if (count($opsi) < 2) {
                    $galat[] = 'Pertanyaan "' . $label . '" butuh minimal 2 pilihan.';
                }

                $baris['opsi'] = $opsi;
            }

            $fields[] = $baris;
        }

        if (!$fields && $judul === '') {
            continue;
        }

        if ($judul === '') {
            $galat[] = 'Ada bagian form yang belum diberi judul.';
        }

        if (!$fields) {
            $galat[] = 'Bagian "' . $judul . '" belum punya pertanyaan.';
        }

        $bagianBersih[] = [
            'judul'  => $judul,
            'info'   => jenisPotong($b['info'] ?? '', 160),
            'fields' => $fields,
        ];
    }

    if (!$bagianBersih) {
        $galat[] = 'Form butuh minimal satu bagian dengan satu pertanyaan.';
    }

    $def['bagian'] = $bagianBersih;

    return [$def, $galat];
}
