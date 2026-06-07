<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="flex-between mb-4">
    <h1>Gerenciar Recrutadores</h1>
    <button onclick="document.getElementById('modal-create').style.display='flex'" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Recrutador
    </button>
</div>

<div class="card table-container">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Data de Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recruiters as $recruiter): ?>
                <tr>
                    <td><?= htmlspecialchars($recruiter['name']) ?></td>
                    <td><?= htmlspecialchars($recruiter['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($recruiter['created_at'])) ?></td>
                    <td>
                        <!-- Apenas placeholder para deleção -->
                        <button class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Excluir</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Criar -->
<div id="modal-create"
    style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; z-index: 1000;">
    <div class="card" style="width: 100%; max-width: 500px; position: relative;">
        <h2 class="mb-4">Novo Recrutador</h2>
        <form action="<?= APP_URL ?>/admin/recruiters/create" method="POST">
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>
            <div class="flex-between">
                <button type="button" onclick="document.getElementById('modal-create').style.display='none'"
                    class="btn btn-outline">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>