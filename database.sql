-- =========================================================
-- FILE: database.sql
-- FUNGSI: Membuat database "perpustakaan" beserta tabel-tabel
--         yang dibutuhkan (users & buku), lalu mengisi data awal.
-- CARA PAKAI: Buka phpMyAdmin (http://localhost/phpmyadmin),
--         klik tab "Import" -> pilih file ini -> klik "Go".
--         Atau buka tab "SQL" lalu paste seluruh isi file ini.
-- =========================================================

-- 1. Membuat database (kalau belum ada)
CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;

-- 2. Tabel "users" -> menyimpan akun untuk login
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- akan diisi hash bcrypt, BUKAN teks polos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel "buku" -> data buku perpustakaan (objek CRUD utama)
CREATE TABLE IF NOT EXISTS buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Membuat 1 akun admin default
--    Username : admin
--    Password : admin123
--    (password di bawah ini adalah hasil password_hash('admin123')
--     dari PHP, jadi saat login PHP akan mencocokkannya otomatis
--     memakai fungsi password_verify())
INSERT INTO users (username, password)
VALUES ('admin', '$2b$10$U5GA8BDhHKrmMPBSvnMYLOg5PNJgL4LxNFQImAieVzF/H/AiXcC9.')
ON DUPLICATE KEY UPDATE username = username;

-- 5. Contoh data buku supaya tabel tidak kosong saat pertama dicoba
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 3),
('Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 7);
