<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika ada non-breaking space di sini, itu bisa jadi masalah, pastikan bersih
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
    <title>Dashboard Admin - SMK</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7; /* Warna latar serupa dengan login */
            margin: 0;
            padding: 0;
            color: #333; /* Warna teks default */
        }

        .dashboard-header {
            background-color: #007bff; /* Warna biru primer seperti tombol login */
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header .header-title {
            font-size: 22px;
            font-weight: 600;
        }

        .dashboard-header .logout-button {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background-color: #0056b3; /* Warna biru lebih gelap untuk logout */
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .dashboard-header .logout-button:hover {
            background-color: #004494; /* Lebih gelap lagi saat hover */
        }

        .dashboard-container {
            padding: 30px;
            max-width: 1200px; /* Lebar maksimum konten dashboard */
            margin: 20px auto; /* Tengahkan container */
        }

        .dashboard-container h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .dashboard-container p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }

        .menu-section {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Shadow serupa login-container */
            margin-top: 30px;
        }

        .menu-section h2 {
            font-size: 22px;
            color: #007bff; /* Warna biru untuk judul menu */
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #eef2f7; /* Garis bawah halus */
            padding-bottom: 10px;
        }

        .menu-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-section ul li {
            margin-bottom: 12px;
        }

        .menu-section ul li a {
            color: #0056b3; /* Warna link menu */
            text-decoration: none;
            font-size: 17px;
            padding: 8px 0; /* Beri sedikit padding vertikal */
            display: block; /* Agar padding bisa diterapkan */
            transition: color 0.3s ease;
        }

        .menu-section ul li a:hover {
            color: #007bff; /* Warna link saat hover */
            text-decoration: underline;
        }

        /* Responsif sederhana */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .dashboard-header .logout-button {
                margin-top: 10px;
            }
            .dashboard-container {
                padding: 20px;
            }
            .dashboard-container h1 {
                font-size: 24px;
            }
            .menu-section h2 {
                font-size: 20px;
            }
            .menu-section ul li a {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-title">Dashboard Admin</div>
        <a href="actions/admin_logout.php" class="logout-button">Logout</a>
    </div>

    <div class="dashboard-container">
        <h1>Selamat Datang di Dashboard Admin, <?php echo htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p>Ini adalah halaman dashboard admin. Anda bisa menambahkan menu dan fungsionalitas di sini untuk mengelola sistem penerimaan siswa baru SMK.</p>

        <div class="menu-section">
            <h2>Menu Utama</h2>
            <ul>
                <li><a href="register_student.php">Daftarkan Akun Siswa Baru</a></li>
                <li><a href="manage_students.php">Kelola Data Siswa & Input Nilai</a></li>
                </ul>
        </div>
    </div>
</body>
</html>