<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Avaliação de Candidato</h1>
<div class="card mb-4">
    <div class="flex-between">
        <div>
            <h3><?= htmlspecialchars($assignment['candidate_name']) ?></h3>
            <p class="text-muted"><?= $assignment['candidate_email'] ?></p>
        </div>
        <div>
            <span class="badge"
                style="background: var(--primary-color); padding: 0.5rem; border-radius: 4px;"><?= htmlspecialchars($assignment['title']) ?></span>
        </div>
    </div>
</div>

<div class="mb-4 text-right">
    <button type="button" id="btnAutoCorrect" class="btn btn-primary"><i class="fas fa-magic"></i> Corrigir
        Automaticamente com IA</button>
</div>

<form action="" method="POST">
    <input type="hidden" name="action" value="save">
    <?php foreach ($questions as $q): ?>
        <div class="card mb-4" style="border-left: 4px solid var(--primary-color)">
            <p class="text-muted mb-2">Questão (<?= $q['type'] ?>)</p>
            <p class="mb-2"><strong><?= nl2br(htmlspecialchars($q['question_text'])) ?></strong></p>

            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <p class="text-muted" style="font-size: 0.8rem;">Resposta do Candidato:</p>
                <p><?= nl2br(htmlspecialchars($q['candidate_answer'] ?? 'Não respondido')) ?></p>
            </div>

            <?php if ($q['correct_guide']): ?>
                <div style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 4px;">
                    <p class="text-muted" style="font-size: 0.8rem; color: var(--success) !important;">Guia de Correção /
                        Resposta Esperada:</p>
                    <p style="color: var(--success)"><?= nl2br(htmlspecialchars($q['correct_guide'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="card mb-4">
        <h3>Conclusão da Avaliação</h3>
        <div class="form-group">
            <label>Nota Final (0-100)</label>
            <input type="number" name="score" min="0" max="100" step="0.1" value="<?= $assignment['score'] ?>" required>
        </div>
        <div class="form-group">
            <label>Feedback para o Candidato</label>
            <textarea name="feedback" rows="4"
                required><?= htmlspecialchars($assignment['feedback'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Avaliação</button>
    </div>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script>
    document.getElementById('btnAutoCorrect').addEventListener('click', function () {
        const btn = this;
        const originalText = btn.innerHTML;

        if (!confirm('Deseja que a IA analise todas as respostas e sugira uma nota e feedback? Isso pode levar alguns segundos.')) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analisando...';

        // Coletar dados da página para enviar
        // Como a página já tem as perguntas e respostas renderizadas, ideal seria ter o ID do assignment
        // A rota atual é /recruiter/assignment/{id}/review. Vamos pegar o ID da URL ou injetar via PHP.

        const assignmentId = <?= $assignment['id'] ?>;

        fetch('<?= APP_URL ?>/recruiter/assignment/' + assignmentId + '/autocorrect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Resposta inválida do servidor:', text);
                    throw new Error('Servidor retornou uma resposta inválida (não-JSON). Veja o console.');
                }
            })
            .then(data => {
                if (data.error) {
                    alert('Erro na IA: ' + data.error);
                } else {
                    document.querySelector('input[name="score"]').value = data.score;
                    document.querySelector('textarea[name="feedback"]').value = data.feedback;
                    alert('Correção sugerida realizada!');
                }
            })
            .catch(err => {
                alert('Erro na requisição: ' + err.message);
                console.error(err);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });
</script>