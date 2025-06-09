<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftarkan Akun Siswa Baru - Dashboard Admin</title>
    <link rel="stylesheet" href="../css/admin_style.css"> <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .page-header {
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header .header-title { font-size: 22px; font-weight: 600; }
        .page-header a { color: white; text-decoration: none; padding: 8px 15px; background-color: #0056b3; border-radius: 5px; }
        .page-header a:hover { background-color: #004494; }

        .form-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 600px;
            margin: 30px auto;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        .form-container input[type="text"],
        .form-container input[type="date"],
        .form-container textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccd1d9;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-container input[type="text"]:focus,
        .form-container input[type="date"]:focus,
        .form-container textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
            outline: none;
        }
        .form-container button {
            width: 100%;
            padding: 12px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .message.success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .message.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-title">Daftarkan Akun Siswa Baru</div>
        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <div class="form-container">
        <h2>Form Pendaftaran Akun Siswa</h2>
        <?php
        if (isset($_SESSION['form_message'])) {
            $message_type = isset($_SESSION['form_message_type']) && $_SESSION['form_message_type'] == 'error' ? 'error' : 'success';
            echo '<p class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['form_message']) . '</p>';
            unset($_SESSION['form_message']);
            unset($_SESSION['form_message_type']);
        }
        ?>
        <form action="actions/create_student_account.php" method="POST">
            <div>
                <label for="full_name">Nama Lengkap Siswa:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div>
                <label for="birth_place">Tempat Lahir:</label>
                <input type="text" id="birth_place" name="birth_place" required>
            </div>
            <div>
                <label for="birth_date">Tanggal Lahir:</label>
                <input type="date" id="birth_date" name="birth_date" required>
            </div>
            <div>
                <label for="origin_school">SMP Asal:</label>
                <input type="text" id="origin_school" name="origin_school" required>
            </div>
            <button type="submit">Buat Akun Siswa</button>
        </form>
    </div>
</body>
</html>