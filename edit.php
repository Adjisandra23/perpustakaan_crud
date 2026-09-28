<?php
/**
 * =========================================================
 * FILE: edit.php
 * FUNGSI: Bagian "UPDATE" dari CRUD.
 *         - Method GET  -> ambil data lama berdasarkan id, tampilkan di form
 *         - Method POST -> update data di database
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$error = "";

// --- PENTING: id harus diambil dari sumber yang BERBEDA tergantung method ---
// Saat GET  (baru buka halaman)     -> id datang dari URL: edit.php?id=4
// Saat POST (form baru di-submit)   -> URL tidak punya ?id=..., jadi id
//                                      harus diambil dari <input type="hidden"
//                                      name="id"> yang ada di dalam form.
// (Bug versi sebelumnya: id selalu diambil dari $_GET saja, padahal saat
//  POST, $_GET['id'] kosong -> dianggap 0 -> halaman langsung redirect ke
//  index.php SEBELUM sempat menjalankan query UPDATE-nya sama sekali.)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ----- Proses UPDATE (form sudah di-submit) -----
    $id_post      = isset($_POST['id']) ? (int) $_POST['id'] : 0; // id dari hidden input
    $judul        = trim($_POST['judul']);
    $penulis      = trim($_POST['penulis']);
    $penerbit     = trim($_POST['penerbit']);
    $tahun_terbit = trim($_POST['tahun_terbit']);
    $stok         = trim($_POST['stok']);

    if ($id_post === 0) {
        // Jaga-jaga kalau hidden input id hilang/dirusak
        header("Location: index.php");
        exit();
    }

    if ($judul === "" || $penulis === "" || $penerbit === "" || $tahun_terbit === "" || $stok === "") {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($tahun_terbit) || !is_numeric($stok)) {
        $error = "Tahun terbit dan stok harus berupa angka.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, stok=? WHERE id=?"
        );
        // "sssiii" -> 3 string, lalu 3 integer (tahun_terbit, stok, id)
        mysqli_stmt_bind_param(
            $stmt,
            "sssiii",
            $judul, $penulis, $penerbit, $tahun_terbit, $stok, $id_post
        );
        mysqli_stmt_execute($stmt);

        header("Location: index.php?pesan=Buku berhasil diperbarui");
        exit();
    }

    // Kalau validasi gagal, data yang sedang ditampilkan di form
    // adalah data yang baru saja diketik user (dari $_POST), bukan data lama.
    $buku = $_POST;
} else {
    // ----- Method GET: ambil data lama dari database untuk ditampilkan -----
    // Di sinilah id datang dari URL, contoh: edit.php?id=4
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id === 0) {
        header("Location: index.php");
        exit();
    }

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $buku = mysqli_fetch_assoc($result);

    // Kalau id di URL tidak ada di database, kembalikan ke index.php
    if (!$buku) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:500px;">
    <h1>Edit Buku</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php">
        <!-- hidden input: dikirim ikut form tapi tidak terlihat oleh user,
             supaya PHP tahu baris mana yang harus di-UPDATE -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($buku['id']) ?>">

        <div class="form-group">
            <label>Judul Buku</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required>
        </div>
        <div class="form-group">
            <label>Penulis</label>
            <input type="text" name="penulis" value="<?= htmlspecialchars($buku['penulis']) ?>" required>
        </div>
        <div class="form-group">
            <label>Penerbit</label>
            <input type="text" name="penerbit" value="<?= htmlspecialchars($buku['penerbit']) ?>" required>
        </div>
        <div class="form-group">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit']) ?>" required>
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" value="<?= htmlspecialchars($buku['stok']) ?>" required>
        </div>
        <button type="submit">Update</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
