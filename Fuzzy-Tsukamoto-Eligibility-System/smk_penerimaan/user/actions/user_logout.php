<?php
session_start();

// Hapus semua variabel session yang berkaitan dengan login user
unset($_SESSION['user_logged_in_student_id']);
unset($_SESSION['pending_verification_student_id']); // Hapus juga jika ada
// Anda bisa juga menghapus semua session dengan $_SESSION = array(); jika tidak ada session lain yang perlu dijaga

// Atau hancurkan session sepenuhnya jika itu yang diinginkan
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }
// session_destroy(); // Gunakan ini jika ingin menghancurkan SEMUA session

// Redirect ke halaman login siswa tahap 1
header("Location: ../login_tahap1.php");
exit;
?>