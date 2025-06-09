<?php
session_start();
require_once '../../includes/db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['form_message'] = "Akses ditolak. Silakan login sebagai admin.";
    $_SESSION['form_message_type'] = "error";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $birth_place = trim($_POST['birth_place']);
    $birth_date = trim($_POST['birth_date']);
    $origin_school = trim($_POST['origin_school']);

    if (empty($full_name) || empty($birth_place) || empty($birth_date) || empty($origin_school)) {
        $_SESSION['form_message'] = "Semua kolom wajib diisi.";
        $_SESSION['form_message_type'] = "error";
        header("Location: ../register_student.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Students (full_name, birth_place, birth_date, origin_school) VALUES (:full_name, :birth_place, :birth_date, :origin_school)");

        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':birth_place', $birth_place);
        $stmt->bindParam(':birth_date', $birth_date);
        $stmt->bindParam(':origin_school', $origin_school);

        if ($stmt->execute()) {
            $_SESSION['form_message'] = "Akun siswa baru untuk " . htmlspecialchars($full_name) . " berhasil dibuat.";
            $_SESSION['form_message_type'] = "success";
        } else {
            $_SESSION['form_message'] = "Gagal membuat akun siswa. Terjadi kesalahan.";
            $_SESSION['form_message_type'] = "error";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['form_message'] = "Gagal: Data siswa mungkin sudah ada atau ada data yang tidak unik.";
        } else {
            $_SESSION['form_message'] = "Terjadi kesalahan pada database: " . $e->getMessage();
        }
        $_SESSION['form_message_type'] = "error";
    }
    header("Location: ../register_student.php");
    exit;

} else {
    $_SESSION['form_message'] = "Metode tidak diizinkan.";
    $_SESSION['form_message_type'] = "error";
    header("Location: ../register_student.php");
    exit;
}
?>