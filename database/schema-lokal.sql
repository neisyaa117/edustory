-- =====================================================================
-- EduStory: database BARU untuk komputer sendiri (XAMPP)
-- Impor file ini SEKALI di phpMyAdmin (tab Impor / Import).
-- Semua tabel dibuat sekaligus: users, sekolah, artikel, artikel_gambar.
-- Jangan dipakai pada database yang sudah berisi data.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS edustory
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE edustory;


-- 1. Akun guru

CREATE TABLE IF NOT EXISTS users (
    id         INT NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(150) NOT NULL,
    peran      VARCHAR(40) NULL,
    email      VARCHAR(190) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2. Data sekolah (satu akun satu sekolah)

CREATE TABLE IF NOT EXISTS sekolah (
    id             INT NOT NULL AUTO_INCREMENT,
    user_id        INT NOT NULL,
    nama_sekolah   VARCHAR(200) NOT NULL,
    provinsi       VARCHAR(100) NULL,
    kabupaten      VARCHAR(100) NULL,
    kecamatan      VARCHAR(100) NULL,
    desa           VARCHAR(100) NULL,
    alamat         TEXT NULL,
    jenjang        VARCHAR(50) NULL,
    kode_provinsi  VARCHAR(10) NULL,
    kode_kabupaten VARCHAR(10) NULL,
    kode_kecamatan VARCHAR(12) NULL,
    kode_desa      VARCHAR(16) NULL,
    PRIMARY KEY (id),
    KEY idx_sekolah_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 3. Artikel hasil generate

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


-- 4. Foto artikel (disimpan setelah dikecilkan)

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
