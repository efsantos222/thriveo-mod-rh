<?php
$pageTitle = "Editar PDI";
require_once TEMPLATES_PATH . '/base.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID do PDI não fornecido.";
    header('Location: ?route=pdi');
    exit;
}

// Buscar o PDI
$stmt = $pdo->prepare('
    SELECT * FROM pdis 
    WHERE id = ? AND user_id = ? AND status != "approved"
');

$stmt->execute([$_GET['id'], $_SESSION['user']['id']]);
$pdi = $stmt->fetch();

// Verificar se o PDI existe, pertence ao usuário e não está aprovado
if (!$pdi) {
    $_SESSION['error'] = "PDI não encontrado, você não tem permissão para editá-lo, ou ele já está aprovado.";
    header('Location: ?route=pdi');
    exit;
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Editar PDI #<?php echo $pdi['id']; ?></h2>
                <div>
                    <a href="?route=pdi/view&id=<?php echo $pdi['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" action="?route=pdi/update">
                        <input type="hidden" name="id" value="<?php echo $pdi['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="short_term_goals" class="form-label">Objetivos de Curto Prazo</label>
                            <textarea class="form-control" id="short_term_goals" name="short_term_goals" rows="3" required><?php echo htmlspecialchars($pdi['short_term_goals']); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 3-6 meses.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="medium_term_goals" class="form-label">Objetivos de Médio Prazo</label>
                            <textarea class="form-control" id="medium_term_goals" name="medium_term_goals" rows="3" required><?php echo htmlspecialchars($pdi['medium_term_goals']); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 6-12 meses.</div>
                        </div>

                        <div class="mb-3">
                            <label for="long_term_goals" class="form-label">Objetivos de Longo Prazo</label>
                            <textarea class="form-control" id="long_term_goals" name="long_term_goals" rows="3" required><?php echo htmlspecialchars($pdi['long_term_goals']); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 1-2 anos.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="competencies" class="form-label">Competências a Desenvolver</label>
                            <textarea class="form-control" id="competencies" name="competencies" rows="3"><?php echo htmlspecialchars($pdi['competencies']); ?></textarea>
                            <div class="form-text">Liste as competências que você pretende desenvolver.</div>
                        </div>

                        <div class="mb-3">
                            <label for="actions" class="form-label">Ações</label>
                            <textarea class="form-control" id="actions" name="actions" rows="3"><?php echo htmlspecialchars($pdi['actions']); ?></textarea>
                            <div class="form-text">Descreva as ações que você planeja tomar para atingir seus objetivos.</div>
                        </div>

                        <div class="mb-3">
                            <label for="indicators" class="form-label">Indicadores</label>
                            <textarea class="form-control" id="indicators" name="indicators" rows="3"><?php echo htmlspecialchars($pdi['indicators']); ?></textarea>
                            <div class="form-text">Como você vai medir o progresso dos seus objetivos?</div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?php echo $pdi['status'] === 'draft' ? 'selected' : ''; ?>>Rascunho</option>
                                <option value="pending" <?php echo $pdi['status'] === 'pending' ? 'selected' : ''; ?>>Enviar para Aprovação</option>
                            </select>
                            <div class="form-text">Mantenha como rascunho para continuar editando ou envie para aprovação do gestor.</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>
