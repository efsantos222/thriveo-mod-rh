<?php
// Verificar se o usuário é admin
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    $_SESSION['error'] = "Acesso negado. Você não tem permissão para ativar/desativar usuários.";
    header('Location: ?route=pdi');
    exit;
}

// Validar parâmetros
$id = $_GET['id'] ?? '';
$status = isset($_GET['status']) && $_GET['status'] === 'true';

if (empty($id)) {
    $_SESSION['error'] = "ID do usuário não fornecido.";
    header('Location: ?route=users');
    exit;
}

try {
    // Não permitir desativar o próprio usuário
    if ($id == $_SESSION['user']['id']) {
        throw new Exception("Você não pode desativar seu próprio usuário.");
    }

    // Verificar se o usuário existe
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception("Usuário não encontrado.");
    }

    // Atualizar status
    $stmt = $pdo->prepare('UPDATE users SET active = ? WHERE id = ?');
    $result = $stmt->execute([$status ? 1 : 0, $id]);

    if (!$result) {
        throw new Exception("Erro ao atualizar o status do usuário.");
    }

    // Redirecionar com mensagem de sucesso
    $_SESSION['success'] = "Usuário " . ($status ? "ativado" : "desativado") . " com sucesso!";
    header('Location: ?route=users');
    
} catch (Exception $e) {
    error_log("Erro ao alterar status do usuário: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ?route=users');
}

exit;
