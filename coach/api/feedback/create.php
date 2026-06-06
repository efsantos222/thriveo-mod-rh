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

if (!$input || !validateFeedbackInput($input)) {
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
    
    // Insert feedback
    $stmt = $pdo->prepare("
        INSERT INTO feedback (
            id_remetente,
            id_destinatario,
            categoria,
            tipo_feedback,
            situacao,
            comportamento,
            impacto,
            sugestao,
            data_criacao
        ) VALUES (
            :id_remetente,
            :id_destinatario,
            :categoria,
            :tipo_feedback,
            :situacao,
            :comportamento,
            :impacto,
            :sugestao,
            NOW()
        )
    ");
    
    $stmt->execute([
        'id_remetente' => $_SESSION['user_id'],
        'id_destinatario' => $input['recipient_id'],
        'categoria' => $input['category'],
        'tipo_feedback' => $input['feedback_type'],
        'situacao' => $input['situation'],
        'comportamento' => $input['behavior'],
        'impacto' => $input['impact'],
        'sugestao' => $input['suggestion'] ?? null
    ]);
    
    $feedback_id = $pdo->lastInsertId();
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'feedback_id' => $feedback_id,
        'message' => 'Feedback enviado com sucesso'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar feedback: ' . $e->getMessage()
    ]);
}

function validateFeedbackInput($input) {
    return isset($input['recipient_id']) &&
           isset($input['category']) &&
           isset($input['feedback_type']) &&
           isset($input['situation']) &&
           isset($input['behavior']) &&
           isset($input['impact']);
}
