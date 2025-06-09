<?php
session_start();
require_once '../../includes/db_connection.php';

$major_full_names = [
    "TKJ" => "Teknik Komputer dan Jaringan", "RPL" => "Rekayasa Perangkat Lunak", "MM" => "Multimedia",
    "AKL" => "Akuntansi dan Keuangan Lembaga", "OTKP" => "Otomatisasi dan Tata Kelola Perkantoran",
    "BDP" => "Bisnis Daring dan Pemasaran", "TKRO" => "Teknik Kendaraan Ringan Otomotif",
    "TBSM" => "Teknik dan Bisnis Sepeda Motor", "PH" => "Perhotelan", "TBG" => "Tata Boga",
    "TBS" => "Tata Busana", "FARM" => "Farmasi"
];

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['form_message'] = "Akses ditolak. Silakan login sebagai admin.";
    $_SESSION['form_message_type'] = "error";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id']) && isset($_POST['chosen_major_code'])) {
    $student_id = $_POST['student_id'];
    $chosen_major_code = trim($_POST['chosen_major_code']);

    if (empty($chosen_major_code)) {
        $_SESSION['form_message'] = "Jurusan harus dipilih.";
        $_SESSION['form_message_type'] = "error";
        header("Location: ../select_major.php?student_id=" . $student_id);
        exit;
    }

    $student_scores_from_db = [];
    $student_height_from_db = null;
    $student_weight_from_db = null;

    try {
        if (!isset($conn) || !$conn) {
            throw new Exception("Koneksi database tidak tersedia.");
        }

        $stmt_scores = $conn->prepare("SELECT subject_name, score_value FROM Student_Scores WHERE student_id = :student_id");
        $stmt_scores->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_scores->execute();
        $scores_data = $stmt_scores->fetchAll(PDO::FETCH_ASSOC);

        if (count($scores_data) < 12) {
            throw new Exception("Data nilai siswa belum lengkap (kurang dari 12 mapel) untuk student_id=" . htmlspecialchars($student_id));
        }
        foreach ($scores_data as $score) {
            if (!is_numeric($score['score_value']) || $score['score_value'] < 0 || $score['score_value'] > 100) {
                throw new Exception("Nilai tidak valid untuk mapel: " . htmlspecialchars($score['subject_name']) . " (student_id=" . htmlspecialchars($student_id) . ")");
            }
            $student_scores_from_db[$score['subject_name']] = floatval($score['score_value']);
        }

        $stmt_physical = $conn->prepare("SELECT height_cm, weight_kg FROM Student_Physical_Data WHERE student_id = :student_id");
        $stmt_physical->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_physical->execute();
        $physical_data_row = $stmt_physical->fetch(PDO::FETCH_ASSOC);

        if (!$physical_data_row || !isset($physical_data_row['height_cm']) || !isset($physical_data_row['weight_kg']) ||
            !is_numeric($physical_data_row['height_cm']) || $physical_data_row['height_cm'] <= 0 ||
            !is_numeric($physical_data_row['weight_kg']) || $physical_data_row['weight_kg'] <= 0) {
            throw new Exception("Data fisik siswa belum lengkap/valid (student_id=" . htmlspecialchars($student_id) . ")");
        }
        $student_height_from_db = floatval($physical_data_row['height_cm']);
        $student_weight_from_db = floatval($physical_data_row['weight_kg']);

        $data_for_python = [
            "scores" => (object)$student_scores_from_db,
            "height" => $student_height_from_db,
            "weight" => $student_weight_from_db
        ];
        $input_json_for_python = json_encode($data_for_python, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("PHP json_encode error: " . json_last_error_msg());
        }
        
        $base64_encoded_payload = base64_encode($input_json_for_python);

        $conn->beginTransaction();

        $python_executable = 'python';
        $script_path = realpath(dirname(__FILE__) . '/../../python_fuzzy/fuzzy_logic_script.py');

        if (!$script_path || !file_exists($script_path)) {
            throw new Exception("Skrip Python tidak ditemukan: " . htmlspecialchars(dirname(__FILE__) . '/../../python_fuzzy/fuzzy_logic_script.py'));
        }
        
        $command = escapeshellcmd($python_executable) . ' ' . escapeshellarg($script_path) . ' ' . escapeshellarg($base64_encoded_payload);
        $python_output_json = shell_exec($command);

        if ($python_output_json === null || $python_output_json === false || trim($python_output_json) === '') {
            throw new Exception("Gagal eksekusi Python atau tidak ada output. Cek log server. Command: " . htmlspecialchars($command));
        }

        $fuzzy_results_all_majors = json_decode($python_output_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || (is_array($fuzzy_results_all_majors) && isset($fuzzy_results_all_majors['error']))) {
            $error_detail = (is_array($fuzzy_results_all_majors) && isset($fuzzy_results_all_majors['error'])) ? $fuzzy_results_all_majors['error'] : ("Parse JSON error: " . json_last_error_msg());
            throw new Exception("Error proses hasil fuzzy: " . htmlspecialchars($error_detail) . ". Output Python: <pre>" . htmlspecialchars($python_output_json) . "</pre>");
        }
        
        if (!is_array($fuzzy_results_all_majors)) {
            throw new Exception("Output Python bukan JSON array/objek. Output Python: <pre>" . htmlspecialchars($python_output_json) . "</pre>");
        }

        if (!isset($fuzzy_results_all_majors[$chosen_major_code])) {
            throw new Exception("Kode jurusan '" . htmlspecialchars($chosen_major_code) . "' tidak ditemukan di hasil fuzzy. Output Python: <pre>" . htmlspecialchars($python_output_json) . "</pre>");
        }
        $result_for_chosen_major = $fuzzy_results_all_majors[$chosen_major_code];
        if (!isset($result_for_chosen_major['skor']) || !isset($result_for_chosen_major['status'])) {
            throw new Exception("Struktur hasil fuzzy jurusan pilihan tidak lengkap.");
        }
        $chosen_major_score = floatval($result_for_chosen_major['skor']);
        $application_status_final = '';
        $recommended_major_code_final = null;
        $recommended_major_score_final = null;
        if ($result_for_chosen_major['status'] === "Diterima") {
            $application_status_final = 'Diterima';
        } else {
            $best_recommendation_score = -1.0;
            foreach ($fuzzy_results_all_majors as $major_code_loop => $result_loop) {
                if ($major_code_loop !== $chosen_major_code && isset($result_loop['status']) && $result_loop['status'] === "Diterima") {
                    if (isset($result_loop['skor']) && is_numeric($result_loop['skor'])) {
                        $current_score = floatval($result_loop['skor']);
                        if ($current_score > $best_recommendation_score) {
                            $best_recommendation_score = $current_score;
                            $recommended_major_code_final = $major_code_loop;
                            $recommended_major_score_final = $best_recommendation_score;
                        }
                    }
                }
            }
            if ($recommended_major_code_final !== null) {
                $application_status_final = 'Tidak Diterima - Rekomendasi';
            } else {
                $application_status_final = 'Tidak Diterima - Tanpa Rekomendasi';
            }
        }

        $stmt_delete_application = $conn->prepare("DELETE FROM Applications WHERE student_id = :student_id");
        $stmt_delete_application->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_delete_application->execute();
        $stmt_insert_application = $conn->prepare("
            INSERT INTO Applications (student_id, chosen_major_code, chosen_major_score, status, recommended_major_code, recommended_major_score, all_fuzzy_results_json, application_date) 
            VALUES (:student_id, :chosen_major_code, :chosen_major_score, :status, :recommended_major_code, :recommended_major_score, :all_fuzzy_results_json, NOW())");
        $stmt_insert_application->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_insert_application->bindParam(':chosen_major_code', $chosen_major_code, PDO::PARAM_STR);
        $stmt_insert_application->bindParam(':chosen_major_score', $chosen_major_score);
        $stmt_insert_application->bindParam(':status', $application_status_final, PDO::PARAM_STR);
        $stmt_insert_application->bindParam(':recommended_major_code', $recommended_major_code_final, PDO::PARAM_STR);
        $stmt_insert_application->bindParam(':recommended_major_score', $recommended_major_score_final);
        $stmt_insert_application->bindParam(':all_fuzzy_results_json', $python_output_json, PDO::PARAM_STR);
        $stmt_insert_application->execute();
        $conn->commit();
        
        $student_name_for_message = "Siswa ini";
        $stmt_s_name = $conn->prepare("SELECT full_name FROM Students WHERE student_id = :student_id");
        $stmt_s_name->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt_s_name->execute();
        if($s_name_row = $stmt_s_name->fetch()){
            $student_name_for_message = htmlspecialchars($s_name_row['full_name']);
        }
        $chosen_major_name = $major_full_names[$chosen_major_code] ?? $chosen_major_code;
        if ($application_status_final === 'Diterima') {
            $_SESSION['form_message'] = "Sukses! " . $student_name_for_message . " diterima di jurusan " . htmlspecialchars($chosen_major_name) . ".";
        } elseif ($application_status_final === 'Tidak Diterima - Rekomendasi') {
            $recommended_major_name = $major_full_names[$recommended_major_code_final] ?? $recommended_major_code_final;
            $_SESSION['form_message'] = $student_name_for_message . " tidak diterima di jurusan " . htmlspecialchars($chosen_major_name) . ", tetapi direkomendasikan ke jurusan " . htmlspecialchars($recommended_major_name) . ".";
        } else {
            $_SESSION['form_message'] = $student_name_for_message . " tidak diterima di jurusan " . htmlspecialchars($chosen_major_name) . " dan tidak ada rekomendasi jurusan lain.";
        }
        $_SESSION['form_message_type'] = "success";
        header("Location: ../manage_students.php");
        exit;

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        $student_name_for_error = '';
        if (isset($student_id) && is_numeric($student_id) && isset($conn)) {
            try {
                $stmt_err_sname = $conn->prepare("SELECT full_name FROM Students WHERE student_id = :student_id");
                $stmt_err_sname->bindParam(':student_id', $student_id, PDO::PARAM_INT);
                $stmt_err_sname->execute();
                if($row_sname = $stmt_err_sname->fetch()) {
                    $student_name_for_error = " (Siswa: " . htmlspecialchars($row_sname['full_name']) . ")";
                }
            } catch (PDOException $ex_sname) {}
        }
        $_SESSION['form_message'] = "Gagal memproses pendaftaran" . $student_name_for_error . ": " . $e->getMessage();
        $_SESSION['form_message_type'] = "error";
        $redirect_url = "../manage_students.php";
        if(isset($student_id) && is_numeric($student_id)){
            $redirect_url = "../select_major.php?student_id=" . $student_id;
        }
        header("Location: " . $redirect_url);
        exit;
    }
} else {
    $_SESSION['form_message'] = "Metode tidak diizinkan atau data POST tidak lengkap.";
    $_SESSION['form_message_type'] = "error";
    header("Location: ../manage_students.php");
    exit;
}
?>