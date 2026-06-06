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

if (!$input || !isset($input['answers']) || count($input['answers']) < 70) {
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
    
    // Calculate MBTI scores
    $scores = [
        'E' => 0, 'I' => 0,  // Extraversion vs. Introversion
        'S' => 0, 'N' => 0,  // Sensing vs. Intuition
        'T' => 0, 'F' => 0,  // Thinking vs. Feeling
        'J' => 0, 'P' => 0   // Judging vs. Perceiving
    ];
    
    // Count responses for each preference
    foreach ($input['answers'] as $questionId => $answer) {
        $scores[$answer]++;
    }
    
    // Determine type
    $type = '';
    $type .= $scores['E'] > $scores['I'] ? 'E' : 'I';
    $type .= $scores['S'] > $scores['N'] ? 'S' : 'N';
    $type .= $scores['T'] > $scores['F'] ? 'T' : 'F';
    $type .= $scores['J'] > $scores['P'] ? 'J' : 'P';
    
    // Calculate percentages
    $percentages = [
        'E' => round(($scores['E'] / ($scores['E'] + $scores['I'])) * 100),
        'I' => round(($scores['I'] / ($scores['E'] + $scores['I'])) * 100),
        'S' => round(($scores['S'] / ($scores['S'] + $scores['N'])) * 100),
        'N' => round(($scores['N'] / ($scores['S'] + $scores['N'])) * 100),
        'T' => round(($scores['T'] / ($scores['T'] + $scores['F'])) * 100),
        'F' => round(($scores['F'] / ($scores['T'] + $scores['F'])) * 100),
        'J' => round(($scores['J'] / ($scores['J'] + $scores['P'])) * 100),
        'P' => round(($scores['P'] / ($scores['J'] + $scores['P'])) * 100)
    ];
    
    // Insert assessment result
    $stmt = $pdo->prepare("
        INSERT INTO questionarios (
            tipo,
            titulo,
            descricao
        ) VALUES (
            'MBTI',
            'Avaliação MBTI',
            'Avaliação de tipo de personalidade MBTI'
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
        'type' => $type,
        'scores' => $scores,
        'percentages' => $percentages,
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
        'message' => 'Avaliação MBTI concluída com sucesso'
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
