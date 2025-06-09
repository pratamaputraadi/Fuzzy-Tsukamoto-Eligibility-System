<?php
session_start();
require_once '../../includes/db_connection.php'; // Sesuaikan path

// Cek jika admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['form_message'] = "Akses ditolak. Silakan login sebagai admin.";
    $_SESSION['form_message_type'] = "error";
    header("Location: ../login.php"); // Arahkan ke login admin jika belum login
    exit;
}

if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student_id_to_delete = $_GET['student_id'];

    // Ambil nama siswa untuk pesan (opsional, tapi bagus untuk feedback)
    $student_name = "Siswa dengan ID " . $student_id_to_delete;
    try {
        $stmt_name = $conn->prepare("SELECT full_name FROM Students WHERE student_id = :student_id");
        $stmt_name->bindParam(':student_id', $student_id_to_delete, PDO::PARAM_INT);
        $stmt_name->execute();
        if ($row = $stmt_name->fetch(PDO::FETCH_ASSOC)) {
            $student_name = htmlspecialchars($row['full_name']);
        }
    } catch (PDOException $e) {
        // Abaikan jika gagal mengambil nama, lanjutkan proses hapus
    }


    $conn->beginTransaction();
    try {
        // Karena ada ON DELETE CASCADE di tabel lain yang merujuk ke Students,
        // cukup hapus dari tabel Students.
        $stmt = $conn->prepare("DELETE FROM Students WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id_to_delete, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $conn->commit();
                $_SESSION['form_message'] = "Data siswa '" . $student_name . "' berhasil dihapus beserta semua data terkait.";
                $_SESSION['form_message_type'] = "success";
            } else {
                // Tidak ada baris yang terhapus, mungkin student_id tidak ada
                $conn->rollBack();
                $_SESSION['form_message'] = "Tidak ada data siswa yang ditemukan dengan ID tersebut untuk dihapus.";
                $_SESSION['form_message_type'] = "error";
            }
        } else {
            $conn->rollBack();
            $_SESSION['form_message'] = "Gagal menghapus data siswa.";
            $_SESSION['form_message_type'] = "error";
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        // error_log("Error deleting student: " . $e->getMessage()); // Log error
        $_SESSION['form_message'] = "Terjadi kesalahan pada database saat menghapus data siswa: " . $e->getMessage();
        $_SESSION['form_message_type'] = "error";
    }
} else {
    $_SESSION['form_message'] = "ID Siswa tidak valid atau tidak disediakan untuk dihapus.";
    $_SESSION['form_message_type'] = "error";
}

// Kembali ke halaman manage_students.php
header("Location: ../manage_students.php");
exit;
?>