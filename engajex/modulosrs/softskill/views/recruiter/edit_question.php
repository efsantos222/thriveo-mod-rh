<div class="card" style="max-width: 600px;">
    <h3>Editar Questão</h3>
    <form action="<?php echo BASE_URL; ?>recruiter/updateQuestion" method="POST">
        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
        <input type="hidden" name="type" value="<?php echo $question['test_type']; ?>">
        
        <div class="form-group">
            <label class="form-label">Texto da Questão</label>
            <input type="text" name="text" class="form-input" required 
                   value="<?php echo htmlspecialchars($question['question_text']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Dimensão</label>
            <select name="dimension" class="form-input" required>
                <?php $currentType = $question['test_type']; ?>
                <?php if ($currentType === 'MBTI'): ?>
                    <option value="E/I" <?php echo $question['dimension'] === 'E/I' ? 'selected' : ''; ?>>E/I (Extroversão / Introversão)</option>
                    <option value="S/N" <?php echo $question['dimension'] === 'S/N' ? 'selected' : ''; ?>>S/N (Sensação / Intuição)</option>
                    <option value="T/F" <?php echo $question['dimension'] === 'T/F' ? 'selected' : ''; ?>>T/F (Pensamento / Sentimento)</option>
                    <option value="J/P" <?php echo $question['dimension'] === 'J/P' ? 'selected' : ''; ?>>J/P (Julgamento / Percepção)</option>
                <?php elseif ($currentType === 'Big Five'): ?>
                    <option value="O" <?php echo $question['dimension'] === 'O' ? 'selected' : ''; ?>>Abertura (O)</option>
                    <option value="C" <?php echo $question['dimension'] === 'C' ? 'selected' : ''; ?>>Conscienciosidade (C)</option>
                    <option value="E" <?php echo $question['dimension'] === 'E' ? 'selected' : ''; ?>>Extroversão (E)</option>
                    <option value="A" <?php echo $question['dimension'] === 'A' ? 'selected' : ''; ?>>Amabilidade (A)</option>
                    <option value="N" <?php echo $question['dimension'] === 'N' ? 'selected' : ''; ?>>Neuroticismo (N)</option>
                <?php elseif ($currentType === 'PDA'): ?>
                    <option value="Risco" <?php echo $question['dimension'] === 'Risco' ? 'selected' : ''; ?>>Eixo Risco</option>
                    <option value="Extrov" <?php echo $question['dimension'] === 'Extrov' ? 'selected' : ''; ?>>Extroversão</option>
                    <option value="Pacienc" <?php echo $question['dimension'] === 'Pacienc' ? 'selected' : ''; ?>>Paciência</option>
                    <option value="Norma" <?php echo $question['dimension'] === 'Norma' ? 'selected' : ''; ?>>Normas</option>
                <?php elseif ($currentType === 'Grit Scale'): ?>
                    <option value="Resil" <?php echo $question['dimension'] === 'Resil' ? 'selected' : ''; ?>>Resiliência</option>
                    <option value="Persev" <?php echo $question['dimension'] === 'Persev' ? 'selected' : ''; ?>>Perseverança</option>
                    <option value="Garra" <?php echo $question['dimension'] === 'Garra' ? 'selected' : ''; ?>>Garra</option>
                <?php else: ?>
                    <option value="D" <?php echo $question['dimension'] === 'D' ? 'selected' : ''; ?>>D (Dominância)</option>
                    <option value="I" <?php echo $question['dimension'] === 'I' ? 'selected' : ''; ?>>I (Influência)</option>
                    <option value="S" <?php echo $question['dimension'] === 'S' ? 'selected' : ''; ?>>S (Estabilidade)</option>
                    <option value="C" <?php echo $question['dimension'] === 'C' ? 'selected' : ''; ?>>C (Conformidade)</option>
                <?php endif; ?>
            </select>
        </div>

        <div style="display:flex; gap:10px">
            <button type="submit" class="btn-primary">Atualizar Questão</button>
            <a href="<?php echo BASE_URL; ?>recruiter/questions?type=<?php echo $currentType; ?>" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>
