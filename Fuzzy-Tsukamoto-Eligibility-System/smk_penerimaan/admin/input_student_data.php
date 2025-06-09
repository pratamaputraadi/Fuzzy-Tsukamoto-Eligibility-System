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
$existing_scores = [];
$existing_physical_data = ['height_cm' => '', 'weight_kg' => ''];

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

        $stmt_scores = $conn->prepare("SELECT subject_name, score_value FROM Student_Scores WHERE student_id = :student_id");
        $stmt_scores->bindParam(':student_id', $student_id);
        $stmt_scores->execute();
        $scores_data = $stmt_scores->fetchAll(PDO::FETCH_ASSOC);
        foreach ($scores_data as $score) {
            $existing_scores[$score['subject_name']] = $score['score_value'];
        }

        $stmt_physical = $conn->prepare("SELECT height_cm, weight_kg FROM Student_Physical_Data WHERE student_id = :student_id");
        $stmt_physical->bindParam(':student_id', $student_id);
        $stmt_physical->execute();
        if ($stmt_physical->rowCount() > 0) {
            $existing_physical_data = $stmt_physical->fetch(PDO::FETCH_ASSOC);
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

$subjects = [
    "Matematika", "Bahasa Indonesia", "Bahasa Inggris", "Fisika", "Kimia", "Biologi",
    "PPKn", "IPS", "Sosiologi", "Prakarya", "Seni Budaya", "Informatika"
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai & Data Fisik untuk <?php echo htmlspecialchars($student_name); ?> - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f7; margin: 0; padding: 0; color: #333; }
        .page-header { background-color: #007bff; color: white; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .page-header .header-title { font-size: 22px; font-weight: 600; }
        .page-header a { color: white; text-decoration: none; padding: 8px 15px; background-color: #0056b3; border-radius: 5px; }
        .page-header a:hover { background-color: #004494; }

        .form-container { background-color: #ffffff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 100%; max-width: 700px; margin: 30px auto; }
        .form-container h2, .form-container h3 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-container h3 { font-size: 20px; color: #007bff; margin-top: 30px; }
        .form-container label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; }
        .form-container input[type="text"], .form-container input[type="number"] { width: 100%; padding: 10px 12px; margin-bottom: 15px; border: 1px solid #ccd1d9; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        .form-container input[type="text"]:focus, .form-container input[type="number"]:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); outline: none; }
        .form-container .score-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .form-container .physical-data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-container button { width: 100%; padding: 12px 15px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; margin-top: 20px; }
        .form-container button:hover { background-color: #218838; }
        .message { padding: 10px 15px; margin-bottom: 20px; border-radius: 6px; text-align: center; }
        .message.success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .message.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-title">Input Data Siswa</div>
        <a href="manage_students.php">Kembali ke Daftar Siswa</a>
    </div>

    <div class="form-container">
        <h2>Input Nilai & Data Fisik</h2>
        <p style="text-align:center; font-size: 18px; margin-bottom: 20px;">Siswa: <strong><?php echo htmlspecialchars($student_name); ?></strong></p>

        <?php
        if (isset($_SESSION['form_message'])) {
            $message_type = isset($_SESSION['form_message_type']) && $_SESSION['form_message_type'] == 'error' ? 'error' : 'success';
            echo '<p class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['form_message']) . '</p>';
            unset($_SESSION['form_message']);
            unset($_SESSION['form_message_type']);
        }
        ?>

        <form action="actions/save_student_scores_physical.php" method="POST">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">

            <h3>Nilai Mata Pelajaran</h3>
            <div class="score-grid">
                <?php foreach ($subjects as $subject): ?>
                <div>
                    <label for="score_<?php echo str_replace(' ', '_', $subject); ?>"><?php echo htmlspecialchars($subject); ?>:</label>
                    <input type="number" step="0.01" min="0" max="100" 
                           id="score_<?php echo str_replace(' ', '_', $subject); ?>" 
                           name="scores[<?php echo htmlspecialchars($subject); ?>]" 
                           value="<?php echo isset($existing_scores[$subject]) ? htmlspecialchars($existing_scores[$subject]) : ''; ?>"
                           required>
                </div>
                <?php endforeach; ?>
            </div>

            <h3>Data Fisik</h3>
            <div class="physical-data-grid">
                <div>
                    <label for="height_cm">Tinggi Badan (cm):</label>
                    <input type="number" min="0" id="height_cm" name="height_cm" 
                           value="<?php echo htmlspecialchars($existing_physical_data['height_cm']); ?>" required>
                </div>
                <div>
                    <label for="weight_kg">Berat Badan (kg):</label>
                    <input type="number" min="0" id="weight_kg" name="weight_kg"
                           value="<?php echo htmlspecialchars($existing_physical_data['weight_kg']); ?>" required>
                </div>
            </div>

            <button type="submit">Simpan Data Nilai & Fisik</button>
        </form>
    </div>
</body>
</html>