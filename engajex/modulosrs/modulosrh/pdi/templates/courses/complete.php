<?php
require_once SRC_PATH . '/models/Course.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: ?route=login');
    exit;
}

// Verificar se o ID do curso foi fornecido
if (!isset($_POST['course_id'])) {
    header('Location: ?route=courses/catalog');
    exit;
}

$courseId = $_POST['course_id'];
$userId = $_SESSION['user']['id'];

try {
    // Verificar se o usuário está inscrito
    $stmt = $pdo->prepare('
        SELECT id FROM course_enrollments 
        WHERE user_id = ? AND course_id = ? AND status = "enrolled"
    ');
    $stmt->execute([$userId, $courseId]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Você precisa estar inscrito no curso para marcá-lo como concluído.'
        ];
        header('Location: ?route=courses/view&id=' . $courseId);
        exit;
    }

    // Marcar curso como concluído
    $stmt = $pdo->prepare('
        UPDATE course_enrollments 
        SET status = "completed", completion_date = CURRENT_TIMESTAMP
        WHERE user_id = ? AND course_id = ?
    ');
    $stmt->execute([$userId, $courseId]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Parabéns! Você concluiu o curso.'
    ];
} catch (\PDOException $e) {
    error_log("Error completing course: " . $e->getMessage());
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Erro ao marcar curso como concluído. Por favor, tente novamente.'
    ];
}

header('Location: ?route=courses/view&id=' . $courseId);
exit;
