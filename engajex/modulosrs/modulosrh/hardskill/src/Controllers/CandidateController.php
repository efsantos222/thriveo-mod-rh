<?php

class CandidateController
{
    public static function dashboard()
    {
        $user = current_user();
        $pdo = get_db();

        $assignments = $pdo->query("
            SELECT ta.*, t.title 
            FROM test_assignments ta
            JOIN tests t ON ta.test_id = t.id
            WHERE ta.candidate_id = {$user['id']}
            ORDER BY ta.assigned_at DESC
        ")->fetchAll();

        view('candidate/dashboard', ['assignments' => $assignments]);
    }

    public static function takeTest($assignmentId)
    {
        $user = current_user();
        $pdo = get_db();

        // Verificar permissão
        $assignment = $pdo->prepare("SELECT * FROM test_assignments WHERE id = ? AND candidate_id = ?");
        $assignment->execute([$assignmentId, $user['id']]);
        $assignment = $assignment->fetch();

        if (!$assignment) {
            die("Teste não encontrado ou acesso negado.");
        }

        if ($assignment['status'] !== 'pending' && $assignment['status'] !== 'in_progress') {
            die("Este teste já foi concluído.");
        }

        // Buscar questões
        $questions = $pdo->prepare("SELECT * FROM questions WHERE test_id = ?");
        $questions->execute([$assignment['test_id']]);
        $questions = $questions->fetchAll();

        // Atualizar status para in_progress se for pending
        if ($assignment['status'] === 'pending') {
            $pdo->prepare("UPDATE test_assignments SET status = 'in_progress' WHERE id = ?")->execute([$assignmentId]);
        }

        // Buscar detalhes do teste
        $test = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
        $test->execute([$assignment['test_id']]);
        $test = $test->fetch();

        view('candidate/take_test', ['assignment' => $assignment, 'questions' => $questions, 'test' => $test]);
    }

    public static function submitTest($assignmentId)
    {
        $user = current_user();
        $pdo = get_db();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar permissão novamente
            $stmt = $pdo->prepare("SELECT id FROM test_assignments WHERE id = ? AND candidate_id = ? AND status != 'completed'");
            $stmt->execute([$assignmentId, $user['id']]);
            if (!$stmt->fetch())
                die("Acesso inválido.");

            $answers = $_POST['answers'] ?? [];

            try {
                $pdo->beginTransaction();

                $stmtInsert = $pdo->prepare("INSERT INTO answers (assignment_id, question_id, candidate_answer) VALUES (?, ?, ?)");

                foreach ($answers as $qId => $ans) {
                    // Se for array (checkboxes, não implementado, mas previsto), converter p string
                    if (is_array($ans))
                        $ans = implode(', ', $ans);
                    $stmtInsert->execute([$assignmentId, $qId, $ans]);
                }

                $pdo->prepare("UPDATE test_assignments SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$assignmentId]);

                $pdo->commit();
                redirect('/candidate/dashboard');

            } catch (Exception $e) {
                $pdo->rollBack();
                die("Erro ao salvar respostas: " . $e->getMessage());
            }
        }
    }
}
