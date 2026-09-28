<?php

/**
 * =========================================================
 * FILE: auth_check.php
 * FUNGSI: "Penjaga pintu" -> memastikan hanya user yang SUDAH
 *         LOGIN yang boleh membuka halaman tertentu
 *         (index.php, tambah.php, edit.php, hapus.php).
 *
 * Cara pakai: taruh baris berikut di BARIS PALING ATAS
 * halaman yang ingin diproteksi:
 *      require "auth_check.php";
 * =========================================================
 */

// session_start() WAJIB dipanggil di baris paling atas setiap file
// yang menggunakan $_SESSION, dan harus SEBELUM ada output apapun
/** (termasuk sebelum tag HTML/spasi/enter di luar tag <?php ?>).*/
session_start();

// Mengecek apakah variabel session "username" ADA.
// Variabel ini baru akan ada jika user berhasil login lewat login.php
// (lihat proses di login.php, bagian $_SESSION['username'] = ...).
if (!isset($_SESSION['username'])) {
    // Jika belum login, langsung "tendang" (redirect) ke halaman login.
    // header("Location: ...") HARUS dipanggil sebelum ada output HTML apapun,
    // makanya file ini di-include di paling atas.
    header("Location: login.php");

    // exit() penting supaya kode di bawahnya (di halaman yang memanggil
    // file ini) TIDAK ikut dieksekusi setelah redirect diperintahkan.
    exit();
}
