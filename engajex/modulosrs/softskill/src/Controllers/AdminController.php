<?php
class AdminController
{

    private function render($view, $data = [])
    {
        extract($data);
        $contentView = "../views/admin/$view.php";
        $pageTitle = ucfirst($view);
        require_once '../views/layouts/layout.php';
    }

    public function dashboard()
    {
        // Retrieve some basic stats
        $userModel = new User();
        // Just dummy stats for now or logic to count
        $this->render('dashboard', ['pageTitle' => 'Admin Dashboard']);
    }

    /* Recruiters Management */
    public function recruiters()
    {
        $userModel = new User();
        $recruiters = $userModel->getRecruiters();
        $this->render('recruiters', ['recruiters' => $recruiters, 'pageTitle' => 'Gerenciar Recrutadores']);
    }

    public function saveRecruiter()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            $userModel->name = $_POST['name'];
            $userModel->email = $_POST['email'];
            $userModel->password = $_POST['password'];
            $userModel->role = 'recruiter';

            if ($userModel->create()) {
                header("Location: " . BASE_URL . "admin/recruiters");
            } else {
                // handle error
                die("Erro ao criar recrutador");
            }
        }
    }

    public function deleteRecruiter()
    {
        if (isset($_GET['id'])) {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("DELETE FROM ss_users WHERE id = :id AND role = 'recruiter'");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            header("Location: " . BASE_URL . "admin/recruiters");
        }
    }

    /* Settings (API Key) */
    public function settings()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT setting_value FROM ss_settings WHERE setting_key = 'openai_api_key' LIMIT 1");
        $stmt->execute();
        $key = $stmt->fetchColumn();

        $this->render('settings', ['apiKey' => $key, 'pageTitle' => 'Configurações API']);
    }

    public function saveSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $key = $_POST['api_key'];
            $db = new Database();
            $conn = $db->getConnection();

            // Upsert
            $stmt = $conn->prepare("REPLACE INTO ss_settings (setting_key, setting_value) VALUES ('openai_api_key', :key)");
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            header("Location: " . BASE_URL . "admin/settings");
        }
    }

    /* Questions Management */
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
            header("Location: " . BASE_URL . "admin/questions?type=" . $_POST['type']);
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
                header("Location: " . BASE_URL . "admin/questions");
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

            // Redirect back to the correct type list
            header("Location: " . BASE_URL . "admin/questions?type=" . $_POST['type']);
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
            header("Location: " . BASE_URL . "admin/questions"); // ideally keeps the type
        }
    }
}
