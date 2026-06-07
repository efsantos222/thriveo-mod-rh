<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem">
        <h3>Importar Colaboradores do Engajex</h3>
        <a href="<?php echo BASE_URL; ?>recruiter/candidates" class="btn-secondary" style="text-decoration:none; padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 4px; color: var(--text-primary)">Voltar</a>
    </div>
    <p style="color:var(--text-secondary); margin-bottom: 2rem">Estes são usuários já cadastrados na sua empresa no sistema principal que ainda não estão no módulo de SoftSkill.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th width="150">Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($availableUsers) > 0): 
                foreach ($availableUsers as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>recruiter/doImport?id=<?php echo $u['id']; ?>" class="action-btn" style="background:var(--primary); color:white; border:none; padding: 0.4rem 0.8rem">Importar</a>
                        </td>
                    </tr>
                <?php endforeach; 
            else: ?>
                <tr>
                    <td colspan="3">Nenhum colaborador novo encontrado para importar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
