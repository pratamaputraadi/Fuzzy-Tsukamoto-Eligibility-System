<?php
session_start();
require_once '../../includes/db_connection.php'; // Sesuaikan path

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['form_message'] = "Akses ditolak."; // Pesan untuk halaman tujuan
    $_SESSION['form_message_type'] = "error";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $scores = isset($_POST['scores']) && is_array($_POST['scores']) ? $_POST['scores'] : [];
    $height_cm = trim($_POST['height_cm']);
    $weight_kg = trim($_POST['weight_kg']);

    // Validasi dasar
    if (empty($student_id) || !is_numeric($student_id) || empty($height_cm) || !is_numeric($height_cm) || empty($weight_kg) || !is_numeric($weight_kg) || count($scores) < 12) { // Asumsi 12 mapel
        $_SESSION['form_message'] = "Semua data nilai (12 mata pelajaran) dan data fisik (angka valid) wajib diisi.";
        $_SESSION['form_message_type'] = "error";
        header("Location: ../input_student_data.php?student_id=" . $student_id);
        exit;
    }

    // Validasi lebih lanjut untuk nilai apakah numerik dan dalam rentang
    foreach ($scores as $subject_name => $score_value) {
        if ($score_value === '' || !is_numeric($score_value) || $score_value < 0 || $score_value > 100) {
            $_SESSION['form_message'] = "Nilai untuk " . htmlspecialchars($subject_name) . " tidak valid (harus angka antara 0-100).";
            $_SESSION['form_message_type'] = "error";
            header("Location: ../input_student_data.php?student_id=" . $student_id);
            exit;
        }
    }


    $conn->beginTransaction(); 

    try {
        // Hapus nilai lama siswa (jika ada, untuk simplifikasi UPSERT)
        $stmt_delete_scores = $conn->prepare("DELETE FROM Student_Scores WHERE student_id = :student_id");
        $stmt_delete_scores->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_delete_scores->execute();

        // Insert nilai baru
        $stmt_insert_score = $conn->prepare("INSERT INTO Student_Scores (student_id, subject_name, score_value) VALUES (:student_id, :subject_name, :score_value)");
        foreach ($scores as $subject_name => $score_value) {
            // Validasi sudah dilakukan di atas, jadi langsung bind
            $stmt_insert_score->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt_insert_score->bindParam(':subject_name', $subject_name);
            $stmt_insert_score->bindParam(':score_value', $score_value);
            $stmt_insert_score->execute();
        }

        // Proses data fisik (UPSERT menggunakan INSERT ... ON DUPLICATE KEY UPDATE)
        // Pastikan student_id di Student_Physical_Data adalah UNIQUE KEY
        $stmt_physical_upsert = $conn->prepare("
            INSERT INTO Student_Physical_Data (student_id, height_cm, weight_kg) 
            VALUES (:student_id, :height_cm, :weight_kg) 
            ON DUPLICATE KEY UPDATE 
            height_cm = VALUES(height_cm), weight_kg = VALUES(weight_kg)
        ");
        $stmt_physical_upsert->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_physical_upsert->bindParam(':height_cm', $height_cm, PDO::PARAM_INT);
        $stmt_physical_upsert->bindParam(':weight_kg', $weight_kg, PDO::PARAM_INT);
        $stmt_physical_upsert->execute();

        $conn->commit(); 

        $_SESSION['form_message'] = "Data nilai dan fisik siswa berhasil disimpan. Silakan lanjutkan ke pemilihan jurusan.";
        $_SESSION['form_message_type'] = "success";
        // PERUBAHAN REDIRECT: Arahkan ke halaman pemilihan jurusan
        header("Location: ../select_major.php?student_id=" . $student_id); 
        exit;

    } catch (Exception $e) {
        $conn->rollBack(); 
        $_SESSION['form_message'] = "Gagal menyimpan data: " . $e->getMessage();
        $_SESSION['form_message_type'] = "error";
        // Jika error, kembali ke halaman input data
        header("Location: ../input_student_data.php?student_id=" . $student_id);
        exit;
    }
    
} else {
    $_SESSION['form_message'] = "Metode tidak diizinkan atau ID siswa tidak ada.";
    $_SESSION['form_message_type'] = "error";
    $redirect_location = "../manage_students.php"; // Default redirect
    if(isset($_POST['student_id']) && is_numeric($_POST['student_id'])) { // Jika student_id ada di POST tapi kondisi lain gagal
        $redirect_location = "../input_student_data.php?student_id=".$_POST['student_id'];
    } elseif (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) { // Jika student_id ada di GET (misal akses langsung)
         $redirect_location = "../input_student_data.php?student_id=".$_GET['student_id'];
    }
    header("Location: " . $redirect_location);
    exit;
}
?>