<?php
require_once '../config/db.php';
require_once '../includes/OpenAIHelper.php';
require_once 'header.php';

$result = '';
$prompt_input = '';
$type_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = $_POST['topic'];
    $type = $_POST['type'];
    $details = $_POST['details'];

    $ai = new OpenAIHelper($pdo);

    $prompt = "Atue como um especialista pedagógico. ";

    switch ($type) {
        case 'outline':
            $prompt .= "Crie um plano de curso detalhado (Ementa) sobre o tema: '$topic'. ";
            break;
        case 'content':
            $prompt .= "Escreva um conteúdo didático explicativo e engajador sobre: '$topic'. ";
            break;
        case 'quiz':
            $prompt .= "Crie 5 perguntas de múltipla escolha com gabarito comentado sobre: '$topic'. ";
            break;
        case 'case_study':
            $prompt .= "Desenvolva um Estudo de Caso prático para alunos resolverem sobre: '$topic'. ";
            break;
    }

    if (!empty($details)) {
        $prompt .= "Detalhes adicionais: $details";
    }

    $result = $ai->generate($prompt);

    // Convert newlines to breaks for display (simple markdown-ish)
    // In a real app we'd use a Markdown Parser
    $displayResult = nl2br(htmlspecialchars($result));
}
?>

<div class="container-narrow" style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h1>Assistente de Criação (IA)</h1>
        <p style="color: var(--text-muted);">Colabore com a IA para desenvolver materiais didáticos de alta qualidade.
        </p>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">

        <!-- Input Form -->
        <div class="card">
            <h3>Solicitação</h3>
            <form method="POST" action="ai_create.php">
                <div class="form-group">
                    <label>Tipo de Conteúdo</label>
                    <select name="type" class="form-control" required>
                        <option value="content">Conteúdo Didático (Texto)</option>
                        <option value="outline">Plano de Curso (Ementa)</option>
                        <option value="quiz">Avaliação / Quiz</option>
                        <option value="case_study">Estudo de Caso</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tema / Tópico</label>
                    <input type="text" name="topic" class="form-control" require
                        placeholder="Ex: Liderança Ágil, Python Básico..."
                        value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Detalhes / Instruções Adicionais</label>
                    <textarea name="details" class="form-control" rows="4"
                        placeholder="Ex: Focar em exemplos práticos, linguagem informal..."><?= htmlspecialchars($_POST['details'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="background: linear-gradient(135deg, #a855f7, #ec4899); border:none;">Gerar com IA ✨</button>
            </form>
        </div>

        <!-- Result -->
        <?php if ($result): ?>
            <div class="card" style="border-color: #a855f7;">
                <h3 style="color: #d8b4fe;">Resultado Gerado</h3>
                <div
                    style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 0.5rem; max-height: 500px; overflow-y: auto; white-space: pre-wrap; margin-bottom: 1rem; font-size: 0.9rem; line-height: 1.6;">
                    <?= $result ?>
                </div>
                <button class="btn btn-outline" onclick="copyToClipboard()">Copiar Texto</button>
            </div>
        <?php else: ?>
            <div class="card"
                style="opacity: 0.7; border-style: dashed; display: flex; align-items: center; justify-content: center; min-height: 300px;">
                <p style="text-align: center; color: var(--text-muted);">O resultado aparecerá aqui após a geração.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function copyToClipboard() {
        // Determine the text to copy. Since we want the raw text (Markdown), we use the PHP output directly if accessible, 
        // or we grab the textContent of the div.
        const text = `<?= str_replace('`', '\`', $result ?? '') ?>`;
        navigator.clipboard.writeText(text).then(() => {
            alert('Conteúdo copiado para a área de transferência!');
        });
    }
</script>

</body>

</html>