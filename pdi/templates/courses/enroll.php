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
    // Verificar se o usuário já está inscrito
    $stmt = $pdo->prepare('
        SELECT id FROM course_enrollments 
        WHERE user_id = ? AND course_id = ?
    ');
    $stmt->execute([$userId, $courseId]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = [
            'type' => 'warning',
            'message' => 'Você já está inscrito neste curso.'
        ];
        header('Location: ?route=courses/view&id=' . $courseId);
        exit;
    }

    // Inscrever o usuário no curso
    $stmt = $pdo->prepare('
        INSERT INTO course_enrollments (user_id, course_id, status)
        VALUES (?, ?, "enrolled")
    ');
    $stmt->execute([$userId, $courseId]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Inscrição realizada com sucesso!'
    ];
} catch (\PDOException $e) {
    error_log("Error enrolling in course: " . $e->getMessage());
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => 'Erro ao realizar inscrição. Por favor, tente novamente.'
    ];
}

header('Location: ?route=courses/view&id=' . $courseId);
exit;
