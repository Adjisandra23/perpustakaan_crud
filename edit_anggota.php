<?php

/**
 * =========================================================
 * FILE: edit_anggota.php
 * FUNGSI: UPDATE untuk tabel anggota.
 *         PENTING: mengikuti pola yang sudah diperbaiki di edit.php
 *         -> id diambil dari $_GET saat GET, dan dari $_POST saat POST.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nama    = trim($_POST['nama']);
    $nim     = trim($_POST['nim']);
    $kelas   = trim($_POST['kelas']);
    $no_hp   = trim($_POST['no_hp']);
    $alamat  = trim($_POST['alamat']);

    if ($id_post === 0) {
        header("Location: daftar_anggota.php");
        exit();
    }

    if ($nama === "" || $nim === "" || $kelas === "") {
        $error = "Nama, NIM, dan Kelas wajib diisi.";
    } else {
        // Cek NIM dipakai anggota LAIN (bukan dirinya sendiri) -> id != ?
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM anggota WHERE nim = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $nim, $id_post);
        mysqli_stmt_execute($stmt);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($ada) {
            $error = "NIM ini sudah dipakai anggota lain.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE anggota SET nama=?, nim=?, kelas=?, no_hp=?, alamat=? WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "sssssi", $nama, $nim, $kelas, $no_hp, $alamat, $id_post);
            mysqli_stmt_execute($stmt);

            header("Location: daftar_anggota.php?pesan=Anggota berhasil diperbarui");
            exit();
        }
    }

    $anggota = $_POST;
} else {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id === 0) {
        header("Location: daftar_anggota.php");
        exit();
    }

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM anggota WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);

    if (!$anggota) {
        header("Location: daftar_anggota.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Anggota</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width:500px;">
        <h1>Edit Anggota</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_anggota.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($anggota['id']) ?>">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($anggota['nama']) ?>" required>
            </div>
            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" value="<?= htmlspecialchars($anggota['nim']) ?>" required>
            </div>
            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="kelas" value="<?= htmlspecialchars($anggota['kelas']) ?>" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($anggota['no_hp']) ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?= htmlspecialchars($anggota['alamat']) ?>">
            </div>
            <button type="submit">Update</button>
            <a href="daftar_anggota.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>

</html>