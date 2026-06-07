<?php
try {
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    echo "Connected as root with empty password";
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=localhost", 'root', 'root');
        echo "Connected as root with 'root' password";
    } catch (PDOException $e2) {
        echo "Failed to connect as root: " . $e2->getMessage();
    }
}
?>