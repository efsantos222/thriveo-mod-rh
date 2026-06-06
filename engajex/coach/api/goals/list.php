<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
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
    
    // Get goals with their milestones
    $stmt = $pdo->prepare("
        SELECT 
            m.id_meta,
            m.titulo,
            m.categoria,
            m.criterios_smart,
            m.data_inicio,
            m.data_fim,
            m.status,
            m.data_criacao,
            m.ultima_atualizacao,
            GROUP_CONCAT(
                CONCAT(
                    mm.id_marco, ':', 
                    mm.descricao, ':', 
                    mm.concluido
                )
            ) as milestones
        FROM metas m
        LEFT JOIN marcos_meta mm ON m.id_meta = mm.id_meta
        WHERE m.id_usuario = :id_usuario
        GROUP BY m.id_meta
        ORDER BY m.data_criacao DESC
    ");
    
    $stmt->execute(['id_usuario' => $_SESSION['user_id']]);
    
    $goals = [];
    while ($row = $stmt->fetch()) {
        $milestones = [];
        if ($row['milestones']) {
            foreach (explode(',', $row['milestones']) as $milestone) {
                list($id, $description, $completed) = explode(':', $milestone);
                $milestones[] = [
                    'id' => $id,
                    'description' => $description,
                    'completed' => $completed == '1'
                ];
            }
        }
        
        $goals[] = [
            'id' => $row['id_meta'],
            'title' => $row['titulo'],
            'category' => $row['categoria'],
            'smart' => json_decode($row['criterios_smart'], true),
            'start_date' => $row['data_inicio'],
            'end_date' => $row['data_fim'],
            'status' => $row['status'],
            'created_at' => $row['data_criacao'],
            'updated_at' => $row['ultima_atualizacao'],
            'milestones' => $milestones
        ];
    }
    
    echo json_encode([
        'success' => true,
        'goals' => $goals
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar metas: ' . $e->getMessage()
    ]);
}
