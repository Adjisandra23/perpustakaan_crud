<?php

/**
 * =========================================================
 * FILE: tambah_anggota.php
 * FUNGSI: CREATE untuk tabel anggota. Polanya identik dengan
 *         tambah.php (buku), hanya beda nama kolom.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']);
    $nim     = trim($_POST['nim']);
    $kelas   = trim($_POST['kelas']);
    $no_hp   = trim($_POST['no_hp']);
    $alamat  = trim($_POST['alamat']);

    if ($nama === "" || $nim === "" || $kelas === "") {
        $error = "Nama, NIM, dan Kelas wajib diisi.";
    } else {
        // Cek dulu apakah NIM sudah dipakai anggota lain, supaya pesan
        // errornya jelas (bukan error mysqli mentah soal UNIQUE constraint).
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM anggota WHERE nim = ?");
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($ada) {
            $error = "NIM ini sudah terdaftar untuk anggota lain.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO anggota (nama, nim, kelas, no_hp, alamat) VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sssss", $nama, $nim, $kelas, $no_hp, $alamat);
            mysqli_stmt_execute($stmt);

            header("Location: daftar_anggota.php?pesan=Anggota berhasil ditambahkan");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width:500px;">
        <h1>Tambah Anggota</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="tambah_anggota.php">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="kelas" value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>
            <button type="submit">Simpan</button>
            <a href="daftar_anggota.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>

</html>