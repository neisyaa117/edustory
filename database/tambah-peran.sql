-- Jalankan sekali di phpMyAdmin (database yang dipakai situs)
ALTER TABLE users ADD COLUMN peran VARCHAR(40) NULL AFTER nama;
