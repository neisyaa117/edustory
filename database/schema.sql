-- =====================================================================
-- EduStory: pembaruan database
-- Jalankan SEKALI di phpMyAdmin (tab SQL) pada database EduStory.
-- Tabel users dan sekolah yang sudah ada tidak dihapus.
-- =====================================================================


-- 1. Kode wilayah, dipakai untuk menyambung ke klipaa.
--    Bila muncul "Duplicate column name", berarti sudah pernah dijalankan.

ALTER TABLE sekolah
    ADD COLUMN kode_provinsi  VARCHAR(10) NULL,
    ADD COLUMN kode_kabupaten VARCHAR(10) NULL,
    ADD COLUMN kode_kecamatan VARCHAR(12) NULL,
    ADD COLUMN kode_desa      VARCHAR(16) NULL;


-- 2. Artikel hasil generate.

CREATE TABLE IF NOT EXISTS artikel (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT NOT NULL,
    sekolah_id      INT NULL,
    jenis           VARCHAR(40)  NOT NULL,
    jenis_nama      VARCHAR(80)  NOT NULL,
    judul           VARCHAR(255) NULL,
    isi             MEDIUMTEXT   NULL,
    input_json      MEDIUMTEXT   NOT NULL,
    status          ENUM('baru','menulis','selesai','gagal') NOT NULL DEFAULT 'baru',
    pesan_error     VARCHAR(500) NULL,
    jumlah_tulis    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    dibuat_pada     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_artikel_user (user_id, dibuat_pada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 3. Foto artikel (disimpan setelah dikecilkan).

CREATE TABLE IF NOT EXISTS artikel_gambar (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    artikel_id INT UNSIGNED NOT NULL,
    token      CHAR(32)     NOT NULL,
    mime       VARCHAR(50)  NOT NULL,
    data       MEDIUMBLOB   NOT NULL,
    urutan     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gambar_token (token),
    KEY idx_gambar_artikel (artikel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
