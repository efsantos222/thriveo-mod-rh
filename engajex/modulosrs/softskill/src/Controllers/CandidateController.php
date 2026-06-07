<?php
class CandidateController
{

    private function render($view, $data = [])
    {
        extract($data);
        $contentView = "../views/candidate/$view.php";
        $pageTitle = ucfirst(str_replace('_', ' ', $view));
        require_once '../views/layouts/layout.php';
    }

    public function dashboard()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM ss_test_assignments WHERE candidate_id = :id ORDER BY created_at DESC");
        $stmt->bindParam(':id', $_SESSION['ss_id']);
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('dashboard', ['tests' => $tests, 'pageTitle' => 'Meus Testes']);
    }

    public function takeTest()
    {
        $id = $_GET['id'];
        $db = new Database();
        $conn = $db->getConnection();

        // Verify assignment
        $stmt = $conn->prepare("SELECT * FROM ss_test_assignments WHERE id = :id AND candidate_id = :cid AND status = 'pending'");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cid', $_SESSION['ss_id']);
        $stmt->execute();
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            header("Location: " . BASE_URL . "candidate/dashboard");
            exit;
        }

        // Get Questions
        $stmtQ = $conn->prepare("SELECT * FROM ss_questions WHERE test_type = :type");
        $stmtQ->bindParam(':type', $assignment['test_type']);
        $stmtQ->execute();
        $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        $this->render('take_test', [
            'assignment' => $assignment,
            'questions' => $questions,
            'pageTitle' => 'Realizar Teste: ' . $assignment['test_type']
        ]);
    }

    public function submitTest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignmentId = $_POST['assignment_id'];
            $answers = $_POST['answers']; // Array of question_id => value

            $db = new Database();
            $conn = $db->getConnection();

            // Validate assignment
            $stmt = $conn->prepare("SELECT * FROM ss_test_assignments WHERE id = :id AND candidate_id = :cid");
            $stmt->bindParam(':id', $assignmentId);
            $stmt->bindParam(':cid', $_SESSION['ss_id']);
            $stmt->execute();
            if (!$stmt->fetch())
                die("Acesso negado.");

            // Insert Answers
            $stmtIns = $conn->prepare("INSERT INTO ss_answers (assignment_id, question_id, answer_value) VALUES (:aid, :qid, :val)");

            foreach ($answers as $qid => $val) {
                $stmtIns->bindParam(':aid', $assignmentId);
                $stmtIns->bindParam(':qid', $qid);
                $stmtIns->bindParam(':val', $val);
                $stmtIns->execute();
            }

            // Update Status
            $stmtUp = $conn->prepare("UPDATE ss_test_assignments SET status = 'completed', completed_at = NOW() WHERE id = :id");
            $stmtUp->bindParam(':id', $assignmentId);
            $stmtUp->execute();

            header("Location: " . BASE_URL . "candidate/dashboard");
        }
    }

    public function viewFeedback()
    {
        $id = $_GET['id'];
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM ss_test_assignments WHERE id = :id AND candidate_id = :cid AND feedback_sent = 1");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cid', $_SESSION['ss_id']);
        $stmt->execute();
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            echo "Relatório não disponível.";
            exit;
        }

        $this->render('feedback', ['assignment' => $assignment, 'pageTitle' => 'Meu Resultado']);
    }
}
