<?php
/**
 * =========================================================
 * FILE: koneksi.php
 * FUNGSI: Membuka koneksi ke database MySQL "perpustakaan"
 *         menggunakan ekstensi mysqli.
 *
 * File ini akan di-"include" (dipanggil) oleh hampir semua
 * file PHP lain (login.php, index.php, tambah.php, dst),
 * jadi kita cukup menulis kode koneksi SATU KALI di sini.
 * =========================================================
 */

// --- Konfigurasi koneksi (sesuaikan jika XAMPP kamu berbeda) ---
$host     = "localhost"; // alamat server MySQL, default XAMPP: localhost
$user     = "root";      // username default XAMPP
$password = "";          // password default XAMPP: kosong
$database = "perpustakaan";

// mysqli_connect() akan mencoba membuka koneksi.
// Hasilnya disimpan ke variabel $koneksi, dan variabel inilah
// yang nanti dipakai di file lain untuk menjalankan query.
$koneksi = mysqli_connect($host, $user, $password, $database);

// Jika koneksi GAGAL (misalnya XAMPP belum jalan / database belum dibuat),
// mysqli_connect() akan mengembalikan nilai false.
// die() akan menghentikan eksekusi PHP dan menampilkan pesan error.
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// (Opsional tapi disarankan) memastikan karakter yang dikirim/diterima
// menggunakan UTF-8, supaya teks dengan huruf khusus tidak rusak.
mysqli_set_charset($koneksi, "utf8mb4");

// --- Konfigurasi aturan peminjaman (dipakai di pinjam.php & kembalikan.php) ---
// Menaruh angka "ajaib" ini sebagai konstanta (bukan ditulis langsung di
// tengah kode) memudahkan kalau suatu saat aturan berubah -> cukup ubah
// di SATU tempat ini saja.
define("MAX_HARI_PINJAM", 7);   // batas waktu pinjam: 7 hari dari tanggal pinjam
define("DENDA_PER_HARI", 1000); // denda keterlambatan: Rp 1.000 / hari
