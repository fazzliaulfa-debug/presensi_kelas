<?php
session_start();

// Jika sudah login, arahkan sesuai role
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Presensi RPL 2</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef4ff, #f8f5ff);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #26324a;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 15px 45px rgba(60, 70, 100, 0.12);
            text-align: center;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 22px;
            background: #e7edff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #737b8c;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info {
            margin: 25px auto 35px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .info-box {
            background: #f6f8fc;
            padding: 14px 22px;
            border-radius: 12px;
            font-size: 14px;
        }

        .info-box strong {
            display: block;
            color: #344b8e;
            margin-bottom: 4px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-login {
            background: #5269b5;
            color: white;
        }

        .btn-register {
            background: #edf1ff;
            color: #5269b5;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .footer {
            margin-top: 30px;
            color: #9aa1b0;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 35px 20px;
            }

            h1 {
                font-size: 25px;
            }

            .info {
                flex-direction: column;
            }

            .info-box {
                width: 100%;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="icon">
        📋
    </div>

    <h1>Presensi Mahasiswa</h1>

    <p class="subtitle">
        Rekayasa Perangkat Lunak 2
    </p>

    <div class="info">

        <div class="info-box">
            <strong>Ruang</strong>
            5.2
        </div>

        <div class="info-box">
            <strong>Peserta Awal</strong>
            20 Mahasiswa
        </div>

        <div class="info-box">
            <strong>Sistem</strong>
            QR Code
        </div>

    </div>

    <div class="buttons">

        <a href="login.php" class="btn btn-login">
            🔐 Login
        </a>

        <a href="register.php" class="btn btn-register">
            📝 Daftar Akun
        </a>

    </div>

    <div class="footer">
        Sistem Presensi Mahasiswa • Rekayasa Perangkat Lunak 2
    </div>

</div>

</body>
</html>