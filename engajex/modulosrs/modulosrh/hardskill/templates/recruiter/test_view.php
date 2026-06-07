<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Detalhes do Teste</h1>
<p class="mb-4 text-muted"><?= htmlspecialchars($test['title']) ?></p>

<div class="card">
    <h3>Descrição da Vaga/Contexto</h3>
    <p><?= nl2br(htmlspecialchars($test['job_description'])) ?></p>
</div>

<h3 class="mt-4 mb-2">Questões</h3>
<?php foreach ($questions as $index => $q): ?>
    <div class="card mb-3">
        <h4>Questão <?= $index + 1 ?> <span
                style="font-size: 0.8rem; font-weight: 400; color: var(--text-muted)">(<?= $q['type'] ?>)</span></h4>
        <p><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>

        <?php if ($q['options']): ?>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <?php foreach (json_decode($q['options']) as $opt): ?>
                    <li><?= htmlspecialchars($opt) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border-color);">
            <strong>Gabarito/Guia: </strong> <?= $q['correct_guide'] ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="mt-4">
    <a href="<?= APP_URL ?>/recruiter/tests" class="btn btn-outline">Voltar</a>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>