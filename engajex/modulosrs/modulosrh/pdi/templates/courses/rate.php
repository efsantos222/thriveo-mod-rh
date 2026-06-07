<?php
require_once SRC_PATH . '/models/Course.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: ?route=login');
    exit;
}

// Verificar se os dados necessários foram fornecidos
if (!isset($_POST['course_id']) || !isset($_POST['rating'])) {
    header('Location: ?route=courses/catalog');
    exit;
}

$courseId = $_POST['course_id'];
$userId = $_SESSION['user']['id'];
$rating = (int)$_POST['rating'];
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;

// Validar a avaliação
if ($rating < 1 || $rating > 5) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Avaliação inválida. Por favor, escolha uma nota de 1 a 5.'
    ];
    header('Location: ?route=courses/view&id=' . $courseId);
    exit;
}

try {
    // Verificar se o usuário está inscrito e completou o curso
    $stmt = $pdo->prepare('
        SELECT id FROM course_enrollments 
        WHERE user_id = ? AND course_id = ? AND status = "completed"
    ');
    $stmt->execute([$userId, $courseId]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Você precisa completar o curso antes de avaliá-lo.'
        ];
        header('Location: ?route=courses/view&id=' . $courseId);
        exit;
    }

    // Verificar se o usuário já avaliou este curso
    $stmt = $pdo->prepare('
        SELECT id FROM course_ratings 
        WHERE user_id = ? AND course_id = ?
    ');
    $stmt->execute([$userId, $courseId]);
    
    if ($stmt->rowCount() > 0) {
        // Atualizar avaliação existente
        $stmt = $pdo->prepare('
            UPDATE course_ratings 
            SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP
            WHERE user_id = ? AND course_id = ?
        ');
        $stmt->execute([$rating, $comment, $userId, $courseId]);
        $message = 'Avaliação atualizada com sucesso!';
    } else {
        // Criar nova avaliação
        $stmt = $pdo->prepare('
            INSERT INTO course_ratings (user_id, course_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $courseId, $rating, $comment]);
        $message = 'Avaliação enviada com sucesso!';
    }

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => $message
    ];
} catch (\PDOException $e) {
    error_log("Error rating course: " . $e->getMessage());
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Erro ao enviar avaliação. Por favor, tente novamente.'
    ];
}

header('Location: ?route=courses/view&id=' . $courseId);
exit;
