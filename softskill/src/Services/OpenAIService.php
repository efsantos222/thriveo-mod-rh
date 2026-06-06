<?php
class OpenAIService
{
    private $apiKey;
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();

        $stmt = $this->conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key' LIMIT 1");
        $stmt->execute();
        $this->apiKey = $stmt->fetchColumn();
    }

    public function generateReport($assignmentId)
    {
        if (!$this->apiKey)
            return false;

        // Fetch Data
        $stmt = $this->conn->prepare("
            SELECT q.question_text, q.dimension, a.answer_value 
            FROM answers a 
            JOIN questions q ON a.question_id = q.id 
            WHERE a.assignment_id = :aid
        ");
        $stmt->bindParam(':aid', $assignmentId);
        $stmt->execute();
        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($answers) == 0)
            return false;

        // Fetch Test Info
        $stmtInfo = $this->conn->prepare("SELECT test_type FROM test_assignments WHERE id = :id");
        $stmtInfo->bindParam(':id', $assignmentId);
        $stmtInfo->execute();
        $type = $stmtInfo->fetchColumn();

        // Construct Prompt
        // Construct Prompt
        $prompt = "Aja como um especialista sênior em Recursos Humanos e Psicologia Organizacional. 
        Analise as respostas abaixo referentes a um teste comportamental " . $type . ".
        Gere um relatório profissional, detalhado e construtivo. O relatório deve ser dirigido ao recrutador.
        
        IMPORTANTE: Com base nas respostas e nas dimensões indicadas (Ex: E/I, S/N, T/F, J/P para MBTI), CALCULE e DETERMINE o perfil resultante.
        
        Estrutura obrigatória do relatório:
        0. Classificação do Perfil: ";

        if ($type === 'MBTI') {
            $prompt .= "Indique claramente o código de 4 letras (Ex: ESTJ, INFP, etc.) e o nome do arquétipo associado (Ex: O Executivo, O Lógico).";
        } else {
            $prompt .= "Indique a letra predominante (D, I, S, C) e o estilo comportamental principal.";
        }

        $prompt .= "
        1. Resumo do Perfil
        2. Principais Características (Pontos Fortes)
        3. Áreas de Desenvolvimento (Pontos de Atenção)
        4. Sugestões de Ambiente de Trabalho Ideal
        5. Conclusão
        
        Respostas do Candidato (Escala 1 a 5, onde 1=Discordo Totalmente e 5=Concordo Totalmente):\n";

        foreach ($answers as $a) {
            $prompt .= "- Questão: " . $a['question_text'] . " [Dimensão: " . $a['dimension'] . "] -> Resposta: " . $a['answer_value'] . "\n";
        }

        $prompt .= "\nFormate a saída utilizando tags HTML simples para boa leitura (<h3>, <p>, <ul>, <li>, <strong>). NÃO use blocos de código markdown (```html), use HTML puro diretamente.";

        // API Call
        $data = [
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um avaliador de testes comportamentais sênior. Responda apenas com o HTML do relatório, sem marcação de markdown.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.5
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Log error
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            $reportContent = $result['choices'][0]['message']['content'];

            // Clean Markdown code blocks if present
            $reportContent = str_replace("```html", "", $reportContent);
            $reportContent = str_replace("```", "", $reportContent);

            // Save to DB
            $update = $this->conn->prepare("UPDATE test_assignments SET ai_report = :report WHERE id = :id");
            $update->bindParam(':report', $reportContent);
            $update->bindParam(':id', $assignmentId);
            $update->execute();
            return true;
        }

        return false;
    }
}
