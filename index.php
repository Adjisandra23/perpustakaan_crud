<?php
/**
 * =========================================================
 * FILE: index.php
 * FUNGSI: Halaman utama setelah login.
 *         Bagian "READ" dari CRUD -> menampilkan semua data
 *         buku dari tabel "buku" dalam bentuk tabel HTML.
 * =========================================================
 */

require "auth_check.php"; // <- baris ini "menendang" user ke login.php
                           //    kalau dia belum login. HARUS di baris atas.
require "koneksi.php";    // membuka koneksi $koneksi ke database

// --- Fitur PENCARIAN ---
// Kata kunci diambil dari query string, contoh: index.php?cari=laskar
// trim() untuk menghapus spasi berlebih di awal/akhir.
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : "";

if ($cari !== "") {
    // LIKE dipakai untuk mencari teks yang MENGANDUNG kata kunci,
    // bukan harus sama persis. Tanda % adalah wildcard (bebas karakter apa saja).
    // Kita TETAP pakai prepared statement walau ini fitur pencarian,
    // supaya kata kunci yang diketik user tidak bisa dipakai untuk SQL Injection.
    $kata_kunci = "%" . $cari . "%";
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM buku WHERE judul LIKE ? OR penulis LIKE ? ORDER BY id DESC"
    );
    mysqli_stmt_bind_param($stmt, "ss", $kata_kunci, $kata_kunci);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // Tidak ada pencarian -> tampilkan semua data seperti biasa.
    // Karena tidak ada input user yang masuk ke query ini, aman memakai
    // mysqli_query() biasa (tidak perlu prepared statement).
    $result = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php include "navbar.php"; ?>

    <div class="topbar">
        <h1>Data Buku Perpustakaan</h1>
    </div>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Form pencarian -> method GET supaya kata kunci muncul di URL
         (?cari=...) dan halaman bisa di-bookmark/di-refresh -->
    <form method="GET" action="index.php" style="display:flex; gap:8px; margin-bottom:15px;">
        <input type="text" name="cari" placeholder="Cari judul atau penulis..."
               value="<?= htmlspecialchars($cari) ?>" style="flex:1;">
        <button type="submit">Cari</button>
        <?php if ($cari !== ""): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <a href="tambah.php" class="btn">+ Tambah Buku</a>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        // mysqli_fetch_assoc() mengambil data BARIS DEMI BARIS dari hasil query.
        // Loop ini akan terus berjalan sampai semua baris habis (return null/false).
        while ($row = mysqli_fetch_assoc($result)):
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <!-- htmlspecialchars() WAJIB dipakai setiap kali menampilkan
                     data dari database ke HTML, supaya kalau ada data yang
                     mengandung karakter <script> dsb, itu ditampilkan sebagai
                     teks biasa, bukan dieksekusi sebagai kode (proteksi XSS) -->
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['penulis']) ?></td>
                <td><?= htmlspecialchars($row['penerbit']) ?></td>
                <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                <td><?= htmlspecialchars($row['stok']) ?></td>
                <td>
                    <?php if ((int) $row['stok'] > 0): ?>
                        <a href="pinjam.php?id_buku=<?= $row['id'] ?>" class="btn">Pinjam</a>
                    <?php else: ?>
                        <span class="badge badge-terlambat">Stok Habis</span>
                    <?php endif; ?>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-secondary">Edit</a>
                    <a href="hapus.php?id=<?= $row['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Yakin ingin menghapus buku ini?');">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <tr><td colspan="7" style="text-align:center; color:#888;">
                <?= $cari !== "" ? "Tidak ada buku yang cocok dengan pencarian." : "Belum ada data buku." ?>
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
