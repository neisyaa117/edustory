<?php
/*
| Halaman super admin (hanya baca): lihat semua akun, sekolah, dan artikel
| tanpa membuka phpMyAdmin. Aktif hanya bila ADMIN_PASSWORD diisi
| (config/lokal.php atau environment variable). Password akun guru tidak ditampilkan.
*/
require_once __DIR__ . '/lib/helpers.php';
mulaiSesi();

$sandi = envNilai('ADMIN_PASSWORD');
if ($sandi === '') {
    http_response_code(404);
    exit('Halaman admin belum diaktifkan. Isi ADMIN_PASSWORD di config/lokal.php.');
}

if (isset($_GET['keluar'])) {
    unset($_SESSION['admin']);
    header('Location: admin.php');
    exit;
}

$salah = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['sandi'])) {
    if (hash_equals($sandi, (string) $_POST['sandi'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }
    sleep(1);
    $salah = true;
}

$adaPeran = ($r = $koneksi->query("SHOW COLUMNS FROM users LIKE 'peran'")) && $r->num_rows > 0;
$kolPeran = $adaPeran ? 'u.peran' : 'NULL';
$adaKlipaa = ($r = $koneksi->query("SHOW COLUMNS FROM artikel LIKE 'sudah_klipaa'")) && $r->num_rows > 0;
$kolKlipaa = $adaKlipaa ? 'a.sudah_klipaa' : '0';

$css = '<style>
:root{--hijau:#3f7f62;--hijau-tua:#2b5a45;--hijau-muda:#e6f1ea;--kertas:#fffdf8;--latar:#f3f0e8;--garis:#e3ded2;--teks:#1f2d26;--redup:#6f7c74}
*{box-sizing:border-box}
body{margin:0;background:var(--latar);color:var(--teks);font:15px/1.5 Nunito,Arial,sans-serif}
h1,h2,h3{font-family:"Baloo 2",Nunito,sans-serif;margin:0}
a{color:var(--hijau-tua)}
.atas{background:var(--hijau-tua);color:#fff}
.atas .w{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:14px;padding-bottom:14px}
.atas h1{font-size:22px;font-weight:700}.atas small{display:block;font:500 12px Nunito;opacity:.75}
.atas a{color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.4);padding:6px 14px;border-radius:999px;font-weight:700;font-size:13px}
.w{max-width:1200px;margin:0 auto;padding:18px 16px}
.kartu{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-top:-6px}
.kartu div{background:var(--kertas);border:1px solid var(--garis);border-left:5px solid var(--hijau);border-radius:12px;padding:12px 14px;font-size:13px;color:var(--redup);font-weight:700}
.kartu b{display:block;font:700 30px/1.1 "Baloo 2";color:var(--teks)}
.kartu .kuning{border-left-color:#d9a441}.kartu .merah{border-left-color:#c8553d}.kartu .biru{border-left-color:#4a8fb5}
.alat{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin:20px 0 12px}
.tab{display:flex;gap:6px;background:var(--kertas);border:1px solid var(--garis);border-radius:999px;padding:4px}
.tab button{border:0;background:transparent;padding:8px 18px;border-radius:999px;font:700 14px Nunito;color:var(--redup);cursor:pointer}
.tab button.aktif{background:var(--hijau);color:#fff}
.cari{display:flex;gap:8px;flex:1;max-width:440px}
.cari input{flex:1;min-width:0;padding:10px 14px;border:1px solid var(--garis);border-radius:999px;background:var(--kertas);font:inherit}
button.tombol,.cari button{padding:10px 18px;border:0;border-radius:999px;background:var(--hijau);color:#fff;font:700 14px Nunito;cursor:pointer}
.t{background:var(--kertas);border:1px solid var(--garis);border-radius:14px;overflow:hidden}
table{border-collapse:collapse;width:100%;font-size:14px}
th{background:var(--hijau-muda);color:var(--hijau-tua);text-align:left;padding:11px 14px;font-weight:800;white-space:nowrap}
td{padding:11px 14px;border-top:1px solid var(--garis);vertical-align:middle}
tr:hover td{background:#faf8f1}
.orang{display:flex;align-items:center;gap:10px;font-weight:700}
.av{flex:none;width:34px;height:34px;border-radius:50%;background:var(--hijau-muda);color:var(--hijau-tua);display:grid;place-items:center;font:700 14px "Baloo 2"}
.redup{color:var(--redup);font-size:13px}
.pil{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:800;background:#eee;color:#555;white-space:nowrap}
.pil.selesai{background:#dcf0e3;color:#22623f}.pil.menulis{background:#fbefcf;color:#8a6412}.pil.gagal{background:#f9dcd5;color:#a13b26}
.pil.peran{background:#e4eef6;color:#2d5f82}
.kode{font-family:ui-monospace,Consolas,monospace;font-size:12.5px;background:#f0ede3;padding:2px 6px;border-radius:6px}
.aksi{display:inline-block;margin:0 6px 0 0}
.cek,.hapus{border:1px solid var(--garis);background:#fff;border-radius:999px;padding:5px 12px;font:700 12px Nunito;cursor:pointer;white-space:nowrap}
.cek.ya{background:#dcf0e3;color:#22623f;border-color:#b5dcc3}.hapus{color:#a13b26}.hapus:hover{background:#f9dcd5}
.info{background:#fbefcf;color:#6b4d0d;border-radius:10px;padding:10px 14px;margin:12px 0;font-size:13px}
.kosong{padding:28px;text-align:center;color:var(--redup)}
.masuk{max-width:360px;margin:12vh auto;background:var(--kertas);border:1px solid var(--garis);border-radius:16px;padding:26px}
.masuk input{width:100%;padding:11px 14px;border:1px solid var(--garis);border-radius:10px;margin:14px 0 10px;font:inherit}
.masuk .tombol{width:100%;border-radius:10px}.err{color:#b3261e;font-weight:700}
.detail{display:grid;gap:14px;margin-top:14px}
pre{white-space:pre-wrap;background:var(--kertas);border:1px solid var(--garis);border-radius:14px;padding:18px;font:inherit;line-height:1.75;margin:0}
@media(max-width:760px){
.kartu{grid-template-columns:repeat(2,1fr)}
.cari{max-width:none;flex-basis:100%}
table,tbody,tr,td{display:block}tr:first-child{display:none}
tr{padding:10px 14px;border-top:1px solid var(--garis)}tr:nth-child(2){border-top:0}
td{border:0;padding:3px 0;display:flex;gap:10px;justify-content:space-between;text-align:right}
td::before{content:attr(data-l);color:var(--redup);font-weight:700;font-size:12px;text-align:left;flex:none}
tr:hover td{background:none}}
</style>';
function lencana(string $status): string { return '<span class="pil ' . e($status) . '">' . e($status) . '</span>'; }
function inisial(?string $nama): string { return e(mb_strtoupper(mb_substr(trim((string) $nama) ?: '?', 0, 1))); }
$kepala = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Admin EduStory</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">' . $css . '</head><body>';

if (empty($_SESSION['admin'])) {
    echo $kepala . '<form method="post" class="masuk"><h1>Admin EduStory</h1><span class="redup">Khusus pengelola</span>'
       . '<input type="password" name="sandi" placeholder="Password admin" autofocus required><button class="tombol">Masuk</button>'
       . ($salah ? '<p class="err">Password salah.</p>' : '') . '</form></body></html>';
    exit;
}

$_SESSION['admin_csrf'] = $_SESSION['admin_csrf'] ?? bin2hex(random_bytes(16));
$csrf = $_SESSION['admin_csrf'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi'])) {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Sesi tidak valid. Muat ulang halaman admin.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['aksi'] === 'hapus' && $id > 0) {
        $h = $koneksi->prepare('DELETE FROM artikel_gambar WHERE artikel_id = ?');
        $h->bind_param('i', $id);
        $h->execute();
        $h = $koneksi->prepare('DELETE FROM artikel WHERE id = ?');
        $h->bind_param('i', $id);
        $h->execute();
    }
    if ($_POST['aksi'] === 'klipaa' && $adaKlipaa && $id > 0) {
        $v = ((int) ($_POST['nilai'] ?? 0)) === 1 ? 1 : 0;
        $h = $koneksi->prepare('UPDATE artikel SET sudah_klipaa = ?, klipaa_pada = IF(? = 1, NOW(), NULL) WHERE id = ?');
        $h->bind_param('iii', $v, $v, $id);
        $h->execute();
    }
    $balik = trim((string) ($_POST['q'] ?? ''));
    header('Location: admin.php?tab=artikel' . ($balik !== '' ? '&q=' . urlencode($balik) : ''));
    exit;
}

function formAksi(array $a, bool $adaK, string $csrf, string $q): string
{
    $id = (int) $a['id'];
    $sudah = !empty($a['klipaa']) || !empty($a['sudah_klipaa']);
    $dasar = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="q" value="' . e($q) . '">';
    $h = '';
    if ($adaK) {
        $h .= '<form method="post" class="aksi">' . $dasar . '<input type="hidden" name="aksi" value="klipaa"><input type="hidden" name="nilai" value="' . ($sudah ? 0 : 1) . '">'
            . '<button class="cek' . ($sudah ? ' ya' : '') . '" title="Klik untuk mengubah">' . ($sudah ? '&#10003; Sudah di klipaa' : 'Belum di klipaa') . '</button></form>';
    }
    return $h . '<form method="post" class="aksi" onsubmit="return confirm(\'Hapus artikel ini beserta fotonya? Tidak bisa dibatalkan.\')">'
        . $dasar . '<input type="hidden" name="aksi" value="hapus"><button class="hapus">Hapus</button></form>';
}

function angka(mysqli $k, string $sql): int
{
    $r = $k->query($sql);
    return $r ? (int) $r->fetch_row()[0] : 0;
}

/* ----- Detail satu artikel ----- */
if (isset($_GET['artikel'])) {
    $st = $koneksi->prepare('SELECT a.*, ' . $kolKlipaa . ' AS klipaa, u.nama AS nama_guru, u.email, ' . $kolPeran . ' AS peran, s.nama_sekolah,
        (SELECT COUNT(*) FROM artikel_gambar g WHERE g.artikel_id = a.id) AS jml_foto, (SELECT COALESCE(SUM(LENGTH(g.data)),0) FROM artikel_gambar g WHERE g.artikel_id = a.id) AS byte_foto
        FROM artikel a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN sekolah s ON s.id = a.sekolah_id WHERE a.id = ?');
    $id = (int) $_GET['artikel'];
    $st->bind_param('i', $id);
    $st->execute();
    $a = $st->get_result()->fetch_assoc();
    echo $kepala . '<div class="atas"><div class="w"><h1>Admin EduStory<small>Detail artikel</small></h1><a href="admin.php">&larr; Kembali</a></div></div><div class="w">';
    if (!$a) { exit('<p>Artikel tidak ditemukan.</p></div></body></html>'); }
    echo '<h2 style="font-size:26px">' . e($a['judul'] ?: '(belum ada judul)') . '</h2><p>' . lencana($a['status'])
       . ' <span class="pil peran">' . e($a['jenis_nama']) . '</span> <span class="redup">' . e($a['dibuat_pada']) . '</span></p>'
       . '<p><b>' . e($a['nama_guru']) . '</b> <span class="pil peran">' . e($a['peran'] ?: '-') . '</span> <span class="redup">' . e($a['email'])
       . ' &middot; ' . e($a['nama_sekolah']) . ' &middot; ' . (int) $a['jml_foto'] . ' foto (' . round(((int) $a['byte_foto']) / 1024) . ' KB)</span></p>';
    echo '<p>' . formAksi($a, $adaKlipaa, $csrf, '') . '</p>';
    if ($a['pesan_error']) { echo '<p class="err">Error: ' . e($a['pesan_error']) . '</p>'; }
    echo '<div class="detail"><h3>Isian yang dikirim</h3><div class="t"><table><tr><th>Isian</th><th>Isi</th></tr>';
    foreach ((json_decode((string) $a['input_json'], true) ?: []) as $k => $v) {
        echo '<tr><td data-l="Isian" style="width:30%;font-weight:700">' . e($k) . '</td><td data-l="Isi">' . e(is_array($v) ? implode(', ', $v) : $v) . '</td></tr>';
    }
    echo '</table></div><h3>Hasil artikel</h3><pre>' . e($a['isi'] ?: '(kosong)') . '</pre></div></div></body></html>';
    exit;
}

/* ----- Ringkasan + daftar ----- */
$q = trim((string) ($_GET['q'] ?? ''));
$like = '%' . $q . '%';

$st = $koneksi->prepare('SELECT u.id, u.nama, ' . $kolPeran . ' AS peran, u.email, u.dibuat_pada, s.nama_sekolah, s.jenjang, s.desa, s.kecamatan, s.kabupaten, s.provinsi, s.kode_desa,
    (SELECT COUNT(*) FROM artikel a WHERE a.user_id = u.id) AS jml
    FROM users u LEFT JOIN sekolah s ON s.user_id = u.id
    WHERE ? = "" OR u.nama LIKE ? OR u.email LIKE ? OR s.nama_sekolah LIKE ? OR s.kabupaten LIKE ?
    ORDER BY u.id DESC LIMIT 300');
$st->bind_param('sssss', $q, $like, $like, $like, $like);
$st->execute();
$guru = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$st = $koneksi->prepare('SELECT a.id, a.judul, a.jenis_nama, a.status, a.dibuat_pada, ' . $kolKlipaa . ' AS klipaa, u.nama AS guru, ' . $kolPeran . ' AS peran, s.nama_sekolah
    FROM artikel a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN sekolah s ON s.id = a.sekolah_id
    WHERE ? = "" OR a.judul LIKE ? OR a.jenis_nama LIKE ? OR u.nama LIKE ? OR s.nama_sekolah LIKE ?
    ORDER BY a.id DESC LIMIT 300');
$st->bind_param('sssss', $q, $like, $like, $like, $like);
$st->execute();
$artikel = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$n = fn(string $sql) => angka($koneksi, $sql);
echo $kepala . '<div class="atas"><div class="w"><h1>Admin EduStory<small>Semua data yang masuk, hanya baca</small></h1><a href="admin.php?keluar=1">Keluar</a></div></div><div class="w">'
   . '<div class="kartu"><div>Akun<b>' . $n('SELECT COUNT(*) FROM users') . '</b></div>'
   . '<div class="biru">Sekolah<b>' . $n('SELECT COUNT(*) FROM sekolah') . '</b></div>'
   . '<div class="kuning">Artikel<b>' . $n('SELECT COUNT(*) FROM artikel') . '</b></div>'
   . '<div>Selesai<b>' . $n("SELECT COUNT(*) FROM artikel WHERE status='selesai'") . '</b></div>'
   . '<div class="biru">Sudah di klipaa<b>' . ($adaKlipaa ? $n('SELECT COUNT(*) FROM artikel WHERE sudah_klipaa = 1') : '-') . '</b></div>'
   . '<div class="merah">Gagal<b>' . $n("SELECT COUNT(*) FROM artikel WHERE status='gagal'") . '</b></div></div>'
   . ($adaKlipaa ? '' : '<div class="info">Fitur centang klipaa belum aktif. Jalankan database/tambah-klipaa.sql di phpMyAdmin.</div>')
   . '<div class="alat"><div class="tab"><button class="aktif" data-tab="akun">Akun (' . count($guru) . ')</button><button data-tab="artikel">Artikel (' . count($artikel) . ')</button></div>'
   . '<form class="cari"><input type="text" name="q" value="' . e($q) . '" placeholder="Cari nama, email, sekolah, kabupaten, judul"><button>Cari</button></form></div>'
   . '<div class="t" id="akun"><table><tr><th>Nama</th><th>Sebagai</th><th>Sekolah</th><th>Wilayah</th><th>Kode desa</th><th>Artikel</th><th>Daftar</th></tr>';
foreach ($guru as $g) {
    echo '<tr><td data-l="Nama"><div class="orang"><span class="av">' . inisial($g['nama']) . '</span><span>' . e($g['nama']) . '<br><span class="redup">' . e($g['email']) . '</span></span></div></td>'
       . '<td data-l="Sebagai"><span class="pil peran">' . e($g['peran'] ?: '-') . '</span></td>'
       . '<td data-l="Sekolah">' . e($g['nama_sekolah']) . ' <span class="redup">' . e($g['jenjang']) . '</span></td>'
       . '<td data-l="Wilayah" class="redup">' . e(implode(', ', array_filter([$g['desa'], $g['kecamatan'], $g['kabupaten'], $g['provinsi']]))) . '</td>'
       . '<td data-l="Kode desa"><span class="kode">' . e($g['kode_desa'] ?: '-') . '</span></td><td data-l="Artikel"><b>' . (int) $g['jml'] . '</b></td>'
       . '<td data-l="Daftar" class="redup">' . e($g['dibuat_pada']) . '</td></tr>';
}
echo '</table>' . ($guru ? '' : '<div class="kosong">Tidak ada data.</div>') . '</div>'
   . '<div class="t" id="artikel" hidden><table><tr><th>#</th><th>Judul</th><th>Jenis</th><th>Penulis</th><th>Sekolah</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr>';
foreach ($artikel as $a) {
    echo '<tr><td data-l="#" class="redup">' . (int) $a['id'] . '</td><td data-l="Judul"><a href="admin.php?artikel=' . (int) $a['id'] . '"><b>' . e($a['judul'] ?: '(belum ada judul)') . '</b></a></td>'
       . '<td data-l="Jenis">' . e($a['jenis_nama']) . '</td><td data-l="Penulis">' . e($a['guru']) . ' <span class="pil peran">' . e($a['peran'] ?: '-') . '</span></td>'
       . '<td data-l="Sekolah">' . e($a['nama_sekolah']) . '</td><td data-l="Status">' . lencana($a['status']) . '</td><td data-l="Dibuat" class="redup">' . e($a['dibuat_pada']) . '</td>'
       . '<td data-l="Aksi">' . formAksi($a, $adaKlipaa, $csrf, $q) . '</td></tr>';
}
echo '</table>' . ($artikel ? '' : '<div class="kosong">Tidak ada data.</div>') . '</div></div>'
   . '<script>document.querySelectorAll("[data-tab]").forEach(function(b){b.onclick=function(){document.querySelectorAll("[data-tab]").forEach(function(x){x.classList.toggle("aktif",x===b);document.getElementById(x.dataset.tab).hidden=x!==b})}});if(/[?&]tab=artikel/.test(location.search))document.querySelector("[data-tab=artikel]").click();</script></body></html>';
