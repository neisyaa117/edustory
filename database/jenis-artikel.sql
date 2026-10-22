-- =====================================================================
-- EduStory: tabel pengelola jenis artikel (CRUD di halaman admin)
-- Jalankan SEKALI di phpMyAdmin (tab SQL).
-- Catatan: aplikasi juga membuat tabel ini otomatis saat admin dibuka,
-- jadi file ini hanya cadangan kalau akun database tidak boleh CREATE.
-- Isi config/jenis-artikel.php tidak diubah dan tetap jadi data bawaan.
-- =====================================================================

CREATE TABLE IF NOT EXISTS jenis_artikel_db (
    slug            VARCHAR(40) NOT NULL,
    aktif           TINYINT(1)  NOT NULL DEFAULT 1,
    data            MEDIUMTEXT  NULL,
    dibuat_pada     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME    NULL,
    PRIMARY KEY (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
