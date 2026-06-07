<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Dashboard do Administrador</h1>
<p class="mb-4 text-muted">Visão geral do sistema.</p>

<div class="grid grid-2">
    <div class="card">
        <h3><i class="fas fa-user-tie"></i> Recrutadores</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--primary-color)"><?= $stats['recruiters'] ?></p>
        <a href="<?= APP_URL ?>/admin/recruiters" class="btn btn-outline" style="margin-top: 1rem">Gerenciar
            Recrutadores</a>
    </div>

    <div class="card">
        <h3><i class="fas fa-users"></i> Candidatos</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--secondary-color)"><?= $stats['candidates'] ?></p>
    </div>

    <div class="card">
        <h3><i class="fas fa-file-alt"></i> Testes Gerados</h3>
        <p style="font-size: 2rem; font-weight: 700; color: var(--success)"><?= $stats['tests'] ?></p>
    </div>

    <div class="card">
        <h3><i class="fas fa-cog"></i> Configurações</h3>
        <p class="text-muted">Chaves de API e ajustes.</p>
        <a href="<?= APP_URL ?>/admin/settings" class="btn btn-outline" style="margin-top: 1rem">Acessar
            Configurações</a>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>