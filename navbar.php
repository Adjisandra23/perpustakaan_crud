<?php
/**
 * =========================================================
 * FILE: navbar.php
 * FUNGSI: Menu navigasi + info user, dipakai berulang di
 *         index.php, daftar_anggota.php, daftar_peminjaman.php, dst,
 *         dengan cara: <?php include "navbar.php"; ?>
 *
 * Kenapa dipisah jadi file sendiri? Supaya kalau nanti mau
 * menambah menu baru, cukup edit SATU file ini, tidak perlu
 * mengubah setiap halaman satu-satu (prinsip DRY - Don't Repeat Yourself).
 *
 * Catatan: file ini TIDAK memanggil session_start() sendiri,
 * karena diasumsikan sudah dipanggil oleh auth_check.php di
 * halaman yang meng-include file ini.
 * =========================================================
 */

// basename($_SERVER['PHP_SELF']) -> mengambil nama file yang sedang
// dibuka, misal "index.php". Dipakai untuk menandai menu mana yang
// sedang aktif (memberi class "active").
$halaman_aktif = basename($_SERVER['PHP_SELF']);

function menu_class($nama_file, $halaman_aktif) {
    return $nama_file === $halaman_aktif ? 'nav-link active' : 'nav-link';
}
?>
<div class="topbar">
    <nav class="navbar-menu">
        <a href="index.php" class="<?= menu_class('index.php', $halaman_aktif) ?>">Data Buku</a>
        <a href="daftar_anggota.php" class="<?= menu_class('daftar_anggota.php', $halaman_aktif) ?>">Anggota</a>
        <a href="daftar_peminjaman.php" class="<?= menu_class('daftar_peminjaman.php', $halaman_aktif) ?>">Peminjaman</a>
    </nav>
    <div>
        Halo, <b><?= htmlspecialchars($_SESSION['username']) ?></b> |
        <a href="logout.php">Logout</a>
    </div>
</div>
