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

if (!isset($data['nome_empresa'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome da empresa é obrigatório']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    $stmt = $pdo->prepare('INSERT INTO empresas (nome_empresa, cnpj) VALUES (?, ?)');
    $stmt->execute([$data['nome_empresa'], $data['cnpj'] ?? null]);

    echo json_encode([
        'success' => true,
        'message' => 'Empresa criada com sucesso',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar empresa']);
}
?>