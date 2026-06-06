<div class="card">
    <h3>Bem-vindo, Recrutador!</h3>
    <p style="margin-top:1rem; color:var(--text-secondary)">Gerencie seus candidatos e acompanhe os resultados dos
        testes comportamentais.</p>
</div>

<div class="grid-3">
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">Meus Candidatos</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:var(--primary); margin:1rem 0">
            <?php echo $candidateCount; ?>
        </div>
        <a href="<?php echo BASE_URL; ?>recruiter/candidates" class="action-btn">Gerenciar</a>
    </div>
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">Testes Concluídos</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:var(--success); margin:1rem 0">
            <?php echo $completedTests; ?>
        </div>
    </div>
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">Testes Pendentes</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:#fbbf24; margin:1rem 0">
            <?php echo $pendingTests; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top:2rem">
    <h3>Testes Aguardando Análise (IA)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Candidato</th>
                <th>Teste</th>
                <th>Concluído em</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($testsPendingAI) && count($testsPendingAI) > 0):
                foreach ($testsPendingAI as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['candidate_name']); ?></td>
                        <td><span class="badge badge-completed"><?php echo $t['test_type']; ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($t['completed_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>recruiter/generateReport?id=<?php echo $t['id']; ?>"
                                class="btn-primary" style="padding:0.4rem 1rem; font-size:0.85rem; text-decoration:none">Gerar
                                Relatório IA</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4">Nenhum teste aguardando correção/análise.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>