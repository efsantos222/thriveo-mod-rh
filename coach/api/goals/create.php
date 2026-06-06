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

if (!$input || !validateGoalInput($input)) {
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
    
    // Insert goal
    $stmt = $pdo->prepare("
        INSERT INTO metas (
            id_usuario,
            titulo,
            categoria,
            criterios_smart,
            data_inicio,
            data_fim,
            status
        ) VALUES (
            :id_usuario,
            :titulo,
            :categoria,
            :criterios_smart,
            :data_inicio,
            :data_fim,
            'em_andamento'
        )
    ");
    
    $stmt->execute([
        'id_usuario' => $_SESSION['user_id'],
        'titulo' => $input['title'],
        'categoria' => $input['category'],
        'criterios_smart' => json_encode($input['smart']),
        'data_inicio' => $input['smart']['time_bound']['start_date'],
        'data_fim' => $input['smart']['time_bound']['end_date']
    ]);
    
    $goal_id = $pdo->lastInsertId();
    
    // Insert milestones
    if (!empty($input['milestones'])) {
        $stmt = $pdo->prepare("
            INSERT INTO marcos_meta (
                id_meta,
                descricao,
                concluido
            ) VALUES (
                :id_meta,
                :descricao,
                false
            )
        ");
        
        foreach ($input['milestones'] as $milestone) {
            $stmt->execute([
                'id_meta' => $goal_id,
                'descricao' => $milestone
            ]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'goal_id' => $goal_id,
        'message' => 'Meta criada com sucesso'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar meta: ' . $e->getMessage()
    ]);
}

function validateGoalInput($input) {
    return isset($input['title']) &&
           isset($input['category']) &&
           isset($input['smart']) &&
           isset($input['smart']['specific']) &&
           isset($input['smart']['measurable']) &&
           isset($input['smart']['achievable']) &&
           isset($input['smart']['relevant']) &&
           isset($input['smart']['time_bound']) &&
           isset($input['smart']['time_bound']['start_date']) &&
           isset($input['smart']['time_bound']['end_date']) &&
           isset($input['milestones']) &&
           is_array($input['milestones']);
}
