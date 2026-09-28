<?php
/**
 * =========================================================
 * FILE: kembalikan.php
 * FUNGSI: Memproses pengembalian buku.
 *         Sama seperti pinjam.php, ada 2 hal yang harus terjadi
 *         BERSAMAAN, jadi dibungkus TRANSACTION lagi:
 *  1. UPDATE baris peminjaman -> isi tanggal_kembali, denda, status
 *  2. UPDATE stok buku (stok + 1)
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    mysqli_begin_transaction($koneksi);

    try {
        // Ambil data peminjaman ini, KUNCI barisnya (FOR UPDATE) supaya
        // tidak diproses dobel kalau user klik tombol "Kembalikan" 2x cepat.
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM peminjaman WHERE id = ? AND status = 'dipinjam' FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $peminjaman = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$peminjaman) {
            // Entah id salah, atau buku ini sudah lebih dulu dikembalikan
            throw new Exception("Data peminjaman tidak ditemukan atau sudah dikembalikan.");
        }

        $tanggal_kembali = date('Y-m-d');

        // --- Hitung denda ---
        // datediff dalam PHP: selisih hari antara 2 tanggal.
        // Kita pakai DateTime supaya perhitungannya akurat (tidak
        // terpengaruh masalah zona waktu / jumlah hari dalam bulan).
        $wajib_kembali = new DateTime($peminjaman['tanggal_wajib_kembali']);
        $kembali       = new DateTime($tanggal_kembali);
        $selisih       = $wajib_kembali->diff($kembali)->days;

        // Kalau tanggal kembali masih SEBELUM/SAMA DENGAN wajib kembali,
        // $wajib_kembali > $kembali tidak mungkin di kasus normal, tapi
        // kita tetap jaga-jaga dengan invert: kalau kembali <= wajib -> 0 hari telat.
        $hari_telat = ($kembali > $wajib_kembali) ? $selisih : 0;
        $denda      = $hari_telat * DENDA_PER_HARI;

        // 1. UPDATE data peminjaman
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE peminjaman SET tanggal_kembali = ?, denda = ?, status = 'dikembalikan' WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "sii", $tanggal_kembali, $denda, $id);
        mysqli_stmt_execute($stmt);

        // 2. Kembalikan stok buku (+1)
        $stmt = mysqli_prepare($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $peminjaman['id_buku']);
        mysqli_stmt_execute($stmt);

        mysqli_commit($koneksi);

        $pesan = $denda > 0
            ? "Buku dikembalikan. Terlambat $hari_telat hari, denda Rp" . number_format($denda, 0, ',', '.') . "."
            : "Buku berhasil dikembalikan tepat waktu.";

        header("Location: daftar_peminjaman.php?pesan=" . urlencode($pesan));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: daftar_peminjaman.php?pesan=" . urlencode("Gagal: " . $e->getMessage()));
        exit();
    }
}

header("Location: daftar_peminjaman.php");
exit();
