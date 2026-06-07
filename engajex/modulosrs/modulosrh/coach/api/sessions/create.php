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

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
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
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert session
    $stmt = $pdo->prepare("
        INSERT INTO sessoes_coaching (
            tipo, id_coach, data_sessao, hora_inicio, hora_fim, 
            local, observacoes, status
        ) VALUES (
            :tipo, :id_coach, :data_sessao, :hora_inicio, :hora_fim,
            :local, :observacoes, 'agendada'
        )
    ");
    
    $stmt->execute([
        'tipo' => $input['type'],
        'id_coach' => $_SESSION['user_id'],
        'data_sessao' => $input['date'],
        'hora_inicio' => $input['time_start'],
        'hora_fim' => $input['time_end'],
        'local' => $input['location'],
        'observacoes' => $input['notes']
    ]);
    
    $sessionId = $pdo->lastInsertId();
    
    // Insert participants
    $stmt = $pdo->prepare("
        INSERT INTO participantes_sessao (id_sessao, id_usuario)
        VALUES (:id_sessao, :id_usuario)
    ");
    
    foreach ($input['participants'] as $participantId) {
        $stmt->execute([
            'id_sessao' => $sessionId,
            'id_usuario' => $participantId
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sessão criada com sucesso',
        'session_id' => $sessionId
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar sessão: ' . $e->getMessage()
    ]);
}
