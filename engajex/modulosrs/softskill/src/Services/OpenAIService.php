<?php
class OpenAIService
{
    private $apiKey;
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    private function getApiKey($assignmentId)
    {
        // 1. Get the Recruiter's Company ID for this assignment
        $stmt = $this->conn->prepare("
            SELECT u.company_id 
            FROM ss_test_assignments ta 
            JOIN ss_users u ON ta.recruiter_id = u.id 
            WHERE ta.id = ?");
        $stmt->execute([$assignmentId]);
        $companyId = $stmt->fetchColumn();

        if ($companyId) {
            // 2. Get the API Key from the 'companies' table
            $stmt = $this->conn->prepare("SELECT openai_api_key FROM companies WHERE id = ?");
            $stmt->execute([$companyId]);
            $key = $stmt->fetchColumn();
            if ($key) return $key;
        }

        // 3. Fallback to global setting if company key is missing (optional, but good for safety)
        $stmt = $this->conn->prepare("SELECT setting_value FROM ss_settings WHERE setting_key = 'openai_api_key' LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function generateReport($assignmentId)
    {
        $apiKey = $this->getApiKey($assignmentId);
        if (!$apiKey) return false;

        // Fetch Data - Now including is_inverse
        $stmt = $this->conn->prepare("
            SELECT q.question_text, q.dimension, q.is_inverse, a.answer_value 
            FROM ss_answers a 
            JOIN ss_questions q ON a.question_id = q.id 
            WHERE a.assignment_id = :aid
        ");
        $stmt->bindParam(':aid', $assignmentId);
        $stmt->execute();
        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($answers) == 0)
            return false;

        // Fetch Test Info
        $stmtInfo = $this->conn->prepare("SELECT test_type FROM ss_test_assignments WHERE id = :id");
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
            $prompt .= "Método de Análise: Utilize o método estatístico de z-score para determinar o posicionamento do candidato em relação ao desvio padrão das dimensões.
            Identifique o tipo MBTI (ex: ENTJ, ISFP) com base na pontuação predominante em cada par: E/I, S/N, T/F, J/P.
            Explique as características do arquétipo e como ele se comporta no ambiente corporativo.";
        } elseif ($type === 'DISC') {
            $prompt .= "Método de Análise: Utilize o método estatístico de z-score para calcular a intensidade de cada fator (D, I, S, C).
            Identifique o perfil predominante (Executor, Comunicador, Planejador, Analista).
            Forneça uma análise de pontos fortes, áreas de desenvolvimento e estilo de comunicação.";
        } elseif ($type === 'Big Five') {
            $prompt .= "Indique a pontuação predominante nas 5 dimensões: Abertura, Conscienciosidade, Extroversão, Amabilidade e Neuroticismo.";
        } elseif ($type === 'PDA') {
            $prompt .= "Analise as tendências naturais e adaptadas com foco em Eixo de Risco, Extroversão, Paciência e Normas.";
        } elseif ($type === 'Grit Scale') {
            $prompt .= "Indique o Nível de Resiliência/Garra do candidato (Escala de 1 a 5) e sua capacidade de manter esforço e interesse a longo prazo.";
        } else {
            $prompt .= "Indique o perfil resultante conforme a metodologia do teste.";
        }

        $prompt .= "
        1. Resumo do Perfil
        2. Principais Características (Pontos Fortes)
        3. Áreas de Desenvolvimento (Pontos de Atenção)
        4. Sugestões de Ambiente de Trabalho Ideal
        5. Conclusão
        
        Respostas do Candidato (Escala 1 a 5, onde 1=Discordo Totalmente e 5=Concordo Totalmente):\n";

        foreach ($answers as $a) {
            $invText = ($a['is_inverse']) ? " [ITEM INVERSO]" : "";
            $prompt .= "- Questão: " . $a['question_text'] . " [Dimensão: " . $a['dimension'] . "]" . $invText . " -> Resposta: " . $a['answer_value'] . "\n";
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
            'Authorization: Bearer ' . $apiKey
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

            // Save to DB - Ensure connection is still alive after long API call
            try {
                @$this->conn->query("SELECT 1");
            } catch (Exception $e) {
                $db = new Database();
                $this->conn = $db->getConnection();
            }

            $update = $this->conn->prepare("UPDATE ss_test_assignments SET ai_report = :report WHERE id = :id");
            $update->bindParam(':report', $reportContent);
            $update->bindParam(':id', $assignmentId);
            $update->execute();
            return true;
        }

        return false;
    }
}
