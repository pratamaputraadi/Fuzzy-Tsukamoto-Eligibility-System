<?php
session_start();
require_once '../includes/db_connection.php'; // Sesuaikan path relatif

// Jika siswa belum login sepenuhnya, arahkan ke tahap 1
if (!isset($_SESSION['user_logged_in_student_id'])) {
    $_SESSION['login_error_user'] = "Anda harus login untuk melihat hasil penerimaan."; // Pesan untuk login tahap 1
    header("Location: login_tahap1.php");
    exit;
}

$student_id = $_SESSION['user_logged_in_student_id'];
$student_data = null;
$application_data = null;
$page_title = "Hasil Penerimaan Siswa"; // Judul default
$background_color = "#eef2f7"; // Warna latar default
$message_class = ""; // Kelas CSS untuk pesan
$display_message = "";
$chosen_major_name_display = "Tidak Diketahui";
$recommended_major_name_display = "Tidak Ada";

// Daftar nama jurusan lengkap (sinkronkan dengan yang ada di admin/actions/finalize_registration.php dan skrip Python)
$major_full_names = [
    "TKJ" => "Teknik Komputer dan Jaringan", "RPL" => "Rekayasa Perangkat Lunak", "MM" => "Multimedia",
    "AKL" => "Akuntansi dan Keuangan Lembaga", "OTKP" => "Otomatisasi dan Tata Kelola Perkantoran",
    "BDP" => "Bisnis Daring dan Pemasaran", "TKRO" => "Teknik Kendaraan Ringan Otomotif",
    "TBSM" => "Teknik dan Bisnis Sepeda Motor", "PH" => "Perhotelan", "TBG" => "Tata Boga",
    "TBS" => "Tata Busana", "FARM" => "Farmasi"
];

try {
    // Ambil data siswa
    $stmt_student = $conn->prepare("SELECT full_name, birth_place, birth_date, origin_school FROM Students WHERE student_id = :student_id");
    $stmt_student->bindParam(':student_id', $student_id);
    $stmt_student->execute();
    $student_data = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if (!$student_data) {
        throw new Exception("Data profil siswa tidak ditemukan.");
    }

    // Ambil data aplikasi/pendaftaran siswa
    $stmt_application = $conn->prepare("SELECT status, chosen_major_code, recommended_major_code FROM Applications WHERE student_id = :student_id");
    $stmt_application->bindParam(':student_id', $student_id);
    $stmt_application->execute();
    $application_data = $stmt_application->fetch(PDO::FETCH_ASSOC);

    if (!$application_data) {
        $display_message = "Status pendaftaran Anda belum diproses atau belum tersedia. Silakan hubungi pihak sekolah.";
        $message_class = "info";
        $background_color = "#d1ecf1";
    } else {
        $chosen_major_name_display = isset($major_full_names[$application_data['chosen_major_code']]) ? $major_full_names[$application_data['chosen_major_code']] : $application_data['chosen_major_code'];

        if ($application_data['status'] === 'Diterima') {
            $page_title = "Selamat! Anda Diterima";
            $background_color = "#d4edda"; // Hijau untuk 'diterima'
            $message_class = "success";
            $display_message = "Selamat! Anda telah diterima di jurusan <strong>" . htmlspecialchars($chosen_major_name_display) . "</strong>.";
        } elseif ($application_data['status'] === 'Tidak Diterima - Rekomendasi') {
            $page_title = "Informasi Penerimaan";
            $background_color = "#fff3cd"; // Kuning
            $message_class = "warning";
            if (isset($application_data['recommended_major_code']) && isset($major_full_names[$application_data['recommended_major_code']])) {
                $recommended_major_name_display = $major_full_names[$application_data['recommended_major_code']];
            } else {
                $recommended_major_name_display = $application_data['recommended_major_code'] ?? "Tidak Ada Rekomendasi Spesifik";
            }
            $display_message = "Mohon maaf, Anda tidak diterima di jurusan " . htmlspecialchars($chosen_major_name_display) . ". Tetapi berdasarkan perhitungan nilai Anda, kami merekomendasikan Anda untuk mendaftar di jurusan <strong>" . htmlspecialchars($recommended_major_name_display) . "</strong>.";
        } elseif ($application_data['status'] === 'Tidak Diterima - Tanpa Rekomendasi') {
            $page_title = "Informasi Penerimaan";
            $background_color = "#f8d7da"; // Merah
            $message_class = "danger";
            $display_message = "Mohon maaf, Anda tidak diterima di jurusan " . htmlspecialchars($chosen_major_name_display) . ". Nilai Anda belum mencukupi untuk direkomendasikan ke jurusan lain. Tetap semangat dan jangan menyerah!";
        } else {
            $display_message = "Status pendaftaran Anda sedang dalam proses atau belum tersedia.";
            $message_class = "info";
            $background_color = "#d1ecf1"; 
        }
    }

} catch (Exception $e) {
    // error_log("Error fetching student result: " . $e->getMessage());
    $display_message = "Terjadi kesalahan saat mengambil data hasil penerimaan Anda. Silakan coba lagi nanti atau hubungi administrator.";
    $message_class = "danger";
    $background_color = "#f8d7da";
}

