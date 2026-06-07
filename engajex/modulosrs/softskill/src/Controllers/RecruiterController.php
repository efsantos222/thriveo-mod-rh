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
        $recruiterId = $_SESSION['ss_id'];

        // Count candidates
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ss_users WHERE recruiter_id = :rid");
        $stmt->bindParam(':rid', $recruiterId);
        $stmt->execute();
        $candidateCount = $stmt->fetchColumn();

        // Count tests
        $stmt = $conn->prepare("SELECT 
            SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
            FROM ss_test_assignments WHERE recruiter_id = :rid");
        $stmt->bindParam(':rid', $recruiterId);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch tests needing AI correction (Completed but no Report)
        $stmtPendingAI = $conn->prepare("
            SELECT ta.id, ta.test_type, ta.completed_at, u.name as candidate_name 
            FROM ss_test_assignments ta 
            JOIN ss_users u ON ta.candidate_id = u.id 
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
        $candidates = $userModel->getCandidatesByRecruiter($_SESSION['ss_id']);
        $this->render('candidates', ['candidates' => $candidates, 'pageTitle' => 'Meus Candidatos']);
    }

    public function importUsers()
    {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get company_id of the recruiter from main session
        $companyId = $_SESSION['company_id'] ?? 1;

        // Fetch users from the main 'users' table who are not in 'ss_users' as a candidate for THIS recruiter
        $stmt = $conn->prepare("
            SELECT u.id, u.name, u.email 
            FROM users u 
            WHERE u.company_id = :cid 
            AND u.role != 'admin'
            AND u.email NOT IN (SELECT email FROM ss_users)
        ");
        $stmt->bindParam(':cid', $companyId);
        $stmt->execute();
        $availableUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('import_users', ['availableUsers' => $availableUsers, 'pageTitle' => 'Importar Colaboradores']);
    }

    public function doImport()
    {
        if (isset($_GET['id'])) {
            $userId = $_GET['id'];
            $db = new Database();
            $conn = $db->getConnection();

            // Fetch user info from main table
            $stmt = $conn->prepare("SELECT name, email, password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($u) {
                // Check if already in ss_users
                $check = $conn->prepare("SELECT id FROM ss_users WHERE email = ?");
                $check->execute([$u['email']]);
                $existing = $check->fetch();
                if (!$existing) {
                    $ins = $conn->prepare("INSERT INTO ss_users (name, email, password, role, recruiter_id) VALUES (?, ?, ?, 'candidate', ?)");
                    $ins->execute([$u['name'], $u['email'], $u['password'], $_SESSION['ss_id']]);
                } else {
                    // User already exists (e.g. created by SSO bridge) — assign to this recruiter
                    $upd = $conn->prepare("UPDATE ss_users SET recruiter_id = ?, role = 'candidate' WHERE id = ?");
                    $upd->execute([$_SESSION['ss_id'], $existing['id']]);
                }
            }
            header("Location: " . BASE_URL . "recruiter/candidates");
        }
    }

    public function saveCandidate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = new Database();
            $conn = $db->getConnection();

            // Check if email already exists to avoid duplicates
            $check = $conn->prepare("SELECT id FROM ss_users WHERE email = ?");
            $check->execute([strtolower(trim($_POST['email']))]);
            $existing = $check->fetch();

            if ($existing) {
                // User already exists (e.g. via SSO) — assign to this recruiter
                $upd = $conn->prepare("UPDATE ss_users SET recruiter_id = ?, role = 'candidate', name = ? WHERE id = ?");
                $upd->execute([$_SESSION['ss_id'], htmlspecialchars(strip_tags($_POST['name'])), $existing['id']]);
                header("Location: " . BASE_URL . "recruiter/candidates");
            } else {
                $userModel = new User();
                $userModel->name = $_POST['name'];
                $userModel->email = strtolower(trim($_POST['email']));
                $userModel->password = $_POST['password'];
                $userModel->role = 'candidate';
                $userModel->recruiter_id = $_SESSION['ss_id'];

                if ($userModel->create()) {
                    header("Location: " . BASE_URL . "recruiter/candidates");
                } else {
                    die("Erro ao criar candidato");
                }
            }
        }
    }

    public function deleteCandidate()
    {
        if (isset($_GET['id'])) {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("DELETE FROM ss_users WHERE id = :id AND recruiter_id = :rid");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->bindParam(':rid', $_SESSION['ss_id']);
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
        $stmt = $conn->prepare("SELECT * FROM ss_users WHERE id = :id AND recruiter_id = :rid");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':rid', $_SESSION['ss_id']);
        $stmt->execute();
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidate)
            die("Candidato não encontrado ou acesso negado.");

        // Get Tests
        $stmtTests = $conn->prepare("SELECT * FROM ss_test_assignments WHERE candidate_id = :cid ORDER BY created_at DESC");
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
            $stmt = $conn->prepare("INSERT INTO ss_test_assignments (recruiter_id, candidate_id, test_type) VALUES (:rid, :cid, :type)");
            $stmt->bindParam(':rid', $_SESSION['ss_id']);
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

        $stmt = $conn->prepare("SELECT ta.*, u.name as candidate_name FROM ss_test_assignments ta JOIN ss_users u ON ta.candidate_id = u.id WHERE ta.id = :id AND ta.recruiter_id = :rid");
        $stmt->bindParam(':id', $assignmentId);
        $stmt->bindParam(':rid', $_SESSION['ss_id']);
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
        $stmt = $conn->prepare("UPDATE ss_test_assignments SET feedback_sent = 1 WHERE id = :id AND recruiter_id = :rid");
        $stmt->bindParam(':id', $assignmentId);
        $stmt->bindParam(':rid', $_SESSION['ss_id']);
        $stmt->execute();

        header("Location: " . BASE_URL . "recruiter/viewResult?id=" . $assignmentId);
    }

    /* Questions Management (Added for Recruiter) */
    public function questions()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $testType = $_GET['type'] ?? 'MBTI';

        $stmt = $conn->prepare("SELECT * FROM ss_questions WHERE test_type = :type");
        $stmt->bindParam(':type', $testType);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('questions', ['questions' => $questions, 'currentType' => $testType, 'pageTitle' => 'Gerenciar Questões']);
    }

    public function saveQuestion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("INSERT INTO ss_questions (test_type, question_text, dimension) VALUES (:type, :text, :dim)");
            $stmt->bindParam(':type', $_POST['type']);
            $stmt->bindParam(':text', $_POST['text']);
            $stmt->bindParam(':dim', $_POST['dimension']);
            $stmt->execute();
            header("Location: " . BASE_URL . "recruiter/questions?type=" . $_POST['type']);
        }
    }

    public function editQuestion()
    {
        if (isset($_GET['id'])) {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM ss_questions WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($question) {
                $this->render('edit_question', ['question' => $question, 'pageTitle' => 'Editar Questão']);
            } else {
                header("Location: " . BASE_URL . "recruiter/questions");
            }
        }
    }

    public function updateQuestion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("UPDATE ss_questions SET question_text = :text, dimension = :dim WHERE id = :id");
            $stmt->bindParam(':text', $_POST['text']);
            $stmt->bindParam(':dim', $_POST['dimension']);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();

            header("Location: " . BASE_URL . "recruiter/questions?type=" . $_POST['type']);
        }
    }

    public function deleteQuestion()
    {
        if (isset($_GET['id'])) {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("DELETE FROM ss_questions WHERE id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            header("Location: " . BASE_URL . "recruiter/questions");
        }
    }
}
