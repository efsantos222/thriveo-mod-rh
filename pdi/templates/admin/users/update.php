<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ?route=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $manager_id = $_POST['manager_id'] ?: null;
    $user_type = $_POST['user_type'] ?? 'employee';

    // Definir is_admin e is_manager baseado no tipo
    $is_admin = ($user_type === 'admin');
    $is_manager = ($user_type === 'manager' || $user_type === 'admin');

    try {
        // Se uma nova senha foi fornecida, incluí-la na atualização
        if ($password) {
            $sql = '
                UPDATE users 
                SET name = ?, 
                    email = ?, 
                    password_hash = ?,
                    manager_id = ?,
                    is_admin = ?,
                    is_manager = ?
                WHERE id = ?
            ';
            $params = [$name, $email, $password, $manager_id, $is_admin, $is_manager, $id];
        } else {
            $sql = '
                UPDATE users 
                SET name = ?, 
                    email = ?, 
                    manager_id = ?,
                    is_admin = ?,
                    is_manager = ?
                WHERE id = ?
            ';
            $params = [$name, $email, $manager_id, $is_admin, $is_manager, $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header('Location: ?route=admin/users&success=Usuário atualizado com sucesso!');
        exit;
    } catch (PDOException $e) {
        header('Location: ?route=admin/users&error=Erro ao atualizar usuário: ' . $e->getMessage());
        exit;
    }
}

header('Location: ?route=admin/users');
exit;
