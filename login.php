<?php
/**
 * =========================================================
 * FILE: login.php
 * FUNGSI: Menampilkan form login DAN memproses login.
 *
 * Satu file ini punya 2 "peran" tergantung method HTTP:
 *  - Method GET  -> user baru membuka halaman -> tampilkan form
 *  - Method POST -> user menekan tombol "Login" -> proses cek
 *                   username & password ke database
 * =========================================================
 */

session_start();       // wajib, karena kita akan menyimpan data ke $_SESSION
require "koneksi.php"; // supaya variabel $koneksi bisa dipakai di sini

// Kalau user yang SUDAH login membuka login.php lagi,
// langsung saja arahkan ke halaman utama (index.php).
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = ""; // menampung pesan error kalau login gagal

// $_SERVER['REQUEST_METHOD'] berisi "GET" atau "POST".
// Blok ini hanya berjalan saat form di-submit (method POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil input dari form. trim() menghapus spasi di awal/akhir.
    // Kita TIDAK langsung memasukkan input mentah ke query SQL demi
    // mencegah SQL Injection -> makanya nanti pakai prepared statement.
    $username = trim($_POST['username']);
    $password_input = $_POST['password'];

    if ($username === "" || $password_input === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        // ---------- PREPARED STATEMENT (anti SQL Injection) ----------
        // "?" adalah placeholder yang nanti diisi lewat bind_param(),
        // sehingga input user tidak pernah langsung ditempel ke teks SQL.
        $stmt = mysqli_prepare($koneksi, "SELECT id, username, password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username); // "s" = tipe data string
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result); // ambil 1 baris data user (atau null kalau tidak ketemu)

        // password_verify() membandingkan password yang diketik user
        // dengan hash yang tersimpan di database (dibuat oleh password_hash()).
        // Kita TIDAK PERNAH menyimpan atau membandingkan password asli/polos.
        if ($user && password_verify($password_input, $user['password'])) {
            // Login berhasil -> simpan data ke session supaya "diingat"
            // selama user berpindah-pindah halaman.
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit();
        } else {
            // Sengaja memakai pesan generik ("username ATAU password salah")
            // bukan spesifik ("username tidak ditemukan"), supaya orang
            // yang mencoba menebak-nebak tidak tahu username mana yang valid.
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width:400px; margin-top:60px;">
        <h1>Login</h1>
        <p>Sistem Manajemen Perpustakaan</p>

        <?php if ($error): ?>
            <!-- htmlspecialchars() mencegah error/isi yang mengandung tag HTML
                 dieksekusi sebagai HTML (proteksi dasar dari XSS) -->
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <p style="margin-top:20px; font-size:13px; color:#777;">
            Akun default: <b>admin</b> / <b>admin123</b>
        </p>
    </div>
</body>
</html>
