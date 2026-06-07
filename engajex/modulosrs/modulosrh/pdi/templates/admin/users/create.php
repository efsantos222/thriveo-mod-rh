<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ?route=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $manager_id = $_POST['manager_id'] ?: null;
    $user_type = $_POST['user_type'] ?? 'employee';

    // Definir is_admin e is_manager baseado no tipo
    $is_admin = ($user_type === 'admin');
    $is_manager = ($user_type === 'manager' || $user_type === 'admin');

    try {
        $stmt = $pdo->prepare('
            INSERT INTO users (
                name, 
                email, 
                password_hash, 
                manager_id,
                is_admin,
                is_manager,
                active
            ) VALUES (?, ?, ?, ?, ?, ?, 1)
        ');
        
        $stmt->execute([
            $name,
            $email,
            $password,
            $manager_id,
            $is_admin,
            $is_manager
        ]);

        header('Location: ?route=admin/users&success=Usuário criado com sucesso!');
        exit;
    } catch (PDOException $e) {
        header('Location: ?route=admin/users&error=Erro ao criar usuário: ' . $e->getMessage());
        exit;
    }
}

header('Location: ?route=admin/users');
exit;
