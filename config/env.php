<?php

/*
|--------------------------------------------------------------------------
| Pembaca pengaturan
|--------------------------------------------------------------------------
|
| Urutan pencarian nilai:
| 1. Environment variable (misalnya diisi di dashboard Vercel)
| 2. File config/lokal.php (untuk komputer sendiri, tidak ikut Git)
| 3. Nilai bawaan
|
*/

if (!function_exists('envNilai')) {

    function envNilai(string $kunci, string $bawaan = ''): string
    {
        static $lokal = null;

        if ($lokal === null) {
            $berkas = __DIR__ . '/lokal.php';
            $hasil = is_file($berkas) ? require $berkas : [];
            $lokal = is_array($hasil) ? $hasil : [];
        }

        $nilai = getenv($kunci);

        if ($nilai !== false && $nilai !== '') {
            return (string) $nilai;
        }

        if (isset($_ENV[$kunci]) && $_ENV[$kunci] !== '') {
            return (string) $_ENV[$kunci];
        }

        if (isset($lokal[$kunci]) && $lokal[$kunci] !== '') {
            return (string) $lokal[$kunci];
        }

        return $bawaan;
    }
}
