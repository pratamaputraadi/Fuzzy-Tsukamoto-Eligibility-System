<?php
session_start();
require_once '../includes/db_connection.php'; // Sesuaikan path ke file koneksi database Anda

// Cek jika admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: login.php");
    exit;
}

// Ambil semua data siswa dari database untuk ditampilkan
$students = [];
$fetch_error = null;
try {
    // Urutkan berdasarkan nama siswa secara ascending (A-Z)
    $stmt = $conn->query("SELECT student_id, full_name, birth_place, birth_date, origin_school FROM Students ORDER BY full_name ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Sebaiknya error ini di-log, bukan ditampilkan langsung ke user di production
    // error_log("Error fetching students data: " . $e->getMessage());
    $fetch_error = "Gagal mengambil data siswa dari database. Silakan coba lagi atau hubungi administrator. Detail: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa - Dashboard Admin</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #eef2f7; 
            margin: 0; 
            padding: 0; 
            color: #333; 
        }
        .page-header { 
            background-color: #007bff; 
            color: white; 
            padding: 15px 30px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; /* Tambah margin bawah */
        }
        .page-header .header-title { 
            font-size: 22px; 
            font-weight: 600; 
        }
        .page-header a.nav-link {
            color: white; 
            text-decoration: none; 
            padding: 8px 15px; 
            background-color: #0056b3; 
            border-radius: 5px; 
            transition: background-color 0.3s ease;
        }
        .page-header a.nav-link:hover { 
            background-color: #004494; 
        }

        .content-container { 
            padding: 20px 30px; /* Disesuaikan paddingnya */
            max-width: 1200px; 
            margin: 0 auto 30px auto; /* Margin bawah ditambahkan */
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .content-container h2 { 
            text-align: center; 
            margin-top: 0; /* Hapus margin atas jika header sudah ada */
            margin-bottom: 25px; 
            color: #333; 
            font-size: 24px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            font-size: 15px;
        }
        table th, table td { 
            border: 1px solid #ddd; 
            padding: 10px 12px; 
            text-align: left; 
            vertical-align: middle;
        }
        table th { 
            background-color: #f0f2f5; /* Warna header tabel lebih lembut */
            font-weight: 600; 
            color: #495057; 
        }
        table tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        table tr:hover { 
            background-color: #e9ecef; 
        }
        .actions-column { /* Kolom tindakan agar tombol tidak terlalu lebar */
            width: 320px; /* Sesuaikan lebar ini jika perlu */
            text-align: center; /* Tengahkan tombol jika mau */
        }
        .action-button {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px; /* Ukuran font tombol sedikit dikecilkan */
            margin-right: 5px;
            color: white !important; 
            display: inline-block; 
            margin-bottom: 5px; 
            text-align: center;
            min-width: 90px; /* Lebar minimum agar tombol terlihat seragam */
            border: none; /* Hapus border default dari <a> */
            cursor: pointer; /* Ubah cursor jadi pointer */
            transition: background-color 0.2s ease;
        }
        .btn-input-data { background-color: #17a2b8; /* Teal */ }
        .btn-input-data:hover { background-color: #138496; }
        
        .btn-select-major { background-color: #28a745; /* Hijau */ }
        .btn-select-major:hover { background-color: #218838; }

        .btn-delete { background-color: #dc3545; /* Merah */ }
        .btn-delete:hover { background-color: #c82333; }

        .no-data { 
            text-align: center; 
            color: #777; 
            padding: 30px 20px; /* Padding lebih besar */
            font-size: 18px; 
            background-color: #f9f9f9;
            border-radius: 6px;
        }
        .message { 
            padding: 12px 18px; /* Padding pesan lebih besar */
            margin-bottom: 20px; 
            border-radius: 6px; 
            text-align: center; 
            font-size: 15px;
            border: 1px solid transparent;
        }
        .message.success { 
            color: #0f5132; 
            background-color: #d1e7dd; 
            border-color: #badbcc; 
        }
        .message.error { 
            color: #842029; 
            background-color: #f8d7da; 
            border-color: #f5c2c7; 
        }
        .add-student-link-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .add-student-link {
            padding: 10px 18px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 15px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .add-student-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-title">Kelola Data Siswa</div>
        <a href="dashboard.php" class="nav-link">Kembali ke Dashboard</a>
    </div>

    <div class="content-container">
        <h2>Daftar Siswa Terdaftar</h2>
        
        <div class="add-student-link-container">
            <a href="register_student.php" class="add-student-link">+ Daftarkan Siswa Baru</a>
        </div>

        <?php 
        // Menampilkan pesan dari session (misalnya setelah redirect dari proses hapus/tambah)
        if (isset($_SESSION['form_message'])) {
            $message_type = isset($_SESSION['form_message_type']) && $_SESSION['form_message_type'] == 'error' ? 'error' : 'success';
            echo '<p class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['form_message']) . '</p>';
            unset($_SESSION['form_message']); 
            if(isset($_SESSION['form_message_type'])) unset($_SESSION['form_message_type']);
        }
        // Menampilkan error jika gagal mengambil data siswa
        if (isset($fetch_error)): 
        ?>
            <p class="message error"><?php echo htmlspecialchars($fetch_error); ?></p>
        <?php endif; ?>

        <?php if (!empty($students)): ?>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Lengkap</th>
                    <th>Tempat Lahir</th>
                    <th>Tanggal Lahir</th>
                    <th>Asal SMP</th>
                    <th class="actions-column">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $student): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['birth_place']); ?></td>
                    <td><?php echo htmlspecialchars(date('d F Y', strtotime($student['birth_date']))); ?></td>
                    <td><?php echo htmlspecialchars($student['origin_school']); ?></td>
                    <td class="actions-column">
                        <a href="input_student_data.php?student_id=<?php echo $student['student_id']; ?>" class="action-button btn-input-data" title="Input atau Edit Nilai & Data Fisik">Input/Edit Data</a>
                        <a href="select_major.php?student_id=<?php echo $student['student_id']; ?>" class="action-button btn-select-major" title="Pilih Jurusan dan Proses Kelayakan">Pilih Jurusan</a>
                        <a href="actions/delete_student.php?student_id=<?php echo $student['student_id']; ?>" 
                           class="action-button btn-delete" 
                           title="Hapus Siswa" 
                           onclick="return confirm('Apakah Anda YAKIN ingin menghapus data siswa bernama \'<?php echo htmlspecialchars(addslashes($student['full_name'])); ?>\'?\n\nSemua data terkait (nilai, fisik, aplikasi) juga akan terhapus.\nTindakan ini TIDAK DAPAT DIURUNGKAN.');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif (empty($fetch_error)): // Tampilkan "Belum ada data" hanya jika tidak ada error fetch ?>
            <p class="no-data">Belum ada data siswa yang terdaftar. Silakan daftarkan siswa baru terlebih dahulu.</p>
        <?php endif; ?>
    </div>
</body>
</html>