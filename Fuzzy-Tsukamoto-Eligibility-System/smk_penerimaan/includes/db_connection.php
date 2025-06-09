<?php
// File: smk_penerimaan/includes/db_connection.php

$host = "localhost";
$db_name = "db_smk_penerimaan"; // Pastikan ini nama database Anda
$username_db = "root";          // Username database (default XAMPP)
$password_db = "";              // Password database (default XAMPP kosong)

try {
    // Membuat koneksi PDO dan menyimpannya ke variabel $conn
    $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username_db, $password_db);
    
    // Mengatur mode error PDO ke exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur default fetch mode ke associative array (opsional)
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Jangan ada 'echo' di sini jika file ini akan di-include di banyak tempat
    // // echo "Koneksi ke database berhasil!"; 

} catch(PDOException $exception) {
    // Jika koneksi gagal, tampilkan pesan error dan hentikan skrip
    // Di lingkungan produksi, sebaiknya error ini di-log.
    die("KONEKSI DATABASE GAGAL: " . $exception->getMessage());
}
?>