<?php
// ==========================================
// KONEKSI DATABASE
// Sistem Presensi RPL 2
// ==========================================

// Zona waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$username = "root";
$password = "";
$database = "presensi_rpl2";

$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Menggunakan UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>