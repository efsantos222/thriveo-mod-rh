<?php

class RecruiterController
{
    public static function dashboard()
    {
        $user = current_user();
        $pdo = get_db();

        $stats = [
            'my_tests' => $pdo->query("SELECT COUNT(*) FROM tests WHERE recruiter_id = {$user['id']}")->fetchColumn(),
            'my_candidates' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_by = {$user['id']}")->fetchColumn(),
            'pending_reviews' => $pdo->query("
                SELECT COUNT(*) FROM test_assignments ta 
                JOIN tests t ON ta.test_id = t.id 
                WHERE t.recruiter_id = {$user['id']} AND ta.status = 'completed'
            ")->fetchColumn()
        ];

        view('recruiter/dashboard', ['stats' => $stats]);
    }

    public static function tests()
    {
        $user = current_user();
        $pdo = get_db();
        $tests = $pdo->query("SELECT * FROM tests WHERE recruiter_id = {$user['id']} ORDER BY created_at DESC")->fetchAll();
        view('recruiter/tests', ['tests' => $tests]);
    }

    public static function createTest()
    {
        view('recruiter/tests_create');
    }

    public static function generateTestAI()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['error' => 'Method not allowed'], 405);
        }

        $jobDescription = $_POST['job_description'] ?? '';
        $level = $_POST['level'] ?? 'Mid-Level';

        $pdo = get_db();
        $apiKey = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'")->fetchColumn();

        if (!$apiKey) {
            json_response(['error' => 'API Key não configurada pelo Administrador.'], 500);
        }

        // Prompt para IA
        $prompt = "Crie um teste técnico para a seguinte vaga: {$jobDescription}. Nível: {$level}.
        O teste deve conter 10 questões variadas de complexidade elevada (múltipla escolha, dissertativa, análise de cenário proposto).
        Responda ESTRITAMENTE em formato JSON válido com a seguinte estrutura:
        {
            \"title\": \"Título Sugerido do Teste\",
            \"questions\": [
                {
                    \"type\": \"multiple_choice\" (ou \"long_text\" ou \"scenario\"),
                    \"question_text\": \"Texto da pergunta\",
                    \"options\": [\"Opção A\", \"Opção B\"...] (apenas para multiple_choice, null caso contrário),
                    \"correct_guide\": \"Resposta correta ou guia de correção\"
                }
            ]
        }";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um especialista técnico em recrutamento. Gere apenas JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.5
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $apiKey
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            json_response(['error' => 'Erro na conexão com OpenAI: ' . curl_error($ch)], 500);
        }
        curl_close($ch);

        $response = json_decode($result, true);
        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            json_response(['error' => 'Resposta inválida da IA', 'raw' => $result], 500);
        }

        // Tentar limpar markdown code blocks se houver
        $content = preg_replace('/^```json/', '', $content);
        $content = preg_replace('/```$/', '', $content);
        $data = json_decode($content, true);

        if (!$data) {
            json_response(['error' => 'Erro ao decodificar JSON da IA', 'content' => $content], 500);
        }

        // Salvar no Banco
        $user = current_user();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO tests (recruiter_id, title, job_description) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $data['title'], $jobDescription]);
            $testId = $pdo->lastInsertId();

            $stmtQ = $pdo->prepare("INSERT INTO questions (test_id, type, question_text, options, correct_guide) VALUES (?, ?, ?, ?, ?)");

            foreach ($data['questions'] as $q) {
                $options = isset($q['options']) ? json_encode($q['options']) : null;
                $stmtQ->execute([$testId, $q['type'], $q['question_text'], $options, $q['correct_guide']]);
            }

            $pdo->commit();
            json_response(['success' => true, 'redirect' => '/recruiter/tests']);

        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(['error' => 'Erro ao salvar teste: ' . $e->getMessage()], 500);
        }
    }

    public static function manageCandidates()
    {
        $user = current_user();
        $pdo = get_db();
        $candidates = $pdo->query("SELECT * FROM users WHERE role = 'candidate' AND created_by = {$user['id']} ORDER BY created_at DESC")->fetchAll();
        view('recruiter/candidates', ['candidates' => $candidates]);
    }

    public static function listAssignments()
    {
        $user = current_user();
        $pdo = get_db();
        $assignments = $pdo->query("
            SELECT ta.*, u.name as candidate_name, u.email as candidate_email, t.title as test_title 
            FROM test_assignments ta
            JOIN users u ON ta.candidate_id = u.id
            JOIN tests t ON ta.test_id = t.id
            WHERE t.recruiter_id = {$user['id']}
            ORDER BY ta.assigned_at DESC
        ")->fetchAll();

        view('recruiter/assignments', ['assignments' => $assignments]);
    }

    public static function createCandidate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user = current_user();

            $pdo = get_db();
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_by) VALUES (?, ?, ?, 'candidate', ?)");
                $stmt->execute([$name, $email, $password, $user['id']]);
                redirect('/recruiter/candidates');
            } catch (PDOException $e) {
                // Tratar erro
                die($e->getMessage());
            }
        }
        view('recruiter/candidates_create');
    }

    public static function assignTest()
    {
        $user = current_user();
        $pdo = get_db();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testId = $_POST['test_id'];
            $candidateId = $_POST['candidate_id'];

            $stmt = $pdo->prepare("INSERT INTO test_assignments (test_id, candidate_id) VALUES (?, ?)");
            $stmt->execute([$testId, $candidateId]);
            redirect('/recruiter/dashboard'); // Ou listar atribuições
        }

        // View de atribuição (não implementada separada, pode ser modal no dashboard ou página própria.
        // Vou fazer uma página simples.
        $tests = $pdo->query("SELECT id, title FROM tests WHERE recruiter_id = {$user['id']}")->fetchAll();
        $candidates = $pdo->query("SELECT id, name FROM users WHERE role = 'candidate' AND created_by = {$user['id']}")->fetchAll();
        view('recruiter/assign', ['tests' => $tests, 'candidates' => $candidates]);
    }

    public static function reviewAssignment($id)
    {
        $user = current_user();
        $pdo = get_db();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $score = $_POST['score'];
            $feedback = $_POST['feedback'];

            $stmt = $pdo->prepare("UPDATE test_assignments SET score = ?, feedback = ?, status = 'graded' WHERE id = ?");
            $stmt->execute([$score, $feedback, $id]);

            redirect('/recruiter/dashboard');
        }

        $assignment = $pdo->prepare("
            SELECT ta.*, t.title, u.name as candidate_name, u.email as candidate_email
            FROM test_assignments ta
            JOIN tests t ON ta.test_id = t.id
            JOIN users u ON ta.candidate_id = u.id
            WHERE ta.id = ? AND t.recruiter_id = ?
        ");
        $assignment->execute([$id, $user['id']]);
        $assignment = $assignment->fetch();

        if (!$assignment)
            die("Avaliação não encontrada.");

        $questions = $pdo->prepare("
            SELECT q.*, a.candidate_answer 
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id AND a.assignment_id = ?
            WHERE q.test_id = ?
        ");
        $questions->execute([$id, $assignment['test_id']]);
        $questions = $questions->fetchAll();

        view('recruiter/review', ['assignment' => $assignment, 'questions' => $questions]);
    }

    public static function viewTest($id)
    {
        $user = current_user();
        $pdo = get_db();

        $test = $pdo->prepare("SELECT * FROM tests WHERE id = ? AND recruiter_id = ?");
        $test->execute([$id, $user['id']]);
        $test = $test->fetch();

        if (!$test)
            die("Teste não encontrado.");

        $questions = $pdo->prepare("SELECT * FROM questions WHERE test_id = ?");
        $questions->execute([$id]);
        $questions = $questions->fetchAll();

        view('recruiter/test_view', ['test' => $test, 'questions' => $questions]);
    }
    public static function autoCorrectTest($id)
    {
        // Configurações para processo longo
        set_time_limit(300); // 5 minutos
        ini_set('display_errors', 0); // Evitar sujeira no JSON

        // Verificar permissões
        $user = current_user();
        $pdo = get_db();

        // Buscar dados do teste e respostas
        $assignment = $pdo->prepare("
            SELECT ta.*, t.title, t.job_description, t.id as real_test_id
            FROM test_assignments ta
            JOIN tests t ON ta.test_id = t.id
            WHERE ta.id = ? AND t.recruiter_id = ?
        ");
        $assignment->execute([$id, $user['id']]);
        $assignment = $assignment->fetch();

        if (!$assignment) {
            json_response(['error' => 'Avaliação não encontrada'], 404);
        }

        $questions = $pdo->prepare("
            SELECT q.question_text, q.correct_guide, a.candidate_answer 
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id AND a.assignment_id = ?
            WHERE q.test_id = ?
        ");
        $questions->execute([$id, $assignment['real_test_id']]);
        $questions = $questions->fetchAll(PDO::FETCH_ASSOC);

        // Buscar API KEY
        $apiKey = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'")->fetchColumn();
        if (!$apiKey) {
            json_response(['error' => 'API Key não configurada.'], 500);
        }

        // Montar Prompt
        $promptData = [
            'vaga' => $assignment['job_description'],
            'respostas' => $questions
        ];

        $prompt = "Você é um assistente sênior de recrutamento. Avalie as respostas de um candidato para um teste técnico da vaga: {$assignment['job_description']}.
        
        Abaixo estão as questões, o guia de correção (se houver) e a resposta do candidato.
        
        Analise a qualidade técnica, profundidade e correção das respostas.
        
        Retorne ESTRITAMENTE um JSON com o seguinte formato:
        {
            \"score\": (nota de 0 a 100, float, use ponto como decimal),
            \"feedback\": \"Feedback detalhado e construtivo para o candidato, destacando pontos fortes e o que melhorar.\"
        }
        
        Dados: " . json_encode($promptData);

        // Chamar OpenAI
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um avaliador técnico preciso e justo. Responda apenas JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.3
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $apiKey
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            json_response(['error' => 'Erro OpenAI: ' . curl_error($ch)], 500);
        }
        curl_close($ch);

        $response = json_decode($result, true);
        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            json_response(['error' => 'Resposta inválida da IA', 'raw' => $result], 500);
        }

        // Limpar Markdown
        $content = preg_replace('/^```json/', '', $content);
        $content = preg_replace('/```$/', '', $content);
        $data = json_decode($content, true);

        if (!$data) {
            json_response(['error' => 'Erro ao processar JSON da IA', 'content' => $content], 500);
        }

        json_response($data);
    }
}
