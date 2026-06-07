<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="flex-between mb-4">
    <h1>Meus Testes</h1>
    <a href="<?= APP_URL ?>/recruiter/tests/create" class="btn btn-primary"><i class="fas fa-magic"></i> Gerar Novo com
        IA</a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Vaga (Descrição)</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
                <tr>
                    <td><?= htmlspecialchars($test['title']) ?></td>
                    <td><small><?= substr(htmlspecialchars($test['job_description']), 0, 50) ?>...</small></td>
                    <td><?= date('d/m/Y', strtotime($test['created_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/recruiter/test/<?= $test['id'] ?>/view" class="btn btn-outline"
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Ver
                            Detalhes</a>
                        <!-- TODO: Deletar testes -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>