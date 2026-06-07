<div style="margin-bottom:1.5rem">
    <a href="?type=MBTI" class="action-btn"
        style="<?php echo $currentType == 'MBTI' ? 'background:var(--primary);border:none;color:white' : ''; ?>">MBTI</a>
    <a href="?type=DISC" class="action-btn"
        style="<?php echo $currentType == 'DISC' ? 'background:var(--primary);border:none;color:white' : ''; ?>">DISC</a>
    <a href="?type=Big Five" class="action-btn"
        style="<?php echo $currentType == 'Big Five' ? 'background:var(--primary);border:none;color:white' : ''; ?>">Big Five</a>
    <a href="?type=PDA" class="action-btn"
        style="<?php echo $currentType == 'PDA' ? 'background:var(--primary);border:none;color:white' : ''; ?>">PDA</a>
    <a href="?type=Grit Scale" class="action-btn"
        style="<?php echo $currentType == 'Grit Scale' ? 'background:var(--primary);border:none;color:white' : ''; ?>">Grit Scale</a>
</div>

<div class="card">
    <h3>Nova Questão (<?php echo $currentType; ?>)</h3>
    <form action="<?php echo BASE_URL; ?>recruiter/saveQuestion" method="POST" class="grid-3" style="align-items:end">
        <input type="hidden" name="type" value="<?php echo $currentType; ?>">
        <div class="form-group" style="margin:0; grid-column: span 2">
            <label class="form-label">Texto da Questão</label>
            <input type="text" name="text" class="form-input" required placeholder="Ex: Gosto de trabalhar em equipe">
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Dimensão</label>
            <select name="dimension" class="form-input" required>
                <?php if ($currentType === 'MBTI'): ?>
                    <option value="E/I">E/I (Extroversão / Introversão)</option>
                    <option value="S/N">S/N (Sensação / Intuição)</option>
                    <option value="T/F">T/F (Pensamento / Sentimento)</option>
                    <option value="J/P">J/P (Julgamento / Percepção)</option>
                <?php elseif ($currentType === 'Big Five'): ?>
                    <option value="O">Abertura (O)</option>
                    <option value="C">Conscienciosidade (C)</option>
                    <option value="E">Extroversão (E)</option>
                    <option value="A">Amabilidade (A)</option>
                    <option value="N">Neuroticismo (N)</option>
                <?php elseif ($currentType === 'PDA'): ?>
                    <option value="Risco">Eixo Risco</option>
                    <option value="Extrov">Extroversão</option>
                    <option value="Pacienc">Paciência</option>
                    <option value="Norma">Normas</option>
                <?php elseif ($currentType === 'Grit Scale'): ?>
                    <option value="Resil">Resiliência</option>
                    <option value="Persev">Perseverança</option>
                    <option value="Garra">Garra</option>
                <?php else: ?>
                    <option value="D">D (Dominância)</option>
                    <option value="I">I (Influência)</option>
                    <option value="S">S (Estabilidade)</option>
                    <option value="C">C (Conformidade)</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0">
            <button type="submit" class="btn-primary">Adicionar</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Questões Cadastradas</h3>
    <table class="table">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Texto</th>
                <th width="100">Dimensão</th>
                <th width="160">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($questions) > 0):
                foreach ($questions as $q): ?>
                    <tr>
                        <td><?php echo $q['id']; ?></td>
                        <td><?php echo htmlspecialchars($q['question_text']); ?></td>
                        <td><span class="badge"><?php echo htmlspecialchars($q['dimension']); ?></span></td>
                        <td style="display: flex; gap: 8px;">
                            <a href="<?php echo BASE_URL; ?>recruiter/editQuestion?id=<?php echo $q['id']; ?>" class="action-btn"
                                style="color:var(--primary); border-color:var(--primary); margin:0;">Editar</a>
                            <a href="<?php echo BASE_URL; ?>recruiter/deleteQuestion?id=<?php echo $q['id']; ?>" class="action-btn"
                                style="color:var(--danger); border-color:var(--danger); margin:0;"
                                onclick="return confirm('Excluir?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4">Nenhuma questão cadastrada para este teste.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
