<?php

class AdminController
{
    public static function dashboard()
    {
        $pdo = get_db();

        $stats = [
            'recruiters' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn(),
            'candidates' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'")->fetchColumn(),
            'tests' => $pdo->query("SELECT COUNT(*) FROM tests")->fetchColumn()
        ];

        view('admin/dashboard', ['stats' => $stats]);
    }

    public static function manageRecruiters()
    {
        $pdo = get_db();
        $recruiters = $pdo->query("SELECT * FROM users WHERE role = 'recruiter' ORDER BY created_at DESC")->fetchAll();
        view('admin/recruiters', ['recruiters' => $recruiters]);
    }

    public static function createRecruiter()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $pdo = get_db();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'recruiter')");
                $stmt->execute([$name, $email, $password]);
                redirect('/admin/recruiters');
            } catch (PDOException $e) {
                echo "Erro ao criar: " . $e->getMessage();
            }
        }
    }

    public static function settings()
    {
        $pdo = get_db();

        try {
            // Lógica de salvamento
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $apiKey = $_POST['openai_api_key'];
                // Upsert
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$apiKey, $apiKey]);

                redirect('/admin/settings?saved=1');
            }

            // Lógica de leitura
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
            $stmt->execute();
            $apiKey = $stmt->fetchColumn();

            $success = isset($_GET['saved']) ? "Configurações salvas com sucesso!" : null;

            view('admin/settings', ['apiKey' => $apiKey, 'success' => $success]);

        } catch (Exception $e) {
            // Em caso de erro, exibir de forma legível
            echo "<div style='padding:20px; color:red; border:1px solid red; background:#ffeeee;'>";
            echo "<h3>Erro ao processar configurações:</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}
