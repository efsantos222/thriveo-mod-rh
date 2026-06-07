<?php require __DIR__ . '/../layout/header.php'; ?>

<h1><?= htmlspecialchars($test['title']) ?></h1>
<p class="text-muted mb-4">Responda as questões abaixo com atenção.</p>

<form action="<?= APP_URL ?>/candidate/test/<?= $assignment['id'] ?>/submit" method="POST">

    <?php foreach ($questions as $index => $q): ?>
        <div class="card mb-4">
            <h4 class="mb-4">Questão <?= $index + 1 ?></h4>
            <p style="margin-bottom: 1rem; font-size: 1.1rem;"><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>

            <?php if ($q['type'] === 'multiple_choice'): ?>
                <?php
                $options = json_decode($q['options'], true);
                if ($options):
                    foreach ($options as $opt):
                        ?>
                        <div style="margin-bottom: 0.5rem; display: flex; align-items: center;">
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>"
                                id="q_<?= $q['id'] ?>_<?= md5($opt) ?>" style="width: auto; margin-right: 10px;" required>
                            <label for="q_<?= $q['id'] ?>_<?= md5($opt) ?>"
                                style="margin-bottom: 0; cursor: pointer;"><?= htmlspecialchars($opt) ?></label>
                        </div>
                        <?php
                    endforeach;
                endif;
                ?>

            <?php else: ?>
                <textarea name="answers[<?= $q['id'] ?>]" rows="5" placeholder="Digite sua resposta aqui..."
                    required></textarea>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-lg"
            onclick="return confirm('Tem certeza que deseja finalizar o teste?')">Enviar Teste</button>
    </div>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>