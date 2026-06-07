<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Meus Testes Atribuídos</h1>

<div class="card table-container mt-4">
    <table>
        <thead>
            <tr>
                <th>Teste</th>
                <th>Status</th>
                <th>Data Atribuição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td>
                        <?php if ($a['status'] == 'pending'): ?>
                            <span style="color: var(--warning)">Pendente</span>
                        <?php elseif ($a['status'] == 'in_progress'): ?>
                            <span style="color: var(--primary-color)">Em Progresso</span>
                        <?php elseif ($a['status'] == 'completed'): ?>
                            <span style="color: var(--success)">Concluído</span>
                        <?php else: ?>
                            <span style="color: var(--text-muted)">Avaliado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($a['assigned_at'])) ?></td>
                    <td>
                        <?php if ($a['status'] == 'pending' || $a['status'] == 'in_progress'): ?>
                            <a href="<?= APP_URL ?>/candidate/test/<?= $a['id'] ?>/take" class="btn btn-primary">Iniciar
                                Teste</a>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled>Ver Resultado (Em breve)</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>