<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da sessão não fornecido']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        $config['options']
    );
    
    // Check if user has permission to cancel this session
    $stmt = $pdo->prepare("
        SELECT id_coach, status
        FROM sessoes_coaching
        WHERE id_sessao = :id_sessao
    ");
    
    $stmt->execute(['id_sessao' => $input['id']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sessão não encontrada']);
        exit;
    }
    
    if ($session['status'] !== 'agendada') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Apenas sessões agendadas podem ser canceladas']);
        exit;
    }
    
    if ($session['id_coach'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Apenas o coach pode cancelar a sessão']);
        exit;
    }
    
    // Cancel session
    $stmt = $pdo->prepare("
        UPDATE sessoes_coaching
        SET status = 'cancelada'
        WHERE id_sessao = :id_sessao
    ");
    
    $stmt->execute(['id_sessao' => $input['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Sessão cancelada com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cancelar sessão: ' . $e->getMessage()
    ]);
}
