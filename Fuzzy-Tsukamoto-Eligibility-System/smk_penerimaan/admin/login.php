<?php
session_start(); // Mulai session di baris paling atas dari skrip PHP

// Jika admin sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit; // Pastikan exit setelah header untuk menghentikan eksekusi skrip lebih lanjut
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SMK Adipati Singaperbangsa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #eef2f7; /* Warna latar yang lebih lembut */
            margin: 0;
            padding: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px 40px; /* Padding lebih besar */
            border-radius: 10px; /* Border radius lebih besar */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Shadow lebih jelas */
            width: 100%;
            max-width: 400px; /* Lebar maksimum container */
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333; /* Warna teks header */
            font-size: 24px; /* Ukuran font header */
        }
        .login-container label {
            display: block;
            margin-bottom: 8px; /* Margin bawah label */
            color: #555; /* Warna teks label */
            font-weight: 600; /* Ketebalan font label */
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px 15px; /* Padding input lebih besar */
            margin-bottom: 20px; /* Margin bawah input lebih besar */
            border: 1px solid #ccd1d9; /* Warna border input */
            border-radius: 6px; /* Border radius input */
            box-sizing: border-box;
            font-size: 16px; /* Ukuran font input */
        }
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff; /* Warna border saat fokus */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Efek shadow saat fokus */
            outline: none;
        }
        .login-container button {
            width: 100%;
            padding: 12px 15px; /* Padding tombol */
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px; /* Border radius tombol */
            cursor: pointer;
            font-size: 16px; /* Ukuran font tombol */
            font-weight: 600; /* Ketebalan font tombol */
            transition: background-color 0.3s ease; /* Transisi hover */
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #d9534f; /* Warna merah untuk error */
            background-color: #f2dede; /* Latar belakang pesan error */
            border: 1px solid #ebccd1; /* Border pesan error */
            padding: 10px 15px; /* Padding pesan error */
            border-radius: 6px; /* Border radius pesan error */
            text-align: center;
            margin-bottom: 20px; /* Margin bawah pesan error */
            font-size: 15px; /* Ukuran font pesan error */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Admin</h2>
        <?php
        // Menampilkan pesan error jika ada
        if (isset($_SESSION['login_error'])) {
            echo '<p class="error-message">' . htmlspecialchars($_SESSION['login_error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['login_error']); // Hapus pesan error dari session setelah ditampilkan
        }
        ?>
        <form action="actions/admin_login_process.php" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>