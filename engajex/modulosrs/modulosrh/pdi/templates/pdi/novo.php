<?php
$pageTitle = "Novo PDI";
require_once TEMPLATES_PATH . '/base.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Criar Novo PDI</h2>
                <a href="?route=pdi" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
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
                    <form method="post" action="?route=pdi/save">
                        <div class="mb-3">
                            <label for="short_term_goals" class="form-label">Objetivos de Curto Prazo</label>
                            <textarea class="form-control" id="short_term_goals" name="short_term_goals" rows="3" required><?php echo htmlspecialchars($_POST['short_term_goals'] ?? ''); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 3-6 meses.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="medium_term_goals" class="form-label">Objetivos de Médio Prazo</label>
                            <textarea class="form-control" id="medium_term_goals" name="medium_term_goals" rows="3" required><?php echo htmlspecialchars($_POST['medium_term_goals'] ?? ''); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 6-12 meses.</div>
                        </div>

                        <div class="mb-3">
                            <label for="long_term_goals" class="form-label">Objetivos de Longo Prazo</label>
                            <textarea class="form-control" id="long_term_goals" name="long_term_goals" rows="3" required><?php echo htmlspecialchars($_POST['long_term_goals'] ?? ''); ?></textarea>
                            <div class="form-text">Descreva seus objetivos para os próximos 1-2 anos.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="competencies" class="form-label">Competências a Desenvolver</label>
                            <textarea class="form-control" id="competencies" name="competencies" rows="3"><?php echo htmlspecialchars($_POST['competencies'] ?? ''); ?></textarea>
                            <div class="form-text">Liste as competências que você pretende desenvolver.</div>
                        </div>

                        <div class="mb-3">
                            <label for="actions" class="form-label">Ações</label>
                            <textarea class="form-control" id="actions" name="actions" rows="3"><?php echo htmlspecialchars($_POST['actions'] ?? ''); ?></textarea>
                            <div class="form-text">Descreva as ações que você planeja tomar para atingir seus objetivos.</div>
                        </div>

                        <div class="mb-3">
                            <label for="indicators" class="form-label">Indicadores</label>
                            <textarea class="form-control" id="indicators" name="indicators" rows="3"><?php echo htmlspecialchars($_POST['indicators'] ?? ''); ?></textarea>
                            <div class="form-text">Como você vai medir o progresso dos seus objetivos?</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar PDI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>
