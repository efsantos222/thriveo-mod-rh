<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE companies");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . " ";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>