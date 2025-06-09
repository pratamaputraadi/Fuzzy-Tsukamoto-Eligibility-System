<?php
session_start();
// Jika sudah ada siswa yang login, arahkan ke hasil atau dashboard siswa jika ada
if (isset($_SESSION['user_logged_in_student_id'])) {
    header("Location: hasil_penerimaan.php");
    exit;
}
// Jika masih dalam tahap verifikasi kode, arahkan ke tahap 2
if (isset($_SESSION['pending_verification_student_id'])) {
    header("Location: login_tahap2.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - Tahap 1 | PPDB SMK Adipati Singaperbangsa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #eef2f7;
            margin: 0;
            padding: 15px; /* Padding untuk mobile */
            box-sizing: border-box;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px; /* Sedikit lebih lebar untuk input panjang */
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .login-container label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        .login-container input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccd1d9;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .login-container input[type="text"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
        .login-container button {
            width: 100%;
            padding: 12px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 15px;
        }
        .input-hint {
            font-size: 0.9em;
            color: #666;
            margin-top: -15px;
            margin-bottom: 20px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Calon Siswa</h2>
        <p style="text-align:center; margin-bottom:20px;">Tahap 1: Verifikasi Data Diri</p>
        <?php
        if (isset($_SESSION['login_error_user'])) {
            echo '<p class="error-message">' . htmlspecialchars($_SESSION['login_error_user'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['login_error_user']);
        }
        ?>
        <form action="actions/user_auth_stage1.php" method="POST">
            <div>
                <label for="full_name">Nama Lengkap:</label>
                <input type="text" id="full_name" name="full_name" placeholder="Sesuai yang didaftarkan Admin" required>
            </div>
            <div>
                <label for="birth_details">Tempat Tanggal Lahir:</label>
                <input type="text" id="birth_details" name="birth_details" placeholder="Contoh: Bandung, 15 Agustus 2007" required>
                <small class="input-hint">Format: Tempat, DD BulanYYYY (misal: Jakarta, 05 Januari 2008)</small>
            </div>
            <button type="submit">Lanjut ke Tahap 2</button>
        </form>
        <p style="text-align:center; margin-top:20px; font-size:0.9em;">
            <a href="../admin/login.php">Login sebagai Admin</a>
        </p>
    </div>
</body>
</html>