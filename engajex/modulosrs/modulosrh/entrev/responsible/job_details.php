<?php
$pageTitle = 'Detalhes da Vaga';
require_once '../config.php';
require_once '../includes/PdfToText.php';
require_once 'header.php';

$company_id = $_SESSION['company_id'];
$job_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Fetch Candidates (All, to show status)
$candidatesStmt = $pdo->prepare("SELECT id, name, linkedin_pdf FROM candidates WHERE company_id = ? ORDER BY name ASC");
$candidatesStmt->execute([$company_id]);
$allCandidates = $candidatesStmt->fetchAll();

// Verify Job ownership
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ?");
$stmt->execute([$job_id, $company_id]);
$job = $stmt->fetch();

if (!$job) {
    echo "Vaga não encontrada.";
    exit;
}

// Handle AI Script Generation
$message = '';
$generated_script = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Get API Key
    $apiKeyStmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
    $apiKey = $apiKeyStmt->fetchColumn();

    if (!$apiKey) {
        $message = "Chave da API OpenAI não configurada pelo Admin.";
    } else {
        $data = [
            "model" => "gpt-4o",
            "temperature" => 0.7
        ];

        if ($_POST['action'] === 'generate_ai') {
             $prompt = "Você é um assistente especialista em RH. Crie um roteiro de entrevista estruturado para o cargo de '{$job['title']}'.\n\nDescrição da Vaga: {$job['description']}\n\nEspecificações/Requisitos: {$job['specifications']}\n\nO roteiro deve conter:\n1. Introdução\n2. Perguntas sobre histórico profissional\n3. Perguntas técnicas baseadas nas especificações\n4. Perguntas comportamentais\n5. Encerramento.\n\nFormate a resposta em HTML limpo (apenas tags <p>, <ul>, <li>, <strong>, <h3>, etc), sem markdown ticks.";
             $data['messages'] = [
                ["role" => "system", "content" => "You are a helpful HR assistant."],
                ["role" => "user", "content" => $prompt]
            ];
        } elseif ($_POST['action'] === 'generate_analysis') {
            $candidate_id = $_POST['candidate_id'];
            $stmt = $pdo->prepare("SELECT name, linkedin_pdf FROM candidates WHERE id = ? AND company_id = ?");
            $stmt->execute([$candidate_id, $company_id]);
            $cand = $stmt->fetch();

            if ($cand && $cand['linkedin_pdf']) {
                $pdfPath = '../uploads/' . $cand['linkedin_pdf'];
                if (file_exists($pdfPath)) {
                    $pdfText = PdfToText::parse($pdfPath);
                    
                    if (strlen($pdfText) < 50) {
                         $message = "Não foi possível extrair texto suficiente do PDF para análise. Verifique se o PDF contém texto selecionável.";
                    } else {
                        $prompt = "Você é um especialista em Recrutamento e Seleção. Analise o perfil do candidato abaixo em relação à vaga.\n\n";
                        $prompt .= "VAGA: {$job['title']}\nDESCRIÇÃO: {$job['description']}\nREQUISITOS: {$job['specifications']}\n\n";
                        $prompt .= "PERFIL DO CANDIDATO (Extraído do PDF): {$cand['name']}\n{$pdfText}\n\n";
                        $prompt .= "TAREFA:\n1. Identifique as principais aderências do perfil com a vaga.\n2. Identifique LACUNAS (soft skills ou hard skills) ou pontos que precisam ser confirmados.\n3. Crie um ROTEIRO DE PERGUNTAS focado especificamente em esclarecer essas lacunas e confirmar a experiência citada.\n\n";
                        $prompt .= "Formate a resposta em HTML limpo (tags <h3>, <strong>, <ul>, <li>, <p>). Inicie com um título <h3>Análise do Candidato: {$cand['name']}</h3>.";

                        $data['messages'] = [
                            ["role" => "system", "content" => "You are an expert HR recruiter assistant."],
                            ["role" => "user", "content" => $prompt]
                        ];
                    }
                } else {
                    $message = "Arquivo PDF não encontrado no servidor.";
                }
            } else {
                $message = "Candidato inválido ou sem PDF.";
            }
        }

        if (isset($data['messages']) && !$message) {
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            
            if (isset($result['choices'][0]['message']['content'])) {
                $generated_script = $result['choices'][0]['message']['content'];
                // Save valid script
                $save = $pdo->prepare("INSERT INTO interview_scripts (job_id, content) VALUES (?, ?)");
                $save->execute([$job_id, $generated_script]);
                $message = "Análise gerada com sucesso!";
            } else {
                $message = "Erro na API OpenAI: " . ($result['error']['message'] ?? 'Desconhecido');
            }
        }
    }
}

