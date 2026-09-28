-- =========================================================
-- FILE: update_peminjaman.sql
-- FUNGSI: Menambahkan tabel "anggota" dan "peminjaman" ke
--         database "perpustakaan" yang SUDAH ADA (tidak akan
--         menghapus data buku/users yang sudah kamu punya).
-- CARA PAKAI: phpMyAdmin -> pilih database "perpustakaan" ->
--         tab "SQL" -> paste seluruh isi file ini -> "Go".
--         (Kalau kamu baru mulai dari nol, jalankan dulu
--          database.sql, baru kemudian file ini.)
-- =========================================================

USE perpustakaan;

-- 1. Tabel "anggota" -> data orang yang meminjam buku
CREATE TABLE IF NOT EXISTS anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    alamat VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel "peminjaman" -> mencatat setiap transaksi pinjam & kembali
--    - id_buku & id_anggota adalah FOREIGN KEY (relasi) ke tabel lain
--    - tanggal_kembali NULL berarti buku BELUM dikembalikan
--    - status mempermudah query "buku mana saja yang masih dipinjam"
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_buku INT NOT NULL,
    id_anggota INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_wajib_kembali DATE NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    denda INT NOT NULL DEFAULT 0,
    status ENUM('dipinjam', 'dikembalikan') NOT NULL DEFAULT 'dipinjam',

    -- FOREIGN KEY: menjamin id_buku yang dimasukkan PASTI ada di tabel buku,
    -- dan id_anggota PASTI ada di tabel anggota (menjaga integritas data).
    -- Efek sampingnya: buku/anggota yang sudah pernah dipinjam TIDAK BISA
    -- dihapus begitu saja selama masih ada baris peminjaman yang merujuk
    -- ke dia -> ini akan kita tangani di hapus.php & hapus_anggota.php.
    CONSTRAINT fk_peminjaman_buku FOREIGN KEY (id_buku) REFERENCES buku(id),
    CONSTRAINT fk_peminjaman_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

-- 3. Contoh data anggota supaya langsung bisa dicoba
INSERT INTO anggota (nama, no_hp, alamat) VALUES
('Budi Santoso', '081234567890', 'Jl. Merdeka No. 1, Tangerang'),
('Siti Aminah', '081298765432', 'Jl. Kenanga No. 5, Depok');
