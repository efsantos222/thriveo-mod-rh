<?php
// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?route=pdi');
    exit;
}

// Validar e coletar dados do formulário
$id = $_POST['id'] ?? '';
$short_term_goals = $_POST['short_term_goals'] ?? '';
$medium_term_goals = $_POST['medium_term_goals'] ?? '';
$long_term_goals = $_POST['long_term_goals'] ?? '';
$competencies = $_POST['competencies'] ?? '';
$actions = $_POST['actions'] ?? '';
$indicators = $_POST['indicators'] ?? '';
$status = $_POST['status'] ?? 'draft';
$user_id = $_SESSION['user']['id'];

// Validar dados obrigatórios
if (empty($id) || empty($short_term_goals) || empty($medium_term_goals) || empty($long_term_goals)) {
    $_SESSION['error'] = "Todos os campos obrigatórios devem ser preenchidos.";
    header('Location: ?route=pdi/edit&id=' . $id);
    exit;
}

try {
    // Verificar se o PDI existe e pertence ao usuário
    $stmt = $pdo->prepare('SELECT status FROM pdis WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
    $currentPdi = $stmt->fetch();

    if (!$currentPdi) {
        throw new Exception("PDI não encontrado ou você não tem permissão para editá-lo.");
    }

    if ($currentPdi['status'] !== 'draft') {
        throw new Exception("Este PDI não pode mais ser editado pois não está mais em rascunho.");
    }

    // Validar o novo status
    if (!in_array($status, ['draft', 'pending'])) {
        throw new Exception("Status inválido.");
    }

    // Atualizar o PDI
    $stmt = $pdo->prepare("
        UPDATE pdis SET
            short_term_goals = ?,
            medium_term_goals = ?,
            long_term_goals = ?,
            competencies = ?,
            actions = ?,
            indicators = ?,
            status = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND user_id = ? AND status = 'draft'
    ");
    
    $result = $stmt->execute([
        $short_term_goals,
        $medium_term_goals,
        $long_term_goals,
        $competencies,
        $actions,
        $indicators,
        $status,
        $id,
        $user_id
    ]);

    if (!$result) {
        throw new Exception("Erro ao atualizar o PDI.");
    }

    // Redirecionar com mensagem de sucesso
    $_SESSION['success'] = "PDI atualizado com sucesso!" . 
        ($status === 'pending' ? " O PDI foi enviado para aprovação do gestor." : "");
    header('Location: ?route=pdi/view&id=' . $id);
    
} catch (Exception $e) {
    error_log("Erro ao atualizar PDI: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ?route=pdi/edit&id=' . $id);
}

exit;
