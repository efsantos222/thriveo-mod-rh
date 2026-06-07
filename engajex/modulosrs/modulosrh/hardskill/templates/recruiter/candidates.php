<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="flex-between mb-4">
    <h1>Meus Candidatos</h1>
    <a href="<?= APP_URL ?>/recruiter/candidates/create" class="btn btn-primary"><i class="fas fa-user-plus"></i> Novo
        Candidato</a>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($candidates as $cand): ?>
                <tr>
                    <td><?= htmlspecialchars($cand['name']) ?></td>
                    <td><?= htmlspecialchars($cand['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cand['created_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/recruiter/assign?candidate_id=<?= $cand['id'] ?>" class="btn btn-primary"
                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Atribuir Teste</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>