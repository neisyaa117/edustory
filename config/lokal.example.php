<?php

/*
| Salin file ini menjadi config/lokal.php, lalu isi nilai sebenarnya.
| config/lokal.php TIDAK ikut ke GitHub (lihat .gitignore).
|
| DI KOMPUTER SENDIRI (XAMPP): nilai di bawah ini (localhost/root) sudah pas.
|
| DI HOSTING (misalnya InfinityFree): jangan taruh config/lokal.php lewat Git.
| Buat file ini LANGSUNG di server lewat File Manager atau FTP, isi dengan
| data database hosting itu sendiri (lihat vPanel masing-masing penyedia,
| menu MySQL Databases), BUKAN nilai di bawah ini.
*/

return [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'edustory',

    'AI_PROVIDER' => 'gemini',
    'AI_API_KEY'  => 'isi-kunci-API-kamu',
    'AI_MODEL'    => 'gemini-3.6-flash',

    // Kata sandi untuk membuka admin.php (terpisah dari akun guru).
    // Kosong = halaman admin menolak dibuka sama sekali.
    'ADMIN_PASSWORD' => 'ganti-dengan-kata-sandi-admin',

    // 1 = tampilkan penyebab error yang sebenarnya di layar.
    // Di komputer sendiri boleh 1. Di hosting/situs umum, HARUS 0 (atau dihapus).
    'APP_DEBUG'   => '1',
];
