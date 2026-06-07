<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if (!in_array($_SESSION['role'], ['responsible', 'company_admin', 'manager', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso Negado (Role)']);
    exit;
}

$companyId = $_SESSION['company_id'] ?? 1;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list_cost_centers':
        try {
            $stmt = $pdo->prepare("SELECT * FROM cost_centers WHERE company_id = ? ORDER BY name ASC");
            $stmt->execute([$companyId]);
            $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $centers]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_cost_center':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Nome do Centro de Custo é obrigatório']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO cost_centers (company_id, name, code) VALUES (?, ?, ?)");
            $stmt->execute([$companyId, $name, $code]);
            echo json_encode(['success' => true, 'message' => 'Centro de custo criado com sucesso!', 'id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar: ' . $e->getMessage()]);
        }
        break;

    case 'delete_cost_center':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }
        $id = $_POST['id'] ?? 0;

        try {
            $stmt = $pdo->prepare("DELETE FROM cost_centers WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $companyId]);
            echo json_encode(['success' => true, 'message' => 'Centro de custo removido']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover: ' . $e->getMessage()]);
        }
        break;

    case 'update_cost_center':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE cost_centers SET name = ?, code = ? WHERE id = ? AND company_id = ?");
            $stmt->execute([$name, $code, $id, $companyId]);
            echo json_encode(['success' => true, 'message' => 'Atualizado com sucesso']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
        break;

    // Future: Add cases for 'create_category', 'list_categories', etc.

    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
        break;
}
