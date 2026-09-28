<?php
/**
 * =========================================================
 * FILE: logout.php
 * FUNGSI: Mengakhiri sesi login user, lalu kembali ke login.php
 * =========================================================
 */

session_start();

// session_unset() -> menghapus SEMUA variabel di dalam $_SESSION
session_unset();

// session_destroy() -> menghancurkan data sesi di sisi server
session_destroy();

// Setelah sesi dihapus, arahkan kembali ke halaman login
header("Location: login.php");
exit();
