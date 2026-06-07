<?php
session_start();
require_once '../../config/database.php';

// Check permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['email']) || !isset($data['nome'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Security Check: Master Coach can only edit coachees from their company
if ($_SESSION['user_profile'] === 'master_coach') {
    // Fetch user to be edited to verify ownership
    try {
        $config = require '../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

        $stmt = $pdo->prepare("SELECT id_empresa, perfil FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$data['id_usuario']]);
        $target = $stmt->fetch();

        if (!$target || $target['id_empresa'] != $_SESSION['id_empresa'] || $target['perfil'] !== 'coachee') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar este usuário']);
            exit;
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno de verificação']);
        exit;
    }
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Build Update Query
    $fields = "nome = ?, email = ?";
    $params = [$data['nome'], $data['email']];

    if (!empty($data['senha'])) {
        $fields .= ", senha = ?";
        $params[] = $data['senha']; // Remember: User asked for plain text passwords in previous context
    }

    if (isset($data['perfil']) && $_SESSION['user_profile'] === 'administrador') {
        $fields .= ", perfil = ?";
        $params[] = $data['perfil'];
    }

    if (isset($data['id_empresa']) && $_SESSION['user_profile'] === 'administrador') {
        $fields .= ", id_empresa = ?";
        $params[] = !empty($data['id_empresa']) ? $data['id_empresa'] : null;
    }

    $params[] = $data['id_usuario'];

    $stmt = $pdo->prepare("UPDATE usuarios SET $fields WHERE id_usuario = ?");
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
}
?>