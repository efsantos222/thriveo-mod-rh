<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    $params = [];
    $where = "WHERE u.id_usuario != ?";
    $params[] = $_SESSION['user_id'];

    if ($_SESSION['user_profile'] === 'master_coach') {
        // Master coach vê apenas seus coachees da sua empresa
        // Verifica se master coach tem empresa definida
        if (empty($_SESSION['id_empresa'])) {
            // Se não tiver empresa, não vê ninguém (ou trate como erro)
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $where .= " AND u.id_empresa = ? AND u.perfil = 'coachee'";
        $params[] = $_SESSION['id_empresa'];
    }

    // Buscar usuários com nome da empresa
    $query = "
        SELECT 
            u.id_usuario,
            u.nome,
            u.email,
            u.perfil,
            u.data_criacao,
            e.nome_empresa,
            e.id_empresa
        FROM usuarios u
        LEFT JOIN empresas e ON u.id_empresa = e.id_empresa
        $where
        ORDER BY u.nome ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar usuários: ' . $e->getMessage()
    ]);
}
?>