<?php
session_start();
require_once '../../includes/db_connection.php'; // Sesuaikan path ke db_connection.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username dan password tidak boleh kosong.";
        header("Location: ../login.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT admin_id, username, password_hash FROM Admins WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // PERUBAHAN KRUSIAL DI SINI:
            if ($password === $admin['password_hash']) { // Menggunakan perbandingan string biasa
                // Password cocok
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Hapus pesan error jika ada sebelumnya
                unset($_SESSION['login_error']);

                header("Location: ../dashboard.php"); // Redirect ke dashboard admin
                exit;
            } else {
                // Password tidak cocok
                $_SESSION['login_error'] = "Username atau password salah.";
                header("Location: ../login.php");
                exit;
            }
        } else {
            // Username tidak ditemukan
            $_SESSION['login_error'] = "Username atau password salah.";
            header("Location: ../login.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Admin Login Error: " . $e->getMessage());
        $_SESSION['login_error'] = "Terjadi kesalahan pada server.";
        header("Location: ../login.php");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>