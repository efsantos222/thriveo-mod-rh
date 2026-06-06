<?php
class RecruiterController
{

    private function render($view, $data = [])
    {
        extract($data);
        $contentView = "../views/recruiter/$view.php";
        $pageTitle = ucfirst($view);
        require_once '../views/layouts/layout.php';
    }

    public function dashboard()
    {
        // Show pending tests count, completed tests count
        $db = new Database();
        $conn = $db->getConnection();
        $recruiterId = $_SESSION['user_id'];

        // Count candidates
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE recruiter_id = :rid");
        $stmt->bindParam(':rid', $recruiterId);
        $stmt->execute();
        $candidateCount = $stmt->fetchColumn();

        // Count tests
        $stmt = $conn->prepare("SELECT 
            SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
            FROM test_assignments WHERE recruiter_id = :rid");
        $stmt->bindParam(':rid', $recruiterId);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch tests needing AI correction (Completed but no Report)
        $stmtPendingAI = $conn->prepare("
            SELECT ta.id, ta.test_type, ta.completed_at, u.name as candidate_name 
            FROM test_assignments ta 
            JOIN users u ON ta.candidate_id = u.id 
            WHERE ta.recruiter_id = :rid 
            AND ta.status = 'completed' 
            AND (ta.ai_report IS NULL OR ta.ai_report = '')
            ORDER BY ta.completed_at DESC
        ");
        $stmtPendingAI->bindParam(':rid', $recruiterId);
        $stmtPendingAI->execute();
        $testsPendingAI = $stmtPendingAI->fetchAll(PDO::FETCH_ASSOC);

        $this->render('dashboard', [
            'pageTitle' => 'Painel do Recrutador',
            'candidateCount' => $candidateCount,
            'completedTests' => $stats['completed'] ?? 0,
            'pendingTests' => $stats['pending'] ?? 0,
            'testsPendingAI' => $testsPendingAI
        ]);
    }

    public function candidates()
    {
        $userModel = new User();
        $candidates = $userModel->getCandidatesByRecruiter($_SESSION['user_id']);
        $this->render('candidates', ['candidates' => $candidates, 'pageTitle' => 'Meus Candidatos']);
    }

    public function saveCandidate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            $userModel->name = $_POST['name'];
            $userModel->email = $_POST['email'];
            $userModel->password = $_POST['password'];
            $userModel->role = 'candidate';
            $userModel->recruiter_id = $_SESSION['user_id'];

            if ($userModel->create()) {
                header("Location: " . BASE_URL . "recruiter/candidates");
            } else {
                die("Erro ao criar candidato");
            }
        }
    }

    public function deleteCandidate()
    {
        if (isset($_GET['id'])) {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id AND recruiter_id = :rid");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->bindParam(':rid', $_SESSION['user_id']);
            $stmt->execute();
            header("Location: " . BASE_URL . "recruiter/candidates");
        }
    }

    // View candidate details and assigned tests
    public function viewCandidate()
    {
        $id = $_GET['id'];
        $db = new Database();
        $conn = $db->getConnection();

        // Get Candidate Info
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id AND recruiter_id = :rid");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':rid', $_SESSION['user_id']);
        $stmt->execute();
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidate)
            die("Candidato não encontrado ou acesso negado.");

        // Get Tests
        $stmtTests = $conn->prepare("SELECT * FROM test_assignments WHERE candidate_id = :cid ORDER BY created_at DESC");
        $stmtTests->bindParam(':cid', $id);
        $stmtTests->execute();
        $tests = $stmtTests->fetchAll(PDO::FETCH_ASSOC);

        $this->render('candidate_details', ['candidate' => $candidate, 'tests' => $tests, 'pageTitle' => $candidate['name']]);
    }

    public function assignTest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $candidateId = $_POST['candidate_id'];
            $testType = $_POST['test_type'];

            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("INSERT INTO test_assignments (recruiter_id, candidate_id, test_type) VALUES (:rid, :cid, :type)");
            $stmt->bindParam(':rid', $_SESSION['user_id']);
            $stmt->bindParam(':cid', $candidateId);
            $stmt->bindParam(':type', $testType);
            $stmt->execute();

            header("Location: " . BASE_URL . "recruiter/viewCandidate?id=" . $candidateId);
        }
    }

    public function viewResult()
    {
        $assignmentId = $_GET['id'];
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT ta.*, u.name as candidate_name FROM test_assignments ta JOIN users u ON ta.candidate_id = u.id WHERE ta.id = :id AND ta.recruiter_id = :rid");
        $stmt->bindParam(':id', $assignmentId);
        $stmt->bindParam(':rid', $_SESSION['user_id']);
        $stmt->execute();
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment)
            die("Teste não encontrado.");

        $this->render('result', ['assignment' => $assignment, 'pageTitle' => 'Resultado: ' . $assignment['test_type']]);
    }

    public function generateReport()
    {
        $assignmentId = $_GET['id'];
        // Logic to fetch answers, call OpenAI, update DB, redirect back
        $openAI = new OpenAIService();
        $report = $openAI->generateReport($assignmentId);

        if ($report) {
            header("Location: " . BASE_URL . "recruiter/viewResult?id=" . $assignmentId);
        } else {
            die("Erro ao gerar relatório com IA.");
        }
    }

    public function sendFeedback()
    {
        $assignmentId = $_GET['id'];
        // Toggle visibility
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE test_assignments SET feedback_sent = 1 WHERE id = :id AND recruiter_id = :rid");
        $stmt->bindParam(':id', $assignmentId);
        $stmt->bindParam(':rid', $_SESSION['user_id']);
        $stmt->execute();

        header("Location: " . BASE_URL . "recruiter/viewResult?id=" . $assignmentId);
    }
}
