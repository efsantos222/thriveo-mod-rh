<div class="card">
    <h3>Meus Testes Atribuídos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Teste</th>
                <th>Status</th>
                <th>Data Atribuição</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tests) > 0):
                foreach ($tests as $t): ?>
                    <tr>
                        <td><strong><?php echo $t['test_type']; ?></strong></td>
                        <td>
                            <?php if ($t['status'] === 'pending'): ?>
                                <span class="badge badge-pending">Pendente</span>
                            <?php else: ?>
                                <span class="badge badge-completed">Concluído</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($t['created_at'])); ?></td>
                        <td>
                            <?php if ($t['status'] === 'pending'): ?>
                                <a href="<?php echo BASE_URL; ?>candidate/takeTest?id=<?php echo $t['id']; ?>" class="btn-primary"
                                    style="padding:0.4rem 1rem; font-size:0.85rem; text-decoration:none">Iniciar Avaliação</a>
                            <?php elseif ($t['feedback_sent']): ?>
                                <a href="<?php echo BASE_URL; ?>candidate/viewFeedback?id=<?php echo $t['id']; ?>"
                                    class="action-btn" style="color:var(--primary); border-color:var(--primary)">Ver Resultado</a>
                            <?php else: ?>
                                <span style="color:var(--text-secondary); font-size:0.85rem">Aguardando Avaliação</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4">Você não possui testes atribuídos no momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>