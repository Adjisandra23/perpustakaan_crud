<?php
/**
 * =========================================================
 * FILE: hapus.php
 * FUNGSI: Bagian "DELETE" dari CRUD.
 *         File ini TIDAK punya tampilan HTML sama sekali -> tugasnya
 *         cuma menghapus 1 baris data, lalu langsung redirect kembali.
 *         Dipanggil lewat link <a href="hapus.php?id=..."> di index.php.
 *
 * CATATAN: sejak ada tabel peminjaman (yang FOREIGN KEY ke buku.id),
 * buku yang pernah/sedang dipinjam TIDAK BISA dihapus begitu saja.
 * Kita tangkap kegagalan itu dan tampilkan pesan yang ramah.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Prepared statement juga dipakai di sini walau id sudah di-cast (int),
    // sebagai lapisan proteksi kedua (defense in depth) sekaligus contoh
    // pola yang konsisten dengan file-file CRUD lainnya.
    $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $berhasil = mysqli_stmt_execute($stmt);

    if (!$berhasil) {
        header("Location: index.php?error=" . urlencode(
            "Buku tidak bisa dihapus karena masih memiliki riwayat peminjaman."
        ));
        exit();
    }
}

header("Location: index.php?pesan=Buku berhasil dihapus");
exit();
