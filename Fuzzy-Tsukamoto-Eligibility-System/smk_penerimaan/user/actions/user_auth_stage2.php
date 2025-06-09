<?php
session_start();
require_once '../../includes/db_connection.php'; // Sesuaikan path relatif

// Jika belum ada student_id yang menunggu verifikasi, atau sudah login penuh, arahkan
if (!isset($_SESSION['pending_verification_student_id'])) {
    header("Location: ../login_tahap1.php");
    exit;
}
if (isset($_SESSION['user_logged_in_student_id'])) {
    header("Location: ../hasil_penerimaan.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_code = trim($_POST['verification_code']);
    $student_id_pending = $_SESSION['pending_verification_student_id'];

    if (empty($submitted_code) || !ctype_digit($submitted_code) || strlen($submitted_code) !== 6) {
        $_SESSION['login_error_user_tahap2'] = "Format Kode Verifikasi salah. Masukkan 6 digit angka DDMMYY.";
        header("Location: ../login_tahap2.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT birth_date FROM Students WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id_pending);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $birth_date_db = $student['birth_date']; // Format YYYY-MM-DD

            // Ubah format birth_date dari DB (YYYY-MM-DD) menjadi DDMMYY
            $date_obj = DateTime::createFromFormat('Y-m-d', $birth_date_db);
            if ($date_obj) {
                $expected_code = $date_obj->format('dmy'); // Format DDMMYY

                if ($submitted_code === $expected_code) {
                    // Kode verifikasi cocok
                    $_SESSION['user_logged_in_student_id'] = $student_id_pending;
                    unset($_SESSION['pending_verification_student_id']); // Hapus session pending
                    unset($_SESSION['login_error_user_tahap2']); // Hapus error lama jika ada

                    header("Location: ../hasil_penerimaan.php");
                    exit;
                } else {
                    $_SESSION['login_error_user_tahap2'] = "Kode Verifikasi salah. Pastikan Anda memasukkan dengan benar.";
                    header("Location: ../login_tahap2.php");
                    exit;
                }
            } else {
                // Seharusnya tidak terjadi jika data di DB valid
                // error_log("Gagal memformat tanggal lahir dari DB untuk student_id: " . $student_id_pending);
                $_SESSION['login_error_user_tahap2'] = "Terjadi kesalahan internal saat memvalidasi tanggal lahir.";
                header("Location: ../login_tahap2.php");
                exit;
            }
        } else {
            // Seharusnya tidak terjadi jika student_id_pending valid dari tahap 1
            $_SESSION['login_error_user_tahap2'] = "Data siswa tidak ditemukan untuk verifikasi.";
            unset($_SESSION['pending_verification_student_id']); // Hapus session pending karena tidak valid
            header("Location: ../login_tahap1.php"); // Arahkan kembali ke tahap 1
            exit;
        }

    } catch (PDOException $e) {
        // error_log("User Login Stage 2 Error: " . $e->getMessage());
        $_SESSION['login_error_user_tahap2'] = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
        header("Location: ../login_tahap2.php");
        exit;
    }
} else {
    // Jika bukan metode POST, redirect
    header("Location: ../login_tahap2.php");
    exit;
}
?>