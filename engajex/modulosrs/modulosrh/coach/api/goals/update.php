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

if (!$input || !isset($input['goal_id']) || !isset($input['milestones'])) {
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
    
    // Verify goal ownership
    $stmt = $pdo->prepare("
        SELECT id_meta 
        FROM metas 
        WHERE id_meta = :id_meta 
        AND id_usuario = :id_usuario
    ");
    
    $stmt->execute([
        'id_meta' => $input['goal_id'],
        'id_usuario' => $_SESSION['user_id']
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Meta não encontrada ou acesso negado');
    }
    
    // Update milestones
    $stmt = $pdo->prepare("
        UPDATE marcos_meta
        SET concluido = :concluido
        WHERE id_marco = :id_marco
        AND id_meta = :id_meta
    ");
    
    foreach ($input['milestones'] as $milestone) {
        $stmt->execute([
            'id_marco' => $milestone['id'],
            'concluido' => $milestone['completed'],
            'id_meta' => $input['goal_id']
        ]);
    }
    
    // Check if all milestones are completed
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN concluido = 1 THEN 1 ELSE 0 END) as completed
        FROM marcos_meta
        WHERE id_meta = :id_meta
    ");
    
    $stmt->execute(['id_meta' => $input['goal_id']]);
    $result = $stmt->fetch();
    
    // Update goal status if all milestones are completed
    if ($result['total'] > 0 && $result['total'] == $result['completed']) {
        $stmt = $pdo->prepare("
            UPDATE metas
            SET status = 'concluida',
                ultima_atualizacao = NOW()
            WHERE id_meta = :id_meta
        ");
        
        $stmt->execute(['id_meta' => $input['goal_id']]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE metas
            SET ultima_atualizacao = NOW()
            WHERE id_meta = :id_meta
        ");
        
        $stmt->execute(['id_meta' => $input['goal_id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Meta atualizada com sucesso'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar meta: ' . $e->getMessage()
    ]);
}
