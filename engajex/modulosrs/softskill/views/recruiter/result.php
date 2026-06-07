<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h3>Relatório de Análise: <?php echo $assignment['test_type']; ?></h3>
        <span>Candidato: <strong><?php echo htmlspecialchars($assignment['candidate_name']); ?></strong></span>
    </div>
    <hr style="margin:1rem 0; border:0; border-top:1px solid var(--border)">

    <?php if ($assignment['status'] === 'pending'): ?>
        <p>O candidato ainda não realizou este teste.</p>
    <?php elseif (empty($assignment['ai_report'])): ?>
        <p>Teste concluído pelo candidato. O relatório analítico ainda não foi gerado.</p>
        <a href="<?php echo BASE_URL; ?>recruiter/generateReport?id=<?php echo $assignment['id']; ?>" class="btn-primary"
            style="display:inline-block; margin-top:1rem; text-align:center; text-decoration:none">Gerar Relatório com
            IA</a>
    <?php else: ?>
        <div style="background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:8px; margin-bottom:1.5rem; line-height:1.6">
            <?php echo $assignment['ai_report']; ?>
        </div>

        <?php if (!$assignment['feedback_sent']): ?>
            <p style="margin-bottom:1rem; color:var(--text-secondary)">O candidato ainda não pode ver este resultado.</p>
            <a href="<?php echo BASE_URL; ?>recruiter/sendFeedback?id=<?php echo $assignment['id']; ?>" class="btn-primary"
                style="display:inline-block; text-decoration:none">Liberar Feedback para Candidato</a>
            <a href="<?php echo BASE_URL; ?>recruiter/generateReport?id=<?php echo $assignment['id']; ?>" class="action-btn"
                style="display:inline-block; margin-left:1rem; text-decoration:none"
                onclick="return confirm('Tem certeza? Isso irá gerar um novo relatório usando seus créditos da API e substituirá o atual.')">Refazer
                Análise (IA)</a>
        <?php else: ?>
            <div style="display:flex; align-items:center; gap:10px">
                <span class="badge badge-completed">Feedback Enviado</span>
                <span style="color:var(--text-secondary); font-size:rem">O candidato já pode visualizar o relatório.</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>