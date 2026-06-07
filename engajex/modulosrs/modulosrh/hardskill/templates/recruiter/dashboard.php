<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Painel do Recrutador</h1>
<p class="mb-4 text-muted">Gerencie seus processos seletivos.</p>

<div class="grid grid-2 mb-4">
    <div class="card">
        <h3><i class="fas fa-file-alt"></i> Meus Testes</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--primary-color)"><?= $stats['my_tests'] ?></p>
        <div style="margin-top: 1rem">
            <a href="<?= APP_URL ?>/recruiter/tests" class="btn btn-outline" style="font-size: 0.9rem">Gerenciar
                Testes</a>
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-users"></i> Candidatos</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--secondary-color)"><?= $stats['my_candidates'] ?></p>
        <div style="margin-top: 1rem">
            <a href="<?= APP_URL ?>/recruiter/candidates" class="btn btn-outline" style="font-size: 0.9rem">Gerenciar
                Candidatos</a>
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-check-double"></i> Avaliações</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--warning)"><?= $stats['pending_reviews'] ?> <span
                style="font-size: 1rem; color: #777; font-weight: 400">pendentes</span></p>
        <div style="margin-top: 1rem">
            <a href="<?= APP_URL ?>/recruiter/assignments" class="btn btn-primary">Ver Avaliações</a>
        </div>
    </div>
</div>

<div class="card">
    <h3>Ações Rápidas</h3>
    <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
        <a href="<?= APP_URL ?>/recruiter/tests/create" class="btn btn-outline"><i class="fas fa-magic"></i> Gerar Novo
            Teste (IA)</a>
        <a href="<?= APP_URL ?>/recruiter/assign" class="btn btn-outline"><i class="fas fa-link"></i> Atribuir Teste</a>
        <a href="<?= APP_URL ?>/recruiter/candidates/create" class="btn btn-outline"><i class="fas fa-user-plus"></i>
            Novo Candidato</a>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>