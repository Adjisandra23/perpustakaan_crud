<?php
/**
 * =========================================================
 * FILE: hapus_anggota.php
 * FUNGSI: DELETE untuk tabel anggota.
 *
 * CATATAN PENTING: tabel peminjaman punya FOREIGN KEY yang menunjuk
 * ke anggota.id. Artinya MySQL akan MENOLAK penghapusan kalau
 * anggota tersebut masih tercatat pernah/sedang meminjam buku
 * (supaya riwayat peminjaman tidak jadi "yatim piatu"/rusak).
 * Kita tangkap error itu dan tampilkan pesan yang ramah,
 * bukan error mysqli mentah yang membingungkan.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM anggota WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    // mysqli_stmt_execute() mengembalikan TRUE/FALSE, bukan langsung
    // menghentikan program kalau gagal (beda dengan error fatal biasa).
    $berhasil = mysqli_stmt_execute($stmt);

    if (!$berhasil) {
        // errno 1451 = "Cannot delete or update a parent row: a foreign
        // key constraint fails" -> kode error standar MySQL untuk kasus ini.
        header("Location: daftar_anggota.php?error=" . urlencode(
            "Anggota tidak bisa dihapus karena masih memiliki riwayat peminjaman."
        ));
        exit();
    }
}

header("Location: daftar_anggota.php?pesan=Anggota berhasil dihapus");
exit();
