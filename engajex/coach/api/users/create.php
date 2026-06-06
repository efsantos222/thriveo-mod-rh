<?php
session_start();
require_once '../../config/database.php';

// Verificar permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_profile'], ['administrador', 'master_coach'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validar dados obrigatórios
if (!isset($data['nome']) || !isset($data['email']) || !isset($data['senha']) || !isset($data['perfil'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Lógica de permissão de criação
$empresaId = null;

if ($_SESSION['user_profile'] === 'master_coach') {
    // Master Coach só cria Coachee para sua própria empresa
    if ($data['perfil'] !== 'coachee') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Master Coach só pode criar perfil Coachee']);
        exit;
    }
    $empresaId = $_SESSION['id_empresa'];
    if (empty($empresaId)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Master Coach sem empresa vinculada']);
        exit;
    }

} else if ($_SESSION['user_profile'] === 'administrador') {
    // Admin pode definir empresa se vier no payload
    if (isset($data['id_empresa']) && !empty($data['id_empresa'])) {
        $empresaId = $data['id_empresa'];
    }
    // Se não vier, cria sem empresa (ou poderia forçar, mas por enquanto opcional para Admin)
}

try {
    $config = require '../../config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Verificar email
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado']);
        exit;
    }

    // Inserir
    $stmt = $pdo->prepare('
        INSERT INTO usuarios (nome, email, senha, perfil, id_empresa, data_criacao)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');

    $stmt->execute([
        $data['nome'],
        $data['email'],
        $data['senha'],
        $data['perfil'],
        $empresaId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Usuário criado com sucesso',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário: ' . $e->getMessage()]);
}
?>