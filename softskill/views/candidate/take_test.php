<div class="card">
    <div style="margin-bottom:2rem">
        <h3><?php echo $assignment['test_type']; ?> - Avaliação Comportamental</h3>
        <p style="color:var(--text-secondary)">Por favor, responda com sinceridade o quanto você se identifica com cada
            afirmação. Não há respostas certas ou erradas.</p>
    </div>

    <form action="<?php echo BASE_URL; ?>candidate/submitTest" method="POST">
        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">

        <?php if (count($questions) > 0):
            foreach ($questions as $index => $q): ?>
                <div style="margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid var(--border)">
                    <p style="margin-bottom:1rem; font-weight:500; font-size:1.1rem">
                        <span
                            style="color:var(--primary); font-weight:bold; margin-right:0.5rem"><?php echo $index + 1; ?>.</span>
                        <?php echo htmlspecialchars($q['question_text']); ?>
                    </p>

                    <div
                        style="display:flex; justify-content:space-between; align-items:center; background:rgba(0,0,0,0.2); padding:1.5rem; border-radius:8px; flex-wrap:wrap; gap:10px">
                        <span
                            style="font-size:0.85rem; color:var(--text-secondary); width:80px; text-align:right">Discordo</span>
                        <div style="display:flex; gap:2rem; flex:1; justify-content:center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label
                                    style="cursor:pointer; display:flex; flex-direction:column; align-items:center; position:relative">
                                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $i; ?>" required
                                        style="margin-bottom:0.5rem; width:20px; height:20px; accent-color:var(--primary)">
                                    <span
                                        style="font-size:0.9rem; font-weight:600; color:var(--text-secondary)"><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <span style="font-size:0.85rem; color:var(--text-secondary); width:80px">Concordo</span>
                    </div>
                </div>
            <?php endforeach; else: ?>
            <p>Nenhuma questão cadastrada para este teste. Contate o administrador.</p>
        <?php endif; ?>

        <?php if (count($questions) > 0): ?>
            <div style="text-align:right; margin-top:2rem">
                <button type="submit" class="btn-primary" style="padding:1rem 3rem; width:auto; font-size:1.1rem"
                    onclick="return confirm('Confirma o envio das respostas? Não será possível alterar depois.')">Finalizar
                    Teste</button>
            </div>
        <?php endif; ?>
    </form>
</div>