<?php
requireLogin();
$pdiManager = new \PDI\PDIManager($pdo);
$pdi = isset($pdiId) ? $pdiManager->getPDI($pdiId) : null;
$isEdit = $pdi !== null;

$content = <<<HTML
<div class="row mb-4">
    <div class="col">
        <h2><?= $isEdit ? 'Editar PDI' : 'Novo PDI' ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= $isEdit ? '/pdi/update/'.$pdiId : '/pdi/create' ?>">
                    <div class="mb-4">
                        <h4>Objetivos de Curto Prazo</h4>
                        <textarea name="short_term_goals" class="form-control" rows="4" required><?= $pdi['short_term_goals'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Defina objetivos alcançáveis em até 6 meses</small>
                    </div>

                    <div class="mb-4">
                        <h4>Objetivos de Médio Prazo</h4>
                        <textarea name="medium_term_goals" class="form-control" rows="4" required><?= $pdi['medium_term_goals'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Defina objetivos para 6-12 meses</small>
                    </div>

                    <div class="mb-4">
                        <h4>Objetivos de Longo Prazo</h4>
                        <textarea name="long_term_goals" class="form-control" rows="4" required><?= $pdi['long_term_goals'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Defina objetivos para mais de 12 meses</small>
                    </div>

                    <div class="mb-4">
                        <h4>Competências a Desenvolver</h4>
                        <textarea name="competencies" class="form-control" rows="4" required><?= $pdi['competencies'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Liste as competências que precisam ser desenvolvidas</small>
                    </div>

                    <div class="mb-4">
                        <h4>Ações</h4>
                        <textarea name="actions" class="form-control" rows="4" required><?= $pdi['actions'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Descreva as ações necessárias para atingir os objetivos</small>
                    </div>

                    <div class="mb-4">
                        <h4>Indicadores</h4>
                        <textarea name="indicators" class="form-control" rows="4" required><?= $pdi['indicators'] ?? '' ?></textarea>
                        <small class="form-text text-muted">Defina como será medido o progresso</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/pdi" class="btn btn-secondary">Cancelar</a>
                        <div>
                            <?php if ($isEdit && $pdi['status'] === 'draft'): ?>
                            <button type="submit" name="action" value="save" class="btn btn-primary">Salvar Rascunho</button>
                            <button type="submit" name="action" value="submit" class="btn btn-success">Enviar para Aprovação</button>
                            <?php else: ?>
                            <button type="submit" name="action" value="save" class="btn btn-primary">Salvar como Rascunho</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
HTML;

require __DIR__ . '/../base.php';
