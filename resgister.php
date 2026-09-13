<?php
session_start();
require_once "koneksi.php";

$pesan = "";
$tipe = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"]);
    $nim = trim($_POST["nim"]);
    $kelas = trim($_POST["kelas"]);
    $password = trim($_POST["password"]);

    if ($nama === "" || $nim === "" || $kelas === "" || $password === "") {

        $pesan = "Semua data harus diisi.";
        $tipe = "error";

    } elseif (strlen($password) < 6) {

        $pesan = "Password minimal 6 karakter.";
        $tipe = "error";

    } else {

        // Cek apakah NIM sudah terdaftar
        $cek = mysqli_prepare(
            $conn,
            "SELECT id FROM mahasiswa WHERE nim = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "s", $nim);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {

            $pesan = "NIM tersebut sudah terdaftar.";
            $tipe = "error";

        } else {

            // Username siswa menggunakan NIM
            $username = $nim;

            // Password dibuat aman menggunakan hash
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan akun siswa
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

                /*
                 * QR Code menggunakan NIM sebagai kode unik.
                 * Nanti halaman QR akan menampilkan QR
                 * berdasarkan kode ini.
                 */
                $qr_code = $nim;

                // Simpan data mahasiswa
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

                    $pesan = "Pendaftaran berhasil! Akun kamu sedang menunggu verifikasi admin.";
                    $tipe = "success";

                } else {

                    // Jika data mahasiswa gagal disimpan,
                    // akun yang tadi dibuat ikut dihapus.
                    mysqli_query(
                        $conn,
                        "DELETE FROM users WHERE id = " . intval($user_id)
                    );

                    $pesan = "Pendaftaran gagal. Silakan coba lagi.";
                    $tipe = "error";
                }

                mysqli_stmt_close($stmt2);

            } else {

                $pesan = "Pendaftaran akun gagal.";
                $tipe = "error";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek);
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
            background:
                radial-gradient(circle at top left, #26355f 0%, transparent 35%),
                radial-gradient(circle at bottom right, #34264f 0%, transparent 35%),
                #0d1220;

            color: #f1f3f8;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 25px;
        }

        .register-box {
            width: 100%;
            max-width: 480px;

            background: #171d2d;

            border: 1px solid #29334b;

            border-radius: 22px;

            padding: 38px;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .logo {
            width: 70px;
            height: 70px;

            margin: 0 auto 18px;

            border-radius: 18px;

            background: #252f50;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 34px;
        }

        h1 {
            text-align: center;

            font-size: 27px;

            margin-bottom: 8px;

            color: #ffffff;
        }

        .subtitle {
            text-align: center;

            color: #9ca6bb;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            color: #dce1ec;

            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 14px 15px;

            background: #101625;

            border: 1px solid #303a53;

            border-radius: 11px;

            color: #ffffff;

            outline: none;

            font-size: 14px;
        }

        input::placeholder {
            color: #68738a;
        }

        input:focus {
            border-color: #7186d5;

            background: #12192a;
        }

        .info {
            background: #202940;

            border-left: 4px solid #7186d5;

            padding: 13px 14px;

            border-radius: 9px;

            margin-bottom: 20px;

            color: #aeb8ca;

            font-size: 13px;

            line-height: 1.6;
        }

        button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 11px;

            background: #6176c5;

            color: #ffffff;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        button:hover {
            background: #7186d5;

            transform: translateY(-1px);
        }

        .message {
            padding: 13px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;

            line-height: 1.5;
        }

        .message.error {
            background: #382027;

            border: 1px solid #63333f;

            color: #ffb4c0;
        }

        .message.success {
            background: #1d382e;

            border: 1px solid #315e4c;

            color: #a9e7c9;
        }

        .login-link {
            text-align: center;

            margin-top: 20px;

            color: #8f99ad;

            font-size: 14px;
        }

        .login-link a {
            color: #8498e5;

            text-decoration: none;

            font-weight: bold;
        }

        .back {
            display: block;

            text-align: center;

            margin-top: 18px;

            color: #788398;

            text-decoration: none;

            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="register-box">

    <div class="logo">
        📝
    </div>

    <h1>Daftar Akun</h1>

    <p class="subtitle">
        Presensi Rekayasa Perangkat Lunak 2<br>
        Ruang 5.2
    </p>

    <?php if ($pesan !== ""): ?>

        <div class="message <?= htmlspecialchars($tipe); ?>">
            <?= htmlspecialchars($pesan); ?>
        </div>

    <?php endif; ?>

    <div class="info">
        Setelah mendaftar, akun akan diperiksa oleh admin.
        Akun baru berstatus <strong>Menunggu Verifikasi</strong>
        sampai diterima oleh admin.
    </div>

    <form method="POST">

        <div class="form-group">

            <label for="nama">
                Nama Lengkap
            </label>

            <input
                type="text"
                id="nama"
                name="nama"
                placeholder="Masukkan nama lengkap"
                required
            >

        </div>

        <div class="form-group">

            <label for="nim">
                NIM
            </label>

            <input
                type="text"
                id="nim"
                name="nim"
                placeholder="Masukkan NIM"
                required
            >

        </div>

        <div class="form-group">

            <label for="kelas">
                Kelas
            </label>

            <input
                type="text"
                id="kelas"
                name="kelas"
                placeholder="Contoh: TI-2"
                required
            >

        </div>

        <div class="form-group">

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 6 karakter"
                required
            >

        </div>

        <button type="submit">
            Daftar Akun
        </button>

    </form>

    <div class="login-link">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </div>

    <a href="index.php" class="back">
        ← Kembali ke halaman utama
    </a>

</div>

</body>

</html>