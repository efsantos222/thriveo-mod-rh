<div class="card">
    <h3 style="margin-bottom:1rem">Novo Recrutador</h3>
    <form action="<?php echo BASE_URL; ?>admin/saveRecruiter" method="POST" class="grid-3" style="align-items:end">
        <div class="form-group" style="margin:0">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <button type="submit" class="btn-primary">Cadastrar</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Recrutadores Cadastrados</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Data Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($recruiters->rowCount() > 0):
                while ($rec = $recruiters->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rec['name']); ?></td>
                        <td><?php echo htmlspecialchars($rec['email']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($rec['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/deleteRecruiter?id=<?php echo $rec['id']; ?>"
                                class="action-btn" style="color:var(--danger); border-color:var(--danger)"
                                onclick="return confirm('Tem certeza que deseja excluir este recrutador? Seus candidatos serão desvinculados.')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="4">Nenhum recrutador encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>