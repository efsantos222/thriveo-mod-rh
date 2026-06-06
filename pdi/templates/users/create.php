<?php
// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?route=users');
    exit;
}

// Verificar se o usuário é admin
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    $_SESSION['error'] = "Acesso negado. Você não tem permissão para criar usuários.";
    header('Location: ?route=pdi');
    exit;
}

// Validar e coletar dados do formulário
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$manager_id = $_POST['manager_id'] ?? null;
$is_admin = isset($_POST['is_admin']) ? 1 : 0;
$is_manager = isset($_POST['is_manager']) ? 1 : 0;

// Validar dados obrigatórios
if (empty($name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "Nome, email e senha são obrigatórios.";
    header('Location: ?route=users/new');
    exit;
}

// Validar formato do email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "O email fornecido não é válido.";
    header('Location: ?route=users/new');
    exit;
}

// Validar tamanho da senha
if (strlen($password) < 6) {
    $_SESSION['error'] = "A senha deve ter pelo menos 6 caracteres.";
    header('Location: ?route=users/new');
    exit;
}

try {
    // Verificar se o email já está em uso
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception("Este email já está em uso.");
    }

    // Se um gestor foi selecionado, verificar se ele existe e é realmente um gestor
    if ($manager_id) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? AND is_manager = 1');
        $stmt->execute([$manager_id]);
        if (!$stmt->fetch()) {
            throw new Exception("O gestor selecionado não é válido.");
        }
    }

    // Criar o usuário
    $stmt = $pdo->prepare('
        INSERT INTO users (
            name,
            email,
            password_hash,
            manager_id,
            is_admin,
            is_manager,
            active
        ) VALUES (?, ?, ?, ?, ?, ?, 1)
    ');
    
    $result = $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $manager_id,
        $is_admin,
        $is_manager
    ]);

    if (!$result) {
        throw new Exception("Erro ao criar o usuário.");
    }

    // Redirecionar com mensagem de sucesso
    $_SESSION['success'] = "Usuário criado com sucesso!";
    header('Location: ?route=users');
    
} catch (Exception $e) {
    error_log("Erro ao criar usuário: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ?route=users/new');
}

exit;
