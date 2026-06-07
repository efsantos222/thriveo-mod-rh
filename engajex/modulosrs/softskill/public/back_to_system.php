<?php
// modulosrs/softskill/public/back_to_system.php
session_start();

// Restore Engaja session if backup exists
if (isset($_SESSION['engaja_backup'])) {
    foreach ($_SESSION['engaja_backup'] as $key => $value) {
        $_SESSION[$key] = $value;
    }
    unset($_SESSION['engaja_backup']);
}

header("Location: ../../../dashboard.php");
exit;
?>
