<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $id = $_GET['id'] ?? null;

    if ($id) {
        // Fetch Single Detail
        $stmt = $pdo->prepare("SELECT id, target_url, threat_score, created_at, analysis_json FROM ci_history WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $item['analysis'] = json_decode($item['analysis_json'], true);
            unset($item['analysis_json']);
            echo json_encode($item);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
        }
    } else {
        // Fetch List (Lightweight)
        $stmt = $pdo->prepare("SELECT id, target_url, threat_score, created_at FROM ci_history WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($history);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>