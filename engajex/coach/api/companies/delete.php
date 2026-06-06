<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_profile'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_empresa'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da empresa é obrigatório']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Check if there are users linked to this company
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE id_empresa = ?');
    $stmt->execute([$data['id_empresa']]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir empresa com usuários vinculados']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM empresas WHERE id_empresa = ?');
    $stmt->execute([$data['id_empresa']]);

    echo json_encode(['success' => true, 'message' => 'Empresa excluída com sucesso']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir empresa']);
}
?>