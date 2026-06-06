<?php
require_once '../config.php';
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Delete users first then company
    $pdo->prepare("DELETE FROM users WHERE company_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([$id]);
}

header("Location: companies.php");
exit;
?>