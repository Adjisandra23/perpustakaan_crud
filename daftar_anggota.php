<?php

/**
 * =========================================================
 * FILE: daftar_anggota.php
 * FUNGSI: Menampilkan semua data anggota (peminjam buku).
 *         Polanya sama persis dengan index.php, hanya beda tabel.
 * =========================================================
 */

require "auth_check.php";
require "koneksi.php";

$result = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Anggota - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <?php include "navbar.php"; ?>

        <h1>Data Anggota</h1>

        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['pesan']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <a href="tambah_anggota.php" class="btn">+ Tambah Anggota</a>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Kelas</th>
                    <th>No. HP</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['no_hp']) ?></td>
                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                        <td>
                            <a href="edit_anggota.php?id=<?= $row['id'] ?>" class="btn btn-secondary">Edit</a>
                            <a href="hapus_anggota.php?id=<?= $row['id'] ?>"
                                class="btn btn-danger"
                                onclick="return confirm('Yakin ingin menghapus anggota ini?');">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#888;">Belum ada data anggota.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>