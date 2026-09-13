<?php
session_start();
require_once "koneksi.php";

$pesan = "";
$tipe = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST['nama']);
    $nim = trim($_POST['nim']);
    $kelas = trim($_POST['kelas']);
    $password = $_POST['password'];

    if ($nama === "" || $nim === "" || $kelas === "" || $password === "") {
        $pesan = "Semua data wajib diisi.";
        $tipe = "error";
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter.";
        $tipe = "error";
    } else {

        /* Cek NIM sudah terdaftar atau belum */
        $cek = mysqli_prepare($conn, "SELECT id FROM mahasiswa WHERE nim = ?");
        mysqli_stmt_bind_param($cek, "s", $nim);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {

            $pesan = "NIM tersebut sudah terdaftar.";
            $tipe = "error";

        } else {

            /* Password dibuat aman */
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            /* Username siswa = NIM */
            $username = $nim;

            /* Buat akun users */
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (username, password, role)
                 VALUES (?, ?, 'siswa')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $username,
                $password_hash
            );

            if (mysqli_stmt_execute($stmt)) {

                $user_id = mysqli_insert_id($conn);

                /* QR Code menggunakan NIM sebagai kode unik */
                $qr_code = $nim;

                /* Masukkan data mahasiswa */
                $stmt2 = mysqli_prepare(
                    $conn,
                    "INSERT INTO mahasiswa
                    (user_id, nim, nama, kelas, qr_code, status_akun)
                    VALUES (?, ?, ?, ?, ?, 'menunggu')"
                );

                mysqli_stmt_bind_param(
                    $stmt2,
                    "issss",
                    $user_id,
                    $nim,
                    $nama,
                    $kelas,
                    $qr_code
                );

                if (mysqli_stmt_execute($stmt2)) {

                    $pesan = "Pendaftaran berhasil! Akun kamu menunggu verifikasi admin.";
                    $tipe = "success";

                } else {

                    /* Jika data mahasiswa gagal, hapus akun users */
                    mysqli_query(
                        $conn,
                        "DELETE FROM users WHERE id = '$user_id'"
                    );

                    $pesan = "Pendaftaran gagal. Silakan coba lagi.";
                    $tipe = "error";
                }

            } else {

                $pesan = "NIM tersebut sudah memiliki akun.";
                $tipe = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Daftar Akun - Presensi RPL 2</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    min-height: 100vh;
    background: #0f172a;
    color: #e5e7eb;

    display: flex;
    justify-content: center;
    align-items: center;

    padding: 20px;
}

.container {
    width: 100%;
    max-width: 500px;

    background: #111827;

    border: 1px solid #1f2937;

    border-radius: 18px;

    padding: 35px;

    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}

.logo {
    text-align: center;
    font-size: 40px;
    margin-bottom: 15px;
}

h1 {
    text-align: center;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #94a3b8;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
    color: #cbd5e1;
}

input {
    width: 100%;
    padding: 13px;

    border-radius: 9px;

    border: 1px solid #334155;

    background: #0f172a;

    color: white;

    outline: none;
}

input:focus {
    border-color: #3b82f6;
}

button {
    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 9px;

    background: #2563eb;

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.message {
    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 14px;
}

.success {
    background: #064e3b;
    color: #a7f3d0;
}

.error {
    background: #7f1d1d;
    color: #fecaca;
}

.back {
    display: block;

    text-align: center;

    margin-top: 20px;

    color: #93c5fd;

    text-decoration: none;

    font-size: 14px;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">📝</div>

    <h1>Daftar Akun Siswa</h1>

    <p class="subtitle">
        Rekayasa Perangkat Lunak 2
    </p>

    <?php if ($pesan !== ""): ?>

        <div class="message <?= $tipe; ?>">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">

            <label>Nama Lengkap</label>

            <input
                type="text"
                name="nama"
                placeholder="Masukkan nama lengkap"
                required
            >

        </div>

        <div class="form-group">

            <label>NIM</label>

            <input
                type="text"
                name="nim"
                placeholder="Masukkan NIM"
                required
            >

        </div>

        <div class="form-group">

            <label>Kelas</label>

            <input
                type="text"
                name="kelas"
                placeholder="Contoh: TI-2"
                value="TI-2"
                required
            >

        </div>

        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Minimal 6 karakter"
                required
            >

        </div>

        <button type="submit">
            Daftar Akun
        </button>

    </form>

    <a href="login.php" class="back">
        ← Kembali ke Login
    </a>

</div>

</body>

</html>