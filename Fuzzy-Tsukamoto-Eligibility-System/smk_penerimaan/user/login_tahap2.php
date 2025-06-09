<?php
session_start();

// Jika belum ada student_id yang menunggu verifikasi, arahkan ke tahap 1
if (!isset($_SESSION['pending_verification_student_id'])) {
    header("Location: login_tahap1.php");
    exit;
}
// Jika sudah login penuh, arahkan ke hasil
if (isset($_SESSION['user_logged_in_student_id'])) {
    header("Location: hasil_penerimaan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - Tahap 2 | PPDB SMK Adipati Singaperbangsa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #eef2f7;
            margin: 0;
            padding: 15px;
            box-sizing: border-box;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
            font-size: 24px;
        }
         .login-container p.subtitle {
            text-align:center;
            margin-bottom:20px;
            color: #555;
        }
        .login-container label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] /* Bisa juga type="number" atau "tel" */ {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccd1d9;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            text-align: center; /* Kode biasanya pendek, tengahkan */
        }
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }
        .login-container button {
            width: 100%;
            padding: 12px 15px;
            background-color: #28a745; /* Warna hijau untuk verifikasi */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .login-container button:hover {
            background-color: #218838;
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
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Verifikasi Kode</h2>
        <p class="subtitle">Tahap 2: Masukkan Kode Verifikasi Anda</p>
        <?php
        if (isset($_SESSION['login_error_user_tahap2'])) {
            echo '<p class="error-message">' . htmlspecialchars($_SESSION['login_error_user_tahap2'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['login_error_user_tahap2']);
        }
        ?>
        <form action="actions/user_auth_stage2.php" method="POST">
            <div>
                <label for="verification_code">Kode Verifikasi:</label>
                <input type="text" id="verification_code" name="verification_code" maxlength="6" placeholder="DDMMYY" required>
                <small class="input-hint">Kode adalah tanggal lahir Anda format DDMMYY (Contoh: 150807 jika lahir 15 Agustus 2007)</small>
            </div>
            <button type="submit">Verifikasi & Lihat Hasil</button>
        </form>
         <p style="text-align:center; margin-top:20px; font-size:0.9em;">
            <a href="login_tahap1.php">Kembali ke Tahap 1</a>
        </p>
    </div>
</body>
</html>