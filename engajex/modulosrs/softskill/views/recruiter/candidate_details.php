<div class="card">
    <h3>Dados do Candidato</h3>
    <div class="grid-2">
        <div><strong>Nome:</strong> <?php echo htmlspecialchars($candidate['name']); ?></div>
        <div><strong>Email:</strong> <?php echo htmlspecialchars($candidate['email']); ?></div>
    </div>
</div>

<div class="card">
    <h3>Atribuir Novo Teste</h3>
    <form action="<?php echo BASE_URL; ?>recruiter/assignTest" method="POST" class="grid-2" style="align-items:end">
        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
        <div class="form-group" style="margin:0">
            <label class="form-label">Tipo de Teste</label>
            <select name="test_type" class="form-input">
                <option value="MBTI">MBTI (Arquétipos)</option>
                <option value="DISC">DISC (Comportamental)</option>
                <option value="Big Five">Big Five (Pentafatorial)</option>
                <option value="PDA">PDA (Análise de Perfil)</option>
                <option value="Grit Scale">Grit Scale (Resiliência)</option>
            </select>
        </div>
        <div class="form-group" style="margin:0">
            <button type="submit" class="btn-primary">Atribuir Teste</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Testes Atribuídos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data Atribuição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tests) > 0):
                foreach ($tests as $t): ?>
                    <tr>
                        <td><?php echo $t['test_type']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $t['status']; ?>">
                                <?php echo $t['status'] == 'pending' ? 'Pendente' : 'Concluído'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>recruiter/viewResult?id=<?php echo $t['id']; ?>"
                                class="action-btn">Ver Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4">Nenhum teste atribuído.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>