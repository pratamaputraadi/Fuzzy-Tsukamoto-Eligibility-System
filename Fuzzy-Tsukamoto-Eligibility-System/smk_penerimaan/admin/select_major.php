<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: login.php");
    exit;
}

$student_id = null;
$student_name = "Siswa Tidak Ditemukan";
$data_lengkap = true;

if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    try {
        $stmt_student = $conn->prepare("SELECT full_name FROM Students WHERE student_id = :student_id");
        $stmt_student->bindParam(':student_id', $student_id);
        $stmt_student->execute();
        if ($stmt_student->rowCount() > 0) {
            $student = $stmt_student->fetch(PDO::FETCH_ASSOC);
            $student_name = $student['full_name'];
        } else {
            $_SESSION['form_message'] = "ID Siswa tidak valid atau tidak ditemukan.";
            $_SESSION['form_message_type'] = "error";
            header("Location: manage_students.php");
            exit;
        }

        $stmt_scores_check = $conn->prepare("SELECT COUNT(DISTINCT subject_name) as count_scores FROM Student_Scores WHERE student_id = :student_id");
        $stmt_scores_check->bindParam(':student_id', $student_id);
        $stmt_scores_check->execute();
        $scores_count = $stmt_scores_check->fetch(PDO::FETCH_ASSOC)['count_scores'];
        if ($scores_count < 12) {
            $data_lengkap = false;
            $_SESSION['form_message'] = "Data nilai siswa belum lengkap (kurang dari 12 mata pelajaran). Harap lengkapi dulu.";
        }

        $stmt_physical_check = $conn->prepare("SELECT COUNT(*) as count_physical FROM Student_Physical_Data WHERE student_id = :student_id AND height_cm IS NOT NULL AND weight_kg IS NOT NULL");
        $stmt_physical_check->bindParam(':student_id', $student_id);
        $stmt_physical_check->execute();
        $physical_count = $stmt_physical_check->fetch(PDO::FETCH_ASSOC)['count_physical'];
        if ($physical_count < 1) {
            $data_lengkap = false;
            $_SESSION['form_message'] = isset($_SESSION['form_message']) ? $_SESSION['form_message'] . " Data fisik juga belum lengkap." : "Data fisik siswa belum lengkap. Harap lengkapi dulu.";
        }

        if (!$data_lengkap) {
            $_SESSION['form_message_type'] = "error";
            header("Location: input_student_data.php?student_id=" . $student_id);
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['form_message'] = "Error mengambil data siswa: " . $e->getMessage();
        $_SESSION['form_message_type'] = "error";
        header("Location: manage_students.php");
        exit;
    }
} else {
    $_SESSION['form_message'] = "ID Siswa tidak disediakan.";
    $_SESSION['form_message_type'] = "error";
    header("Location: manage_students.php");
    exit;
}

$majors = [
    "TKJ" => "Teknik Komputer dan Jaringan",
    "RPL" => "Rekayasa Perangkat Lunak",
    "MM" => "Multimedia",
    "AKL" => "Akuntansi dan Keuangan Lembaga",
    "OTKP" => "Otomatisasi dan Tata Kelola Perkantoran",
    "BDP" => "Bisnis Daring dan Pemasaran",
    "TKRO" => "Teknik Kendaraan Ringan Otomotif",
    "TBSM" => "Teknik dan Bisnis Sepeda Motor",
    "PH" => "Perhotelan",
    "TBG" => "Tata Boga",
    "TBS" => "Tata Busana",
    "FARM" => "Farmasi"
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Jurusan & Proses untuk <?php echo htmlspecialchars($student_name); ?> - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f7; margin: 0; padding: 0; color: #333; }
        .page-header { background-color: #007bff; color: white; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .page-header .header-title { font-size: 22px; font-weight: 600; }
        .page-header a { color: white; text-decoration: none; padding: 8px 15px; background-color: #0056b3; border-radius: 5px; }
        .page-header a:hover { background-color: #004494; }

        .form-container { background-color: #ffffff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 100%; max-width: 600px; margin: 30px auto; }
        .form-container h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-container p { text-align: center; font-size: 18px; margin-bottom: 25px; }
        .form-container label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; }
        .form-container select { width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid #ccd1d9; border-radius: 6px; box-sizing: border-box; font-size: 16px; background-color: white; }
        .form-container select:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); outline: none; }
        .form-container button { width: 100%; padding: 12px 15px; background-color: #17a2b8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; margin-top: 10px; }
        .form-container button:hover { background-color: #138496; }
        .message { padding: 10px 15px; margin-bottom: 20px; border-radius: 6px; text-align: center; }
        .message.success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .message.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-title">Pemilihan Jurusan Siswa</div>
        <a href="manage_students.php">Kembali ke Daftar Siswa</a>
    </div>

    <div class="form-container">
        <h2>Proses Pendaftaran Akhir</h2>
        <p>Siswa: <strong><?php echo htmlspecialchars($student_name); ?></strong></p>

        <?php
        if (isset($_SESSION['form_message'])) {
            $message_type = isset($_SESSION['form_message_type']) && $_SESSION['form_message_type'] == 'error' ? 'error' : 'success';
            echo '<p class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['form_message']) . '</p>';
            unset($_SESSION['form_message']);
            unset($_SESSION['form_message_type']);
        }
        ?>

        <form action="actions/finalize_registration.php" method="POST">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <div>
                <label for="chosen_major_code">Pilih Jurusan yang Diinginkan Siswa:</label>
                <select id="chosen_major_code" name="chosen_major_code" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($majors as $code => $name): ?>
                        <option value="<?php echo htmlspecialchars($code); ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Daftarkan & Proses Kelayakan</button>
        </form>
    </div>
</body>
</html>