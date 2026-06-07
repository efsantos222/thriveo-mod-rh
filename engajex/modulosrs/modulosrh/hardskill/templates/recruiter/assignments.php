<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="flex-between mb-4">
    <h1>Avaliações e Atribuições</h1>
    <a href="<?= APP_URL ?>/recruiter/assign" class="btn btn-primary"><i class="fas fa-plus"></i> Atribuir Novo
        Teste</a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Candidato</th>
                <th>Teste Aplicado</th>
                <th>Data Atribuição</th>
                <th>Status</th>
                <th>Nota</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($a['candidate_name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($a['candidate_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($a['test_title']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['assigned_at'])) ?></td>
                    <td>
                        <?php if ($a['status'] === 'pending'): ?>
                            <span style="color: grey"><i class="fas fa-clock"></i> Pendente</span>
                        <?php elseif ($a['status'] === 'in_progress'): ?>
                            <span style="color: orange"><i class="fas fa-spinner"></i> Em Andamento</span>
                        <?php elseif ($a['status'] === 'completed'): ?>
                            <span style="color: blue"><i class="fas fa-check"></i> Aguardando Correção</span>
                        <?php elseif ($a['status'] === 'graded'): ?>
                            <span style="color: var(--success)"><i class="fas fa-star"></i> Corrigido</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $a['score'] !== null ? $a['score'] : '-' ?></td>
                    <td>
                        <?php if ($a['status'] === 'completed' || $a['status'] === 'graded'): ?>
                            <a href="<?= APP_URL ?>/recruiter/assignment/<?= $a['id'] ?>/review" class="btn btn-primary"
                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                <?= $a['status'] === 'graded' ? 'Ver/Editar Correção' : 'Corrigir Agora' ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Aguardando envio</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($assignments)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">Nenhuma avaliação encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>