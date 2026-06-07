<?php
// modulosrh/entrev/back_to_system.php
session_start();

// Restore Engaja role if backup exists
if (isset($_SESSION['role_backup'])) {
    $_SESSION['role'] = $_SESSION['role_backup'];
    unset($_SESSION['role_backup']);
}

header("Location: ../../dashboard.php");
exit;
?>
