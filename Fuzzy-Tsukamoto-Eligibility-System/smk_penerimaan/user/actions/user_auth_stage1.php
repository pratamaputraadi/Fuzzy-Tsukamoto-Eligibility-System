<?php
session_start();
require_once '../../includes/db_connection.php';

function parse_birth_details($birth_details_str) {
    $bulan_map = [
        'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
        'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
        'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12'
    ];

    $parts = explode(',', $birth_details_str, 2);
    if (count($parts) !== 2) {
        return null;
    }

    $birth_place = trim($parts[0]);
    $date_str = trim($parts[1]);

    $date_parts = explode(' ', $date_str);
    if (count($date_parts) !== 3) {
        return null;
    }

    $day = trim($date_parts[0]);
    $month_name = strtolower(trim($date_parts[1]));
    $year = trim($date_parts[2]);

    if (!ctype_digit($day) || !ctype_digit($year) || !isset($bulan_map[$month_name])) {
        return null;
    }

    $month_num = $bulan_map[$month_name];

    $day_padded = str_pad($day, 2, '0', STR_PAD_LEFT);
    $birth_date_db_format = "$year-$month_num-$day_padded";

    $d = DateTime::createFromFormat('Y-m-d', $birth_date_db_format);
    if (!$d || $d->format('Y-m-d') !== $birth_date_db_format) {
        return null;
    }

    return [
        'birth_place' => $birth_place,
        'birth_date_db' => $birth_date_db_format
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $birth_details_input = trim($_POST['birth_details']);

    if (empty($full_name) || empty($birth_details_input)) {
        $_SESSION['login_error_user'] = "Nama lengkap dan tempat tanggal lahir wajib diisi.";
        header("Location: ../login_tahap1.php");
        exit;
    }

    $parsed_data = parse_birth_details($birth_details_input);

    if ($parsed_data === null) {
        $_SESSION['login_error_user'] = "Format Tempat Tanggal Lahir tidak sesuai. Contoh: Bandung, 15 Agustus 2007";
        header("Location: ../login_tahap1.php");
        exit;
    }

    $birth_place_from_input = $parsed_data['birth_place'];
    $birth_date_from_input_db_format = $parsed_data['birth_date_db'];

    try {
        $stmt = $conn->prepare("SELECT student_id, full_name, birth_place, birth_date FROM Students WHERE full_name = :full_name AND birth_place = :birth_place AND birth_date = :birth_date");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':birth_place', $birth_place_from_input);
        $stmt->bindParam(':birth_date', $birth_date_from_input_db_format);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['pending_verification_student_id'] = $student['student_id'];
            unset($_SESSION['login_error_user']); 
            header("Location: ../login_tahap2.php");
            exit;
        } else {
            $_SESSION['login_error_user'] = "Data siswa tidak ditemukan. Pastikan Nama Lengkap, Tempat Lahir, dan Tanggal Lahir sesuai dengan yang didaftarkan oleh Admin.";
            header("Location: ../login_tahap1.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['login_error_user'] = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
        header("Location: ../login_tahap1.php");
        exit;
    }
} else {
    header("Location: ../login_tahap1.php");
    exit;
}
?>