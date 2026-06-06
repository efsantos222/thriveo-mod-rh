<div class="card">
    <h3>Editar Questão (<?php echo $question['test_type']; ?>)</h3>
    <form action="<?php echo BASE_URL; ?>admin/updateQuestion" method="POST" class="grid-3" style="align-items:end">
        <input type="hidden" name="type" value="<?php echo $question['test_type']; ?>">
        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
        <div class="form-group" style="margin:0; grid-column: span 2">
            <label class="form-label">Texto da Questão</label>
            <input type="text" name="text" class="form-input" required
                value="<?php echo htmlspecialchars($question['question_text']); ?>">
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Dimensão</label>
            <?php if ($question['test_type'] === 'MBTI'): ?>
                <select name="dimension" class="form-input" required>
                    <option value="E/I" <?php echo $question['dimension'] == 'E/I' ? 'selected' : ''; ?>>E/I (Extroversão /
                        Introversão)</option>
                    <option value="S/N" <?php echo $question['dimension'] == 'S/N' ? 'selected' : ''; ?>>S/N (Sensação /
                        Intuição)</option>
                    <option value="T/F" <?php echo $question['dimension'] == 'T/F' ? 'selected' : ''; ?>>T/F (Pensamento /
                        Sentimento)</option>
                    <option value="J/P" <?php echo $question['dimension'] == 'J/P' ? 'selected' : ''; ?>>J/P (Julgamento /
                        Percepção)</option>
                </select>
            <?php else: ?>
                <select name="dimension" class="form-input" required>
                    <option value="D" <?php echo $question['dimension'] == 'D' ? 'selected' : ''; ?>>D (Dominância)</option>
                    <option value="I" <?php echo $question['dimension'] == 'I' ? 'selected' : ''; ?>>I (Influência)</option>
                    <option value="S" <?php echo $question['dimension'] == 'S' ? 'selected' : ''; ?>>S (Estabilidade)</option>
                    <option value="C" <?php echo $question['dimension'] == 'C' ? 'selected' : ''; ?>>C (Conformidade)</option>
                </select>
            <?php endif; ?>
        </div>
        <div class="form-group" style="margin:0">
            <button type="submit" class="btn-primary">Atualizar</button>
        </div>
    </form>
    <div style="margin-top:1rem">
        <a href="<?php echo BASE_URL; ?>admin/questions?type=<?php echo $question['test_type']; ?>"
            class="action-btn">Cancelar</a>
    </div>
</div>