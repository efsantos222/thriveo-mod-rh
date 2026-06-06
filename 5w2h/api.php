<?php
// api.php
require_once 'includes/config.php';

// Ensure user is logged in for all API calls
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Acesso negado'], 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_actions':
        $query = "SELECT * FROM actions WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'save_action':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            jsonResponse(['error' => 'Dados inválidos'], 400);

        $fields = [
            'user_id' => $_SESSION['user_id'],
            'what' => $data['what'],
            'why' => $data['why'] ?? '',
            'where' => $data['where'] ?? '',
            'who' => $data['who'] ?? '',
            'when_start' => $data['when_start'] ?: null,
            'when_end' => $data['when_end'] ?: null,
            'how' => $data['how'] ?? '',
            'how_much' => $data['how_much'] ?? '',
            'category' => $data['category'] ?? 'outro',
            'priority' => $data['priority'] ?? 'media',
            'status' => $data['status'] ?? 'pendente',
            'progress' => intval($data['progress'] ?? 0),
            'notes' => $data['notes'] ?? '',
        ];

        if (isset($data['id']) && $data['id']) {
            // Update
            $sql = "UPDATE actions SET what=:what, why=:why, `where`=:where, who=:who, when_start=:when_start, 
                    when_end=:when_end, how=:how, how_much=:how_much, category=:category, priority=:priority, 
                    status=:status, progress=:progress, notes=:notes WHERE id=:id AND user_id=:user_id";
            $fields['id'] = $data['id'];
        } else {
            // Insert
            $sql = "INSERT INTO actions (user_id, what, why, `where`, who, when_start, when_end, how, how_much, category, priority, status, progress, notes) 
                    VALUES (:user_id, :what, :why, :where, :who, :when_start, :when_end, :how, :how_much, :category, :priority, :status, :progress, :notes)";
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
            jsonResponse(['success' => true, 'id' => $data['id'] ?? $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    case 'delete_action':
        $id = $_GET['id'] ?? null;
        if (!$id)
            jsonResponse(['error' => 'ID não fornecido'], 400);

        $stmt = $pdo->prepare("DELETE FROM actions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        jsonResponse(['success' => true]);
        break;

    case 'get_users':
        checkAdmin();
        $stmt = $pdo->query("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id");
        jsonResponse($stmt->fetchAll());
        break;

    case 'get_companies':
        checkAdmin();
        $stmt = $pdo->query("SELECT * FROM companies");
        jsonResponse($stmt->fetchAll());
        break;

    case 'save_user':
        checkAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            jsonResponse(['error' => 'Dados inválidos'], 400);

        $params = [
            'company_id' => $data['company_id'] ?: null,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user',
        ];

        if (isset($data['id']) && $data['id']) {
            $sql = "UPDATE users SET company_id=:company_id, name=:name, email=:email, role=:role";
            if (!empty($data['password'])) {
                $sql .= ", password=:password";
                $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id=:id";
            $params['id'] = $data['id'];
        } else {
            $sql = "INSERT INTO users (company_id, name, email, password, role) VALUES (:company_id, :name, :email, :password, :role)";
            $params['password'] = password_hash($data['password'] ?? '123456', PASSWORD_DEFAULT);
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true]);
        } catch (PDOException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    case 'save_company':
        checkAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            jsonResponse(['error' => 'Dados inválidos'], 400);

        if (isset($data['id']) && $data['id']) {
            $stmt = $pdo->prepare("UPDATE companies SET name = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
            $stmt->execute([$data['name']]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_user':
        checkAdmin();
        $id = $_GET['id'] ?? null;
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    case 'delete_company':
        checkAdmin();
        $id = $_GET['id'] ?? null;
        $pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Ação não encontrada'], 404);
}
?>