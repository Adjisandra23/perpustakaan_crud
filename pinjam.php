<?php
/**
 * =========================================================
 * FILE: pinjam.php
 * FUNGSI: Mencatat transaksi peminjaman baru.
 *
 * Ada 2 hal yang harus terjadi BERSAMAAN saat buku dipinjam:
 *  1. INSERT baris baru ke tabel peminjaman
 *  2. UPDATE stok di tabel buku (stok - 1)
 * Kalau salah satu gagal di tengah jalan (misal server mati),
 * kita tidak mau salah satu berhasil tapi yang lain tidak
 * (nanti stok jadi tidak sinkron dengan data peminjaman).
 * Solusinya: bungkus keduanya dalam TRANSACTION -> "semua
 * berhasil" atau "semua dibatalkan", tidak ada tengah-tengah.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$error = "";

// id_buku bisa datang dari link "Pinjam" di index.php (?id_buku=..)
// atau dari dropdown di form ini sendiri saat submit ulang (kalau error).
$id_buku_terpilih = isset($_GET['id_buku']) ? (int) $_GET['id_buku']
                   : (isset($_POST['id_buku']) ? (int) $_POST['id_buku'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_buku    = (int) $_POST['id_buku'];
    $id_anggota = (int) $_POST['id_anggota'];

    if ($id_buku === 0 || $id_anggota === 0) {
        $error = "Buku dan anggota wajib dipilih.";
    } else {
        // --- Mulai transaction ---
        mysqli_begin_transaction($koneksi);

        try {
            // Ambil stok TERBARU dan KUNCI barisnya ("FOR UPDATE") supaya kalau
            // ada 2 orang meminjam buku yang sama di waktu bersamaan, mereka
            // tidak sama-sama lolos meminjam buku dengan stok tinggal 1.
            $stmt = mysqli_prepare($koneksi, "SELECT stok FROM buku WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt, "i", $id_buku);
            mysqli_stmt_execute($stmt);
            $buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$buku) {
                throw new Exception("Buku tidak ditemukan.");
            }
            if ((int) $buku['stok'] <= 0) {
                throw new Exception("Stok buku sudah habis.");
            }

            // Hitung tanggal wajib kembali = hari ini + MAX_HARI_PINJAM
            // (konstanta ini didefinisikan di koneksi.php)
            $tanggal_pinjam        = date('Y-m-d');
            $tanggal_wajib_kembali = date('Y-m-d', strtotime('+' . MAX_HARI_PINJAM . ' days'));

            // 1. INSERT ke tabel peminjaman
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_wajib_kembali, status)
                 VALUES (?, ?, ?, ?, 'dipinjam')"
            );
            mysqli_stmt_bind_param($stmt, "iiss", $id_buku, $id_anggota, $tanggal_pinjam, $tanggal_wajib_kembali);
            mysqli_stmt_execute($stmt);

            // 2. UPDATE stok buku (dikurangi 1)
            $stmt = mysqli_prepare($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_buku);
            mysqli_stmt_execute($stmt);

            // Kalau sampai baris ini tidak ada error yang dilempar (throw),
            // berarti semua langkah berhasil -> permanenkan perubahannya.
            mysqli_commit($koneksi);

            header("Location: daftar_peminjaman.php?pesan=Peminjaman berhasil dicatat");
            exit();

        } catch (Exception $e) {
            // Kalau ADA error di salah satu langkah -> batalkan SEMUA
            // perubahan yang sudah terjadi di transaction ini (rollback),
            // supaya data tidak setengah-setengah.
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}

// Ambil daftar buku yang stoknya masih > 0, untuk pilihan dropdown
$daftar_buku = mysqli_query($koneksi, "SELECT id, judul, stok FROM buku WHERE stok > 0 ORDER BY judul ASC");
// Ambil semua anggota untuk pilihan dropdown
$daftar_anggota = mysqli_query($koneksi, "SELECT id, nama FROM anggota ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pinjam Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:500px;">
    <?php include "navbar.php"; ?>

    <h1>Pinjam Buku</h1>
    <p style="color:#666;">Durasi pinjam: <?= MAX_HARI_PINJAM ?> hari. Denda keterlambatan: Rp<?= number_format(DENDA_PER_HARI, 0, ',', '.') ?> / hari.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="pinjam.php">
        <div class="form-group">
            <label>Pilih Buku</label>
            <select name="id_buku" required>
                <option value="">-- Pilih Buku --</option>
                <?php while ($b = mysqli_fetch_assoc($daftar_buku)): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $id_buku_terpilih ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['judul']) ?> (stok: <?= $b['stok'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Pilih Anggota</label>
            <select name="id_anggota" required>
                <option value="">-- Pilih Anggota --</option>
                <?php while ($a = mysqli_fetch_assoc($daftar_anggota)): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit">Pinjam</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
