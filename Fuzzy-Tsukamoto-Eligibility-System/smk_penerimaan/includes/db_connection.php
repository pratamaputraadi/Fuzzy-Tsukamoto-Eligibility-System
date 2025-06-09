<?php
$host = "localhost";
$db_name = "db_smk_penerimaan";
$username_db = "root";
$password_db = "";

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username_db, $password_db);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $exception) {
    die("KONEKSI DATABASE GAGAL: " . $exception->getMessage());
}
?>