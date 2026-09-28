<?php
/**
 * =========================================================
 * FILE: tambah.php
 * FUNGSI: Bagian "CREATE" dari CRUD.
 *         - Method GET  -> tampilkan form kosong
 *         - Method POST -> simpan data baru ke tabel buku
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & bersihkan input dari form
    $judul        = trim($_POST['judul']);
    $penulis      = trim($_POST['penulis']);
    $penerbit     = trim($_POST['penerbit']);
    $tahun_terbit = trim($_POST['tahun_terbit']);
    $stok         = trim($_POST['stok']);

    // --- Validasi sederhana di sisi server ---
    // (validasi di sisi browser/HTML "required" bisa saja dilewati,
    //  jadi validasi di PHP tetap wajib ada)
    if ($judul === "" || $penulis === "" || $penerbit === "" || $tahun_terbit === "" || $stok === "") {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($tahun_terbit) || !is_numeric($stok)) {
        $error = "Tahun terbit dan stok harus berupa angka.";
    } else {
        // ---------- PREPARED STATEMENT untuk INSERT ----------
        // "ssssi" artinya: 4 parameter string (s) lalu 1 parameter integer (i),
        // urutannya harus SAMA PERSIS dengan urutan "?" di query.
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $judul, $penulis, $penerbit, $tahun_terbit, $stok
        );
        mysqli_stmt_execute($stmt);

        // Setelah berhasil simpan, redirect ke index.php supaya:
        // 1. Data langsung terlihat di daftar
        // 2. Kalau user menekan tombol "refresh" di browser, form TIDAK
        //    ter-submit ulang (pola ini disebut Post/Redirect/Get)
        header("Location: index.php?pesan=Buku berhasil ditambahkan");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:500px;">
    <h1>Tambah Buku</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- action="tambah.php" -> form ini akan dikirim (submit) ke dirinya
         sendiri, itu sebabnya kode PHP di atas punya bagian "if POST" -->
    <form method="POST" action="tambah.php">
        <div class="form-group">
            <label>Judul Buku</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Penulis</label>
            <input type="text" name="penulis" value="<?= htmlspecialchars($_POST['penulis'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Penerbit</label>
            <input type="text" name="penerbit" value="<?= htmlspecialchars($_POST['penerbit'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($_POST['tahun_terbit'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>" required>
        </div>
        <button type="submit">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
