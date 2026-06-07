<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

echo "<h2>Atualizando Banco de Dados para Construtor de Testes...</h2>";

try {
    // 1. Table for Test Templates
    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_test_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NULL, -- NULL for system defaults available to everyone
        title VARCHAR(255) NOT NULL,
        description TEXT,
        type ENUM('DISC', 'MBTI', 'CUSTOM') DEFAULT 'CUSTOM',
        questions_json LONGTEXT, -- Stores the questions structure
        ai_prompt TEXT, -- Custom instructions for AI analysis
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )");
    echo "<p>Tabela 'coach_test_templates' criada.</p>";

    // 2. Insert Defaults (DISC and MBTI) if not exist
    // Check if DISC exists
    $check = $pdo->query("SELECT id FROM coach_test_templates WHERE title = 'DISC Profile' AND company_id IS NULL");
    if (!$check->fetch()) {
        $discQuestions = [
            ['id' => 1, 'words' => ['Enérgico', 'Discreto', 'Encorajador', 'Considerado']],
            ['id' => 2, 'words' => ['Destemido', 'Amigável', 'Cuidadoso', 'Expressivo']],
            ['id' => 3, 'words' => ['Agradável', 'Preciso', 'Franco', 'Bem-Humorado']],
            ['id' => 4, 'words' => ['Ousado', 'Calmo', 'Animado', 'Lógico']],
            ['id' => 5, 'words' => ['Convincente', 'Equilibrado', 'Original', 'Pacífico']]
            // Simulating short version, user can edit later to add all 24
        ];

        $discPrompt = "Analise o resultado DISC (escolhas de Mais/Menos). Identifique o perfil predominante (D, I, S, C). Forneça Pontos Fortes, Pontos de Melhoria e Estilo de Comunicação.";

        $stmt = $pdo->prepare("INSERT INTO coach_test_templates (title, description, type, questions_json, ai_prompt) VALUES (?, ?, 'DISC', ?, ?)");
        $stmt->execute(['DISC Profile', 'Avaliação de perfil comportamental.', json_encode($discQuestions), $discPrompt]);
        echo "<p>Template padrão 'DISC Profile' inserido.</p>";
    }

    // Check if MBTI exists
    $check = $pdo->query("SELECT id FROM coach_test_templates WHERE title = 'MBTI Type' AND company_id IS NULL");
    if (!$check->fetch()) {
        $mbtiQuestions = [
            [
                'question' => "Em festas e eventos sociais, você geralmente:",
                'options' => [
                    ['text' => "Interage com muitas pessoas (E)", 'value' => "E"],
                    ['text' => "Interage com poucas pessoas (I)", 'value' => "I"]
                ]
            ],
            [
                'question' => "Ao tomar decisões, o que pesa mais?",
                'options' => [
                    ['text' => "Lógica e consistência (T)", 'value' => "T"],
                    ['text' => "Valores pessoais e harmonia (F)", 'value' => "F"]
                ]
            ]
            // Short version
        ];

        $mbtiPrompt = "Analise as respostas MBTI. Determine o tipo (ex: ESTJ, INFP). Descreva as características principais, ambiente de trabalho ideal e liderança.";

        $stmt = $pdo->prepare("INSERT INTO coach_test_templates (title, description, type, questions_json, ai_prompt) VALUES (?, ?, 'MBTI', ?, ?)");
        $stmt->execute(['MBTI Type', 'Avaliação de tipo de personalidade.', json_encode($mbtiQuestions), $mbtiPrompt]);
        echo "<p>Template padrão 'MBTI Type' inserido.</p>";
    }

    // 3. Update Assessments table to link to template_id
    // We add a column 'template_id'. Existing records might have NULL, which is fine (generic legacy).
    // We try/catch in case column exists.
    try {
        $pdo->exec("ALTER TABLE coach_assessments ADD COLUMN template_id INT NULL AFTER user_id");
        $pdo->exec("ALTER TABLE coach_assessments ADD CONSTRAINT fk_assessment_template FOREIGN KEY (template_id) REFERENCES coach_test_templates(id) ON DELETE SET NULL");
        echo "<p>Coluna 'template_id' adicionada em 'coach_assessments'.</p>";
    } catch (Exception $e) { /* Ignore if exists */
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro SQL: " . $e->getMessage() . "</p>";
}

echo "<h3>Configuração Concluída. <a href='modules/coach.php'>Voltar</a></h3>";
?>