// Fetch existing scripts
$scripts = $pdo->prepare("SELECT * FROM interview_scripts WHERE job_id = ? ORDER BY created_at DESC");
$scripts->execute([$job_id]);
$existingScripts = $scripts->fetchAll();

?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2>
                <?= htmlspecialchars($job['title']) ?>
            </h2>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Criado em
                <?= date('d/m/Y', strtotime($job['created_at'])) ?>
            </p>
        </div>
        <a href="jobs.php" class="btn btn-primary"
            style="background: transparent; border: 1px solid var(--primary-color);">Voltar</a>
    </div>

    <div style="margin-top: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <h4 style="color: var(--secondary-color); margin-bottom: 0.5rem;">Descrição</h4>
            <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px;">
                <?= nl2br(htmlspecialchars($job['description'])) ?>
            </div>
        </div>
        <div>
            <h4 style="color: var(--secondary-color); margin-bottom: 0.5rem;">Especificações</h4>
            <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px;">
                <?= nl2br(htmlspecialchars($job['specifications'])) ?>
            </div>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div
        style="background: rgba(99, 102, 241, 0.2); color: #c7d2fe; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3>Roteiros de Entrevista (IA)</h3>
    </div>

    <!-- Generate Generic Script -->
    <div
        style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--glass-border);">
        <h4 style="margin-bottom: 1rem;">Roteiro Padrão</h4>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <p style="color: var(--text-muted); font-size: 0.9rem;">Gera um roteiro base para este cargo.</p>
            <form method="POST">
                <input type="hidden" name="action" value="generate_ai">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                    <i class="ph ph-sparkle"></i> Gerar Padrão
                </button>
            </form>
        </div>
    </div>

    <!-- Generate Candidate Specific Script -->
    <div
        style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--glass-border);">
        <h4 style="margin-bottom: 1rem;">Análise de Candidato & Roteiro Personalizado</h4>
        <form method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="action" value="generate_analysis">
            <div style="flex: 1;">
                <label class="form-label">Selecione o Candidato (apenas com PDF)</label>
                <select name="candidate_id" class="form-control" required style="background: rgba(15, 23, 42, 0.9);">
                    <option value="">Selecione...</option>
                    <?php foreach ($allCandidates as $cand): ?>
                        <?php $hasPdf = !empty($cand['linkedin_pdf']); ?>
                        <option value="<?= $cand['id'] ?>" <?= !$hasPdf ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($cand['name']) ?>     <?= !$hasPdf ? '(Sem PDF)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"
                style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="ph ph-user-focus"></i> Analisar & Gerar
            </button>
        </form>
        <?php if (empty($allCandidates)): ?>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.5rem;">Nenhum candidato cadastrado.</p>
        <?php endif; ?>
    </div>

    <?php if (count($existingScripts) > 0): ?>
        <?php foreach ($existingScripts as $index => $script): ?>
            <div style="border: 1px solid var(--glass-border); border-radius: 8px; margin-bottom: 1rem; overflow: hidden;">
                <div style="background: rgba(255,255,255,0.05); padding: 1rem; cursor: pointer;"
                    onclick="document.getElementById('script-<?= $script['id'] ?>').style.display = document.getElementById('script-<?= $script['id'] ?>').style.display === 'none' ? 'block' : 'none'">
                    <strong>Roteiro #
                        <?= count($existingScripts) - $index ?>
                    </strong> - Gerado em
                    <?= date('d/m/Y H:i', strtotime($script['created_at'])) ?>
                    <span style="float: right;">Changes &#9660;</span>
                </div>
                <div id="script-<?= $script['id'] ?>"
                    style="padding: 1.5rem; display: <?= $index === 0 ? 'block' : 'none' ?>; background: rgba(0,0,0,0.2);">
                    <?= $script['content'] // Content is typically safe from AI but should be treated cautiously if user input was reflected. However, for this demo we assume AI output is safe HTML. ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: var(--text-muted);">Nenhum roteiro gerado ainda.</p>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>