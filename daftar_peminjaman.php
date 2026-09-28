<?php
/**
 * =========================================================
 * FILE: daftar_peminjaman.php
 * FUNGSI: Menampilkan 2 bagian:
 *  1. Peminjaman yang MASIH AKTIF (belum dikembalikan), dengan
 *     penanda "Terlambat" kalau sudah lewat tanggal wajib kembali.
 *  2. Riwayat peminjaman yang SUDAH dikembalikan (termasuk denda).
 *
 * Query di sini memakai JOIN, karena tabel peminjaman hanya
 * menyimpan id_buku & id_anggota (angka), sedangkan yang perlu
 * ditampilkan adalah judul buku & nama anggota (teks) dari
 * tabel lain.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

// --- Peminjaman aktif (belum dikembalikan) ---
// JOIN buku & anggota supaya bisa menampilkan judul dan nama,
// bukan cuma angka id_buku/id_anggota.
$aktif = mysqli_query($koneksi, "
    SELECT p.id, b.judul, a.nama, p.tanggal_pinjam, p.tanggal_wajib_kembali
    FROM peminjaman p
    JOIN buku b ON b.id = p.id_buku
    JOIN anggota a ON a.id = p.id_anggota
    WHERE p.status = 'dipinjam'
    ORDER BY p.tanggal_wajib_kembali ASC
");

// --- Riwayat (sudah dikembalikan), 20 transaksi terakhir ---
$riwayat = mysqli_query($koneksi, "
    SELECT p.id, b.judul, a.nama, p.tanggal_pinjam, p.tanggal_wajib_kembali, p.tanggal_kembali, p.denda
    FROM peminjaman p
    JOIN buku b ON b.id = p.id_buku
    JOIN anggota a ON a.id = p.id_anggota
    WHERE p.status = 'dikembalikan'
    ORDER BY p.tanggal_kembali DESC
    LIMIT 20
");

// Tanggal hari ini, dipakai untuk membandingkan apakah suatu
// peminjaman aktif sudah "Terlambat" atau belum.
$hari_ini = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Peminjaman - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php include "navbar.php"; ?>

    <h1>Peminjaman Aktif</h1>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan']) ?></div>
    <?php endif; ?>

    <a href="pinjam.php" class="btn">+ Catat Peminjaman Baru</a>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Judul Buku</th>
            <th>Peminjam</th>
            <th>Tgl Pinjam</th>
            <th>Wajib Kembali</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($aktif)):
            // Terlambat jika tanggal wajib kembali SUDAH LEWAT dari hari ini
            $terlambat = $row['tanggal_wajib_kembali'] < $hari_ini;
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_wajib_kembali']) ?></td>
                <td>
                    <?php if ($terlambat): ?>
                        <span class="badge badge-terlambat">Terlambat</span>
                    <?php else: ?>
                        <span class="badge badge-dipinjam">Dipinjam</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="kembalikan.php?id=<?= $row['id'] ?>" class="btn"
                       onclick="return confirm('Konfirmasi pengembalian buku ini?');">
                        Kembalikan
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($aktif) === 0): ?>
            <tr><td colspan="7" style="text-align:center; color:#888;">Tidak ada peminjaman aktif.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h1 style="margin-top:35px;">Riwayat Pengembalian</h1>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Judul Buku</th>
            <th>Peminjam</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th>Denda</th>
        </tr>
        </thead>
        <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($riwayat)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_kembali']) ?></td>
                <td>
                    <?php if ((int) $row['denda'] > 0): ?>
                        <span class="badge badge-terlambat">Rp<?= number_format($row['denda'], 0, ',', '.') ?></span>
                    <?php else: ?>
                        <span class="badge badge-dikembalikan">Tepat waktu</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($riwayat) === 0): ?>
            <tr><td colspan="6" style="text-align:center; color:#888;">Belum ada riwayat pengembalian.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
