<?php
requireLogin();
$coachingManager = new \Coaching\CoachingManager($pdo);
$session = isset($sessionId) ? $coachingManager->getSessionDetails($sessionId) : null;
$isCoach = $_SESSION['user_role'] === 'coach';

$content = <<<HTML
<div class="row mb-4">
    <div class="col">
        <h2><?= $session ? 'Sessão de Coaching' : 'Nova Sessão' ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <?php if ($session): ?>
                <div class="mb-4">
                    <h4>Detalhes da Sessão</h4>
                    <dl class="row">
                        <dt class="col-sm-3">Coachee</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($session['user_name']) ?></dd>

                        <dt class="col-sm-3">Coach</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($session['coach_name']) ?></dd>

                        <dt class="col-sm-3">Data</dt>
                        <dd class="col-sm-9"><?= date('d/m/Y H:i', strtotime($session['session_date'])) ?></dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?= getStatusColor($session['status']) ?>">
                                <?= getStatusLabel($session['status']) ?>
                            </span>
                        </dd>
                    </dl>
                </div>

                <?php if ($isCoach || $session['status'] === 'completed'): ?>
                <form method="post" action="/coaching/update/<?= $session['id'] ?>">
                    <div class="mb-3">
                        <label for="summary" class="form-label">Resumo da Sessão</label>
                        <textarea id="summary" name="summary" class="form-control" rows="4"
                            <?= !$isCoach ? 'readonly' : '' ?>><?= $session['summary'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea id="feedback" name="feedback" class="form-control" rows="4"
                            <?= !$isCoach ? 'readonly' : '' ?>><?= $session['feedback'] ?? '' ?></textarea>
                    </div>

                    <?php if ($isCoach && $session['status'] !== 'completed'): ?>
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="action" value="save" class="btn btn-primary">Salvar</button>
                        <button type="submit" name="action" value="complete" class="btn btn-success">Finalizar Sessão</button>
                    </div>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <p class="text-center">O resumo e feedback estarão disponíveis após a conclusão da sessão.</p>
                <?php endif; ?>

                <?php else: ?>
                <form method="post" action="/coaching/schedule">
                    <div class="mb-3">
                        <label for="coach_id" class="form-label">Coach</label>
                        <select id="coach_id" name="coach_id" class="form-select" required>
                            <option value="">Selecione um coach...</option>
                            <?php foreach ($coachingManager->getAvailableCoaches() as $coach): ?>
                            <option value="<?= $coach['id'] ?>"><?= htmlspecialchars($coach['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="session_date" class="form-label">Data e Hora</label>
                        <input type="datetime-local" id="session_date" name="session_date" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/coaching" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Agendar Sessão</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php if ($session): ?>
        <div class="card">
            <div class="card-body">
                <h4>Próximas Ações</h4>
                <?php if ($isCoach): ?>
                <ul class="list-unstyled">
                    <?php if ($session['status'] === 'scheduled'): ?>
                    <li class="mb-2">
                        <a href="#" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            Cancelar Sessão
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                            Reagendar
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">As ações estão disponíveis para o coach responsável pela sessão.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($session && $isCoach): ?>
<!-- Modal de Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja cancelar esta sessão?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                <form method="post" action="/coaching/cancel/<?= $session['id'] ?>" class="d-inline">
                    <button type="submit" class="btn btn-danger">Sim, Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Reagendamento -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reagendar Sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="/coaching/reschedule/<?= $session['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_date" class="form-label">Nova Data e Hora</label>
                        <input type="datetime-local" id="new_date" name="new_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Reagendar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
function getStatusColor($status) {
    return [
        'scheduled' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger'
    ][$status] ?? 'secondary';
}

function getStatusLabel($status) {
    return [
        'scheduled' => 'Agendada',
        'completed' => 'Concluída',
        'cancelled' => 'Cancelada'
    ][$status] ?? 'Desconhecido';
}
?>
HTML;

require __DIR__ . '/../base.php';
