<?php
session_start();
require_once '../../config/database.php';

// Verificar permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Support POST (common for JSON APIs) or DELETE
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST' && $method !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Get Data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id_usuario'] ?? null;

// Allow GET fallback if query param is used
if (!$userId) {
    $userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuário inválido']);
    exit;
}

// Self-delete prevention
if ($userId == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Não é possível excluir o próprio usuário']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Check target user availability and permissions
    $stmt = $pdo->prepare('SELECT id_usuario, perfil, id_empresa FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetUser) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    // Permission Check
    if ($_SESSION['user_profile'] === 'master_coach') {
        // Must be in same company and target must be coachee
        if ($targetUser['id_empresa'] != $_SESSION['id_empresa'] || $targetUser['perfil'] !== 'coachee') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir este usuário']);
            exit;
        }
    }

    // Delete
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Usuário excluído com sucesso'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
}
?>