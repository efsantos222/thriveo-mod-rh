<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
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
    
    // Get past sessions
    $stmt = $pdo->prepare("
        SELECT 
            s.id_sessao,
            s.tipo,
            s.data_sessao,
            s.hora_inicio,
            s.hora_fim,
            s.local,
            s.observacoes,
            s.status,
            c.nome as coach_nome,
            GROUP_CONCAT(u.nome SEPARATOR ', ') as participantes,
            (SELECT COUNT(*) FROM anotacoes_sessao a WHERE a.id_sessao = s.id_sessao) as tem_anotacoes
        FROM sessoes_coaching s
        JOIN usuarios c ON s.id_coach = c.id_usuario
        LEFT JOIN participantes_sessao ps ON s.id_sessao = ps.id_sessao
        LEFT JOIN usuarios u ON ps.id_usuario = u.id_usuario
        WHERE (s.id_coach = :user_id OR ps.id_usuario = :user_id)
        AND (s.data_sessao < CURDATE() OR s.status = 'realizada')
        GROUP BY s.id_sessao
        ORDER BY s.data_sessao DESC, s.hora_inicio DESC
        LIMIT 20
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $sessions = $stmt->fetchAll();
    
    echo json_encode($sessions);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao carregar sessões: ' . $e->getMessage()]);
}
