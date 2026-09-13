<?php
session_start();
require_once "koneksi.php";

// Kalau sudah login
if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    }

    if ($_SESSION['role'] === 'siswa') {
        header("Location: siswa/dashboard.php");
        exit;
    }
}

$error = "";

// Jika form login dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === "" || $password === "") {

        $error = "Username dan password harus diisi.";

    } else {

        /*
        =====================================================
        CARI AKUN
        =====================================================
        */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, role
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            /*
            =====================================================
            CEK PASSWORD
            =====================================================
            */

            $password_valid = false;

            // Untuk password yang dibuat menggunakan password_hash()
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            }

            // Untuk akun admin lama yang password-nya masih biasa
            if ($user['password'] === $password) {
                $password_valid = true;
            }


            if ($password_valid) {

                /*
                =================================================
                JIKA LOGIN SEBAGAI SISWA
                =================================================
                */

                if ($user['role'] === 'siswa') {

                    // Cari data mahasiswa berdasarkan user_id
                    $stmt_siswa = mysqli_prepare(
                        $conn,
                        "SELECT id, nim, nama, kelas, status_akun
                         FROM mahasiswa
                         WHERE user_id = ?
                         LIMIT 1"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_siswa,
                        "i",
                        $user['id']
                    );

                    mysqli_stmt_execute($stmt_siswa);

                    $result_siswa = mysqli_stmt_get_result($stmt_siswa);

                    if (mysqli_num_rows($result_siswa) === 1) {

                        $siswa = mysqli_fetch_assoc($result_siswa);

                        /*
                        -----------------------------------------
                        AKUN MENUNGGU
                        -----------------------------------------
                        */

                        if ($siswa['status_akun'] === 'menunggu') {

                            $error = "Akun kamu masih menunggu verifikasi admin.";

                        /*
                        -----------------------------------------
                        AKUN DITOLAK
                        -----------------------------------------
                        */

                        } elseif ($siswa['status_akun'] === 'ditolak') {

                            $error = "Akun kamu ditolak oleh admin.";

                        /*
                        -----------------------------------------
                        AKUN AKTIF
                        -----------------------------------------
                        */

                        } elseif ($siswa['status_akun'] === 'aktif') {

                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = 'siswa';

                            header("Location: siswa/dashboard.php");
                            exit;

                        } else {

                            $error = "Status akun tidak dikenali.";
                        }

                    } else {

                        $error = "Data mahasiswa tidak ditemukan.";
                    }

                    mysqli_stmt_close($stmt_siswa);

                /*
                =================================================
                JIKA LOGIN SEBAGAI ADMIN
                =================================================
                */

                } elseif ($user['role'] === 'admin') {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = 'admin';

                    header("Location: admin/dashboard.php");
                    exit;

                } else {

                    $error = "Role akun tidak dikenali.";
                }

            } else {

                $error = "Username atau password salah.";
            }

        } else {

            $error = "Username atau password salah.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Presensi RPL 2</title>

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

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;

            color: #e5e7eb;
        }

        .login-box {
            width: 100%;
            max-width: 430px;

            background: #111827;

            border: 1px solid #1f2937;

            padding: 40px;

            border-radius: 20px;

            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .logo {
            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            background: #1e3a8a;

            border-radius: 18px;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 34px;
        }

        h1 {
            text-align: center;

            color: #f8fafc;

            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;

            color: #94a3b8;

            font-size: 14px;

            margin-bottom: 30px;
        }

        label {
            display: block;

            margin-bottom: 8px;

            color: #cbd5e1;

            font-size: 14px;

            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 14px;

            border: 1px solid #334155;

            border-radius: 10px;

            margin-bottom: 18px;

            outline: none;

            font-size: 14px;

            background: #0f172a;

            color: white;
        }

        input:focus {
            border-color: #3b82f6;
        }

        button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 10px;

            background: #2563eb;

            color: white;

            font-weight: bold;

            font-size: 15px;

            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #450a0a;

            color: #fecaca;

            padding: 12px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;
        }

        .register {
            text-align: center;

            margin-top: 20px;

            font-size: 14px;

            color: #94a3b8;
        }

        .register a {
            color: #60a5fa;

            text-decoration: none;

            font-weight: bold;
        }

        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: #94a3b8;

            text-decoration: none;

            font-size: 14px;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        🔐
    </div>

    <h1>Login</h1>

    <p class="subtitle">
        Presensi Rekayasa Perangkat Lunak 2
    </p>

    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label for="username">
            Username
        </label>

        <input
            type="text"
            id="username"
            name="username"
            placeholder="Masukkan username"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

    <div class="register">
        Belum punya akun?
        <a href="register.php">Daftar sekarang</a>
    </div>

    <a href="index.php" class="back">
        ← Kembali ke halaman utama
    </a>

</div>

</body>

</html>