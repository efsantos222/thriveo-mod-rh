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

if (!$input || !isset($input['answers']) || count($input['answers']) !== 24) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $config = require '../../../config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        $config['options']
    );
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Calculate DISC scores
    $scores = [
        'D' => 0, // Dominance
        'I' => 0, // Influence
        'S' => 0, // Steadiness
        'C' => 0  // Compliance
    ];
    
    // Scoring matrix for DISC assessment
    $scoringMatrix = [
        0 => 'D',
        1 => 'I',
        2 => 'S',
        3 => 'C'
    ];
    
    // Calculate scores
    foreach ($input['answers'] as $answer) {
        // Add points for "most" choices
        $scores[$scoringMatrix[$answer['most']]] += 2;
        
        // Subtract points for "least" choices
        $scores[$scoringMatrix[$answer['least']]] -= 1;
    }
    
    // Normalize scores (0-100)
    foreach ($scores as &$score) {
        $score = max(0, min(100, ($score + 24) * 2));
    }
    
    // Determine primary and secondary styles
    arsort($scores);
    $styles = array_keys($scores);
    $primary_style = $styles[0];
    $secondary_style = $styles[1];
    
    // Insert assessment result
    $stmt = $pdo->prepare("
        INSERT INTO questionarios (
            tipo,
            titulo,
            descricao
        ) VALUES (
            'DISC',
            'Avaliação DISC',
            'Avaliação de perfil comportamental DISC'
        )
    ");
    $stmt->execute();
    $questionario_id = $pdo->lastInsertId();
    
    // Insert responses
    $stmt = $pdo->prepare("
        INSERT INTO respostas_questionario (
            id_usuario,
            id_questionario,
            resposta,
            data_resposta
        ) VALUES (
            :id_usuario,
            :id_questionario,
            :resposta,
            NOW()
        )
    ");
    
    $response_data = [
        'scores' => $scores,
        'primary_style' => $primary_style,
        'secondary_style' => $secondary_style,
        'raw_answers' => $input['answers']
    ];
    
    $stmt->execute([
        'id_usuario' => $_SESSION['user_id'],
        'id_questionario' => $questionario_id,
        'resposta' => json_encode($response_data)
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'assessment_id' => $questionario_id,
        'message' => 'Avaliação DISC concluída com sucesso'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar avaliação: ' . $e->getMessage()
    ]);
}
