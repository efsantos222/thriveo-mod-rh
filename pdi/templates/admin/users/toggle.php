<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ?route=login');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Primeiro, pegar o status atual
        $stmt = $pdo->prepare('SELECT active FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            // Inverter o status
            $newStatus = !$user['active'];
            
            $stmt = $pdo->prepare('UPDATE users SET active = ? WHERE id = ?');
            $stmt->execute([$newStatus, $id]);

            header('Location: ?route=admin/users&success=Status do usuário atualizado com sucesso!');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: ?route=admin/users&error=Erro ao atualizar status: ' . $e->getMessage());
        exit;
    }
}

header('Location: ?route=admin/users');
exit;
