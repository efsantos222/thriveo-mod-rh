<?php
// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?route=pdi');
    exit;
}

// Validar e coletar dados do formulário
$short_term_goals = $_POST['short_term_goals'] ?? '';
$medium_term_goals = $_POST['medium_term_goals'] ?? '';
$long_term_goals = $_POST['long_term_goals'] ?? '';
$competencies = $_POST['competencies'] ?? '';
$actions = $_POST['actions'] ?? '';
$indicators = $_POST['indicators'] ?? '';
$user_id = $_SESSION['user']['id'];

// Validar dados obrigatórios
if (empty($short_term_goals) || empty($medium_term_goals) || empty($long_term_goals)) {
    $_SESSION['error'] = "Os objetivos de curto, médio e longo prazo são obrigatórios.";
    header('Location: ?route=pdi/novo');
    exit;
}

try {
    // Debug: Mostrar os valores que serão inseridos
    error_log("Inserindo PDI com os seguintes valores:");
    error_log("user_id: " . $user_id);
    error_log("short_term_goals: " . $short_term_goals);
    error_log("medium_term_goals: " . $medium_term_goals);
    error_log("long_term_goals: " . $long_term_goals);
    error_log("competencies: " . $competencies);
    error_log("actions: " . $actions);
    error_log("indicators: " . $indicators);
    
    // Preparar e executar a query
    $stmt = $pdo->prepare("
        INSERT INTO pdis (
            user_id,
            manager_id,
            short_term_goals,
            medium_term_goals,
            long_term_goals,
            competencies,
            actions,
            indicators,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft')
    ");
    
    $result = $stmt->execute([
        $user_id,           // user_id
        $user_id,           // manager_id (mesmo que user_id por enquanto)
        $short_term_goals,  // short_term_goals
        $medium_term_goals, // medium_term_goals
        $long_term_goals,   // long_term_goals
        $competencies,      // competencies
        $actions,          // actions
        $indicators        // indicators
    ]);

    if (!$result) {
        $error = $stmt->errorInfo();
        throw new PDOException("Erro na inserção: " . $error[2]);
    }
    
    // Redirecionar com mensagem de sucesso
    $_SESSION['success'] = "PDI criado com sucesso!";
    header('Location: ?route=pdi');
    
} catch (PDOException $e) {
    error_log("Erro detalhado ao salvar PDI: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Erro completo: " . print_r($e, true));
    $_SESSION['error'] = "Erro ao salvar o PDI: " . $e->getMessage();
    header('Location: ?route=pdi/novo');
}

exit;
