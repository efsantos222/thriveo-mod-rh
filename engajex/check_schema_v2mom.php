<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE v2mom_vision");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($cols);
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>