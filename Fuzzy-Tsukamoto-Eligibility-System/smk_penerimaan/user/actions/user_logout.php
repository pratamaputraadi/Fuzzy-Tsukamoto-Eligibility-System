<?php
session_start();

unset($_SESSION['user_logged_in_student_id']);
unset($_SESSION['pending_verification_student_id']);

header("Location: ../login_tahap1.php");
exit;
?>