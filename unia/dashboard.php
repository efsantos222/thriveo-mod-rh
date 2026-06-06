<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        header("Location: admin/index.php");
        break;
    case 'coordinator':
        header("Location: coordinator/index.php");
        break;
    case 'instructor':
        header("Location: instructor/index.php");
        break;
    case 'student':
        header("Location: student/index.php");
        break;
    default:
        // Unknown role
        session_destroy();
        header("Location: login.php?error=Erro de permissão");
        break;
}
exit;
?>