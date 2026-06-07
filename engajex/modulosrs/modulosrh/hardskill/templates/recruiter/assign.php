<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Atribuir Teste a Candidato</h1>
<div class="card mt-4" style="max-width: 600px;">
    <form action="<?= APP_URL ?>/recruiter/assign" method="POST">
        <div class="form-group">
            <label>Selecionar Candidato</label>
            <select name="candidate_id" required>
                <option value="">Escolha um candidato...</option>
                <?php foreach ($candidates as $cand): ?>
                    <option value="<?= $cand['id'] ?>" <?= (isset($_GET['candidate_id']) && $_GET['candidate_id'] == $cand['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cand['name']) ?> (<?= $cand['email'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Selecionar Teste</label>
            <select name="test_id" required>
                <option value="">Escolha um teste...</option>
                <?php foreach ($tests as $test): ?>
                    <option value="<?= $test['id'] ?>"><?= htmlspecialchars($test['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Confirmar Atribuição</button>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>