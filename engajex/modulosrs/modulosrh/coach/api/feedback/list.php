<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Buscar feedback recebido e enviado pelo usuário
    $query = '
        SELECT 
            f.id_feedback,
            f.id_remetente,
            f.id_destinatario,
            f.categoria,
            f.tipo_feedback,
            f.situacao,
            f.comportamento,
            f.impacto,
            f.sugestao,
            f.data_criacao,
            u_from.nome as from_name,
            u_from.perfil as from_perfil,
            u_to.nome as to_name,
            u_to.perfil as to_perfil,
            CASE 
                WHEN f.id_remetente = ? THEN "enviado"
                ELSE "recebido"
            END as type
        FROM feedback f
        JOIN usuarios u_from ON f.id_remetente = u_from.id_usuario
        JOIN usuarios u_to ON f.id_destinatario = u_to.id_usuario
        WHERE f.id_remetente = ? OR f.id_destinatario = ?
        ORDER BY f.data_criacao DESC
    ';

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id']
    ]);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $feedback,
        'debug' => [
            'query' => $query,
            'params' => [
                $_SESSION['user_id'],
                $_SESSION['user_id'],
                $_SESSION['user_id']
            ],
            'user_id' => $_SESSION['user_id'],
            'user_profile' => $_SESSION['user_profile']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Erro ao listar feedback: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao listar feedback: ' . $e->getMessage(),
        'debug' => [
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
}
