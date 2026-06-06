<?php
require_once 'config/db.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM portfolio_services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: index.php");
exit();
?>