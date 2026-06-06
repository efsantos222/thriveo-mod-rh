<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user_role'];

// Router based on role
if ($role === 'superadmin') {
    header('Location: admin/dashboard.php');
} elseif ($role === 'responsible') {
    header('Location: responsible/dashboard.php');
} else {
    echo "Role desconhecida.";
    exit;
}
exit;
?>