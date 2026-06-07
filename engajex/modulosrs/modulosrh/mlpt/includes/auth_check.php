<?php
session_start();

function checkAuth($requiredRole = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    if ($requiredRole && $_SESSION['user_role'] !== $requiredRole) {
        // Redirect based on their actua role or unauthorized page
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../app/index.php");
        }
        exit;
    }
}
?>