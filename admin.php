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

$css = '<style>
body{font-family:Nunito,Arial,sans-serif;margin:0;background:#f6f4ee;color:#1d2b24}
.w{max-width:1200px;margin:0 auto;padding:16px}
h1{font-size:22px;margin:0}h2{font-size:17px;margin:26px 0 8px}
.bar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-bottom:12px}
.kartu{display:flex;flex-wrap:wrap;gap:10px}.kartu div{background:#fff;border:1px solid #ddd;border-radius:10px;padding:10px 16px}
.kartu b{display:block;font-size:22px}
.t{overflow-x:auto;background:#fff;border:1px solid #ddd;border-radius:10px}
table{border-collapse:collapse;width:100%;font-size:13px}th,td{padding:8px 10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#eef5f1;white-space:nowrap}a{color:#2f6b4f}
input[type=text],input[type=password]{padding:8px 10px;border:1px solid #bbb;border-radius:8px}
button{padding:8px 14px;border:0;border-radius:8px;background:#4f8f72;color:#fff;cursor:pointer}
pre{white-space:pre-wrap;background:#fff;border:1px solid #ddd;border-radius:10px;padding:14px;font:inherit;line-height:1.6}
.err{color:#b3261e}
</style>';
$kepala = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Admin EduStory</title>' . $css . '</head><body><div class="w">';

if (empty($_SESSION['admin'])) {
    echo $kepala . '<h1>Admin EduStory</h1><form method="post" style="margin-top:14px">'
       . '<input type="password" name="sandi" placeholder="Password admin" autofocus required> <button>Masuk</button></form>'
       . ($salah ? '<p class="err">Password salah.</p>' : '') . '</div></body></html>';
    exit;
}

function angka(mysqli $k, string $sql): int
{
    $r = $k->query($sql);
    return $r ? (int) $r->fetch_row()[0] : 0;
}

/* ----- Detail satu artikel ----- */
if (isset($_GET['artikel'])) {
    $st = $koneksi->prepare('SELECT a.*, u.nama AS nama_guru, u.email, s.nama_sekolah,
        (SELECT COUNT(*) FROM artikel_gambar g WHERE g.artikel_id = a.id) AS jml_foto
        FROM artikel a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN sekolah s ON s.id = a.sekolah_id WHERE a.id = ?');
    $id = (int) $_GET['artikel'];
    $st->bind_param('i', $id);
    $st->execute();
    $a = $st->get_result()->fetch_assoc();
    echo $kepala . '<p><a href="admin.php">&larr; Kembali</a></p>';
    if (!$a) { exit('<p>Artikel tidak ditemukan.</p></div></body></html>'); }
    echo '<h1>' . e($a['judul'] ?: '(belum ada judul)') . '</h1><p>' . e($a['jenis_nama']) . ' &middot; ' . e($a['status'])
       . ' &middot; ' . e($a['dibuat_pada']) . '<br>Guru: ' . e($a['nama_guru']) . ' (' . e($a['email']) . ') &middot; '
       . e($a['nama_sekolah']) . ' &middot; ' . (int) $a['jml_foto'] . ' foto</p>';
    if ($a['pesan_error']) { echo '<p class="err">Error: ' . e($a['pesan_error']) . '</p>'; }
    echo '<h2>Isian yang dikirim</h2><div class="t"><table>';
    foreach ((json_decode((string) $a['input_json'], true) ?: []) as $k => $v) {
        echo '<tr><th>' . e($k) . '</th><td>' . e(is_array($v) ? implode(', ', $v) : $v) . '</td></tr>';
    }
    echo '</table></div><h2>Hasil artikel</h2><pre>' . e($a['isi'] ?: '(kosong)') . '</pre></div></body></html>';
    exit;
}

/* ----- Ringkasan + daftar ----- */
$q = trim((string) ($_GET['q'] ?? ''));
$like = '%' . $q . '%';

$st = $koneksi->prepare('SELECT u.id, u.nama, u.email, u.dibuat_pada, s.nama_sekolah, s.jenjang, s.desa, s.kecamatan, s.kabupaten, s.provinsi, s.kode_desa,
    (SELECT COUNT(*) FROM artikel a WHERE a.user_id = u.id) AS jml
    FROM users u LEFT JOIN sekolah s ON s.user_id = u.id
    WHERE ? = "" OR u.nama LIKE ? OR u.email LIKE ? OR s.nama_sekolah LIKE ? OR s.kabupaten LIKE ?
    ORDER BY u.id DESC LIMIT 300');
$st->bind_param('sssss', $q, $like, $like, $like, $like);
$st->execute();
$guru = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$st = $koneksi->prepare('SELECT a.id, a.judul, a.jenis_nama, a.status, a.dibuat_pada, u.nama AS guru, s.nama_sekolah
    FROM artikel a LEFT JOIN users u ON u.id = a.user_id LEFT JOIN sekolah s ON s.id = a.sekolah_id
    WHERE ? = "" OR a.judul LIKE ? OR a.jenis_nama LIKE ? OR u.nama LIKE ? OR s.nama_sekolah LIKE ?
    ORDER BY a.id DESC LIMIT 300');
$st->bind_param('sssss', $q, $like, $like, $like, $like);
$st->execute();
$artikel = $st->get_result()->fetch_all(MYSQLI_ASSOC);

echo $kepala . '<div class="bar"><h1>Admin EduStory</h1><a href="admin.php?keluar=1">Keluar</a></div>'
   . '<div class="kartu"><div><b>' . angka($koneksi, 'SELECT COUNT(*) FROM users') . '</b>Akun guru</div>'
   . '<div><b>' . angka($koneksi, 'SELECT COUNT(*) FROM sekolah') . '</b>Sekolah</div>'
   . '<div><b>' . angka($koneksi, 'SELECT COUNT(*) FROM artikel') . '</b>Artikel</div>'
   . '<div><b>' . angka($koneksi, "SELECT COUNT(*) FROM artikel WHERE status='selesai'") . '</b>Selesai</div>'
   . '<div><b>' . angka($koneksi, "SELECT COUNT(*) FROM artikel WHERE status='gagal'") . '</b>Gagal</div></div>'
   . '<form style="margin:14px 0"><input type="text" name="q" value="' . e($q) . '" placeholder="Cari nama, email, sekolah, kabupaten, judul"> <button>Cari</button></form>'
   . '<h2>Akun dan sekolah</h2><div class="t"><table><tr><th>Guru</th><th>Email</th><th>Sekolah</th><th>Wilayah</th><th>Kode desa</th><th>Artikel</th><th>Daftar</th></tr>';
foreach ($guru as $g) {
    echo '<tr><td>' . e($g['nama']) . '</td><td>' . e($g['email']) . '</td><td>' . e($g['nama_sekolah']) . ' ' . e($g['jenjang'])
       . '</td><td>' . e(implode(', ', array_filter([$g['desa'], $g['kecamatan'], $g['kabupaten'], $g['provinsi']]))) . '</td><td>'
       . e($g['kode_desa']) . '</td><td>' . (int) $g['jml'] . '</td><td>' . e($g['dibuat_pada']) . '</td></tr>';
}
echo '</table></div><h2>Semua artikel</h2><div class="t"><table><tr><th>#</th><th>Judul</th><th>Jenis</th><th>Guru</th><th>Sekolah</th><th>Status</th><th>Dibuat</th></tr>';
foreach ($artikel as $a) {
    echo '<tr><td>' . (int) $a['id'] . '</td><td><a href="admin.php?artikel=' . (int) $a['id'] . '">' . e($a['judul'] ?: '(belum ada judul)')
       . '</a></td><td>' . e($a['jenis_nama']) . '</td><td>' . e($a['guru']) . '</td><td>' . e($a['nama_sekolah'])
       . '</td><td>' . e($a['status']) . '</td><td>' . e($a['dibuat_pada']) . '</td></tr>';
}
echo '</table></div></div></body></html>';