// Format tanggal lahir untuk tampilan (TANPA IntlDateFormatter)
$birth_date_display = "Tidak diketahui";
if ($student_data && isset($student_data['birth_date'])) {
    try {
        $date_obj = new DateTime($student_data['birth_date']); // Membuat objek DateTime

        // Daftar bulan untuk format manual
        $bulan_map_display = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $day_display = $date_obj->format('d'); // Ambil hari (dd)
        $month_num_display = $date_obj->format('m'); // Ambil bulan (mm)
        $year_display = $date_obj->format('Y'); // Ambil tahun (YYYY)
        
        // Ambil nama bulan dari map, atau gunakan nomor bulan jika tidak ada
        $month_name_display = isset($bulan_map_display[$month_num_display]) ? $bulan_map_display[$month_num_display] : $month_num_display;
        
        $birth_date_display = $day_display . ' ' . $month_name_display . ' ' . $year_display; // Format: DD NamaBulan YYYY

    } catch (Exception $ex) {
        // Jika pembuatan objek DateTime gagal, fallback ke data mentah dari DB
        $birth_date_display = $student_data['birth_date']; 
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | PPDB SMK Adipati Singaperbangsa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: <?php echo $background_color; ?>; /* Warna latar dinamis */
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            color: #333;
        }
        .result-container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
            text-align: center;
            border-top: 5px solid; /* Border atas dinamis */
        }
        .result-container.success { border-top-color: #28a745; /* Hijau untuk sukses (diterima) */ }
        .result-container.warning { border-top-color: #ffc107; /* Kuning untuk rekomendasi */ }
        .result-container.danger { border-top-color: #dc3545; /* Merah untuk tidak diterima */ }
        .result-container.info { border-top-color: #17a2b8; /* Biru muda untuk info/belum proses */ }

        .result-container h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .result-container.success h1 { color: #28a745; }
        .result-container.warning h1 { color: #b8860b; } /* Kuning tua */
        .result-container.danger h1 { color: #dc3545; }
        .result-container.info h1 { color: #17a2b8; }


        .student-info {
            margin-top: 25px;
            margin-bottom: 30px;
            text-align: left;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .student-info p {
            font-size: 17px;
            line-height: 1.7;
            margin: 8px 0;
            color: #444;
        }
        .student-info p strong {
            display: inline-block;
            min-width: 150px; /* Lebar label agar rata */
            font-weight: 600;
            color: #333;
        }

        .message-display {
            font-size: 18px;
            line-height: 1.6;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            color: #fff; /* Teks putih agar kontras */
        }
        .message-display.success { background-color: #28a745; }
        .message-display.warning { background-color: #ffc107; color: #333; /* Teks gelap untuk kuning */ }
        .message-display.danger { background-color: #dc3545; }
        .message-display.info { background-color: #17a2b8; }


        .logout-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 25px;
            background-color: #6c757d; /* Abu-abu untuk logout */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .logout-link:hover {
            background-color: #5a6268;
        }
        .school-logo { /* Jika Anda ingin menambahkan logo */
            max-width: 100px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="result-container <?php echo htmlspecialchars($message_class); ?>">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        
        <?php if ($student_data): ?>
        <div class="student-info">
            <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($student_data['full_name']); ?></p>
            <p><strong>Tempat, Tgl Lahir:</strong> <?php echo htmlspecialchars($student_data['birth_place'] . ', ' . $birth_date_display); ?></p>
            <p><strong>Asal SMP:</strong> <?php echo htmlspecialchars($student_data['origin_school']); ?></p>
            <?php if ($application_data && isset($application_data['chosen_major_code'])): // Tampilkan jurusan pilihan hanya jika aplikasi sudah diproses ?>
            <p><strong>Jurusan Pilihan:</strong> <?php echo htmlspecialchars($chosen_major_name_display); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($display_message)): ?>
            <div class="message-display <?php echo htmlspecialchars($message_class); ?>">
                <?php echo $display_message; // Pesan sudah mengandung HTML (<strong>), jadi tidak di-escape di sini ?>
            </div>
        <?php endif; ?>

        <a href="actions/user_logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>