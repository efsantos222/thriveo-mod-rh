<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: users.php");
exit;
?>