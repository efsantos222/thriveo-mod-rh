<div class="card">
    <h3>Novo Candidato</h3>
    <form action="<?php echo BASE_URL; ?>recruiter/saveCandidate" method="POST" class="grid-3" style="align-items:end">
        <div class="form-group" style="margin:0">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Senha Provisória</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <button type="submit" class="btn-primary">Cadastrar</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Meus Candidatos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($candidates->rowCount() > 0):
                while ($c = $candidates->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>recruiter/viewCandidate?id=<?php echo $c['id']; ?>"
                                class="action-btn" style="color:var(--primary); border-color:var(--primary)">Gerenciar</a>
                            <a href="<?php echo BASE_URL; ?>recruiter/deleteCandidate?id=<?php echo $c['id']; ?>"
                                class="action-btn" style="color:var(--danger); border-color:var(--danger)"
                                onclick="return confirm('Excluir candidato?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="3">Nenhum candidato cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>