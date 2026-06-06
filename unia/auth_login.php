<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=Preencha todos os campos");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role']; // admin, coordinator, instructor, student

            header("Location: dashboard.php");
            exit;
        } else {
            // Invalid credentials
            header("Location: login.php?error=Credenciais inválidas");
            exit;
        }
    } catch (PDOException $e) {
        // Log error
        header("Location: login.php?error=Erro no sistema");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>