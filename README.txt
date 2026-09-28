=================================================================
 PROJEK LATIHAN: CRUD PERPUSTAKAAN + PEMINJAMAN (PHP + MySQLi + XAMPP)
=================================================================

DAFTAR ISI FILE
---------------
- database.sql          -> skrip awal: tabel users & buku + data awal
- update_peminjaman.sql -> skrip TAMBAHAN: tabel anggota & peminjaman
                            (jalankan SETELAH database.sql, aman untuk
                            database yang sudah berjalan / ada datanya)
- koneksi.php           -> konfigurasi koneksi + konstanta aturan pinjam
- auth_check.php        -> "penjaga" yang memblokir halaman jika belum login
- navbar.php            -> menu navigasi (Data Buku / Anggota / Peminjaman)
- login.php / logout.php-> proses masuk & keluar

-- Modul Buku --
- index.php    -> daftar buku (READ) + PENCARIAN + tombol pinjam/edit/hapus
- tambah.php   -> tambah buku (CREATE)
- edit.php     -> edit buku (UPDATE)
- hapus.php    -> hapus buku (DELETE), ditolak jika masih ada riwayat pinjam

-- Modul Anggota --
- daftar_anggota.php -> daftar anggota (READ)
- tambah_anggota.php -> tambah anggota (CREATE)
- edit_anggota.php   -> edit anggota (UPDATE)
- hapus_anggota.php  -> hapus anggota (DELETE), ditolak jika masih ada riwayat pinjam

-- Modul Peminjaman --
- pinjam.php            -> form catat peminjaman baru (stok otomatis berkurang)
- daftar_peminjaman.php -> daftar peminjaman aktif (+status Terlambat) & riwayat
- kembalikan.php        -> proses pengembalian (stok +1, denda dihitung otomatis)

- style.css -> styling tampilan (termasuk navbar & badge status)

CARA INSTALASI
--------------
1. Salin folder "perpustakaan_crud" ke dalam folder htdocs XAMPP kamu.
   Contoh (Windows): C:\xampp\htdocs\perpustakaan_crud
2. Buka XAMPP Control Panel, klik "Start" pada Apache dan MySQL.
3. Buka browser -> http://localhost/phpmyadmin
4. Import "database.sql" (tab Import -> pilih file -> Go).
5. Import "update_peminjaman.sql" (tab Import lagi -> pilih file -> Go).
6. Buka browser -> http://localhost/perpustakaan_crud/login.php
7. Login memakai:
   Username : admin
   Password : admin123

FITUR PEMINJAMAN & DENDA
-------------------------
- Durasi pinjam default: 7 hari (ubah di koneksi.php -> MAX_HARI_PINJAM)
- Denda keterlambatan: Rp 1.000/hari (ubah di koneksi.php -> DENDA_PER_HARI)
- Saat "Pinjam": stok buku otomatis berkurang 1, tanggal wajib kembali
  dihitung otomatis (hari ini + durasi pinjam).
- Saat "Kembalikan": stok buku otomatis bertambah 1. Kalau tanggal
  pengembalian melewati tanggal wajib kembali, denda dihitung otomatis
  (jumlah hari telat x denda per hari) dan disimpan ke riwayat.
- Insert peminjaman + update stok (begitu juga saat pengembalian)
  dibungkus dalam MySQL TRANSACTION, supaya kedua perubahan itu
  selalu terjadi bersamaan (tidak ada kondisi stok "nyasar" kalau
  di tengah proses tiba-tiba gagal).

ALUR PROGRAM (RINGKAS)
-----------------------
1. User login (login.php) -> session dibuat -> auth_check.php menjaga
   semua halaman lain supaya hanya bisa diakses setelah login.
2. index.php menampilkan daftar buku, dengan kolom pencarian
   (judul/penulis) dan tombol "Pinjam" (kalau stok > 0).
3. pinjam.php mencatat transaksi baru ke tabel peminjaman + mengurangi
   stok buku terkait.
4. daftar_peminjaman.php menampilkan peminjaman yang masih aktif
   (dengan status Terlambat kalau lewat tenggat) dan riwayat yang
   sudah dikembalikan beserta dendanya.
5. kembalikan.php menghitung denda (jika ada), menutup transaksi
   peminjaman, dan mengembalikan stok buku.
6. Modul Anggota (daftar/tambah/edit/hapus_anggota.php) mengelola
   data peminjam, dipakai sebagai pilihan dropdown di pinjam.php.

KONSEP PENTING YANG DIPAKAI (untuk bahan belajar uji kompetensi)
-----------------------------------------------------------------
- Prepared Statement (mysqli_prepare + bind_param) -> mencegah SQL Injection,
  termasuk untuk fitur pencarian (LIKE ?).
- password_hash() / password_verify() -> password TIDAK PERNAH disimpan
  sebagai teks biasa di database.
- htmlspecialchars() -> mencegah XSS saat menampilkan data ke HTML.
- $_SESSION -> menyimpan status "sedang login" antar halaman.
- Post/Redirect/Get -> setelah form berhasil diproses (POST), user
  di-redirect (header Location) supaya refresh browser tidak mengulang submit.
- FOREIGN KEY (relasi antar tabel) -> peminjaman.id_buku & id_anggota
  merujuk ke tabel buku & anggota, menjaga data tetap konsisten
  (buku/anggota yang masih punya riwayat pinjam tidak bisa dihapus).
- TRANSACTION (mysqli_begin_transaction/commit/rollback) -> menjamin
  beberapa query yang saling terkait (insert peminjaman + update stok)
  berhasil semua atau gagal semua, tidak setengah-setengah.
- JOIN -> menggabungkan data dari 3 tabel (peminjaman, buku, anggota)
  di daftar_peminjaman.php supaya bisa menampilkan judul & nama,
  bukan cuma angka id.

CATATAN / IDE PENGEMBANGAN LEBIH LANJUT
-----------------------------------------
Kalau masih ada waktu sebelum ujian, fitur berikut sering muncul juga
di uji kompetensi dan bisa jadi nilai tambah:
- Pagination (LIMIT & OFFSET) di daftar buku/peminjaman yang datanya banyak
- Role user (admin vs petugas) dengan hak akses berbeda
- Export data ke Excel/PDF
- Dashboard ringkasan (total buku, sedang dipinjam, jumlah terlambat)
- Notifikasi H-1 sebelum jatuh tempo
Beri tahu saya kalau mau saya bantu tambahkan salah satunya.

