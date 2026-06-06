<?php
$pageTitle = "Visualizar PDI";
require_once TEMPLATES_PATH . '/base.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID do PDI não fornecido.";
    header('Location: ?route=pdi');
    exit;
}

// Buscar o PDI
$stmt = $pdo->prepare('
    SELECT p.*, 
           u.name as user_name,
           m.name as manager_name
    FROM pdis p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN users m ON p.manager_id = m.id
    WHERE p.id = ? AND p.user_id = ?
');

$stmt->execute([$_GET['id'], $_SESSION['user']['id']]);
$pdi = $stmt->fetch();

// Verificar se o PDI existe e pertence ao usuário
if (!$pdi) {
    $_SESSION['error'] = "PDI não encontrado ou você não tem permissão para visualizá-lo.";
    header('Location: ?route=pdi');
    exit;
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>PDI #<?php echo $pdi['id']; ?></h2>
        <div>
            <?php if ($pdi['status'] !== 'approved'): ?>
            <a href="?route=pdi/edit&id=<?php echo $pdi['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <?php endif; ?>
            <a href="?route=pdi" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informações Gerais</h5>
                <span class="badge bg-<?php echo getStatusColor($pdi['status']); ?>">
                    <?php echo getStatusLabel($pdi['status']); ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Colaborador:</strong> <?php echo htmlspecialchars($pdi['user_name']); ?></p>
                    <p><strong>Gestor:</strong> <?php echo htmlspecialchars($pdi['manager_name']); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Criado em:</strong> <?php echo date('d/m/Y', strtotime($pdi['created_at'])); ?></p>
                    <p><strong>Última atualização:</strong> <?php echo date('d/m/Y', strtotime($pdi['updated_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Objetivos de Curto Prazo</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($pdi['short_term_goals'])); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Objetivos de Médio Prazo</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($pdi['medium_term_goals'])); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Objetivos de Longo Prazo</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($pdi['long_term_goals'])); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Competências</h5>
                </div>
                <div class="card-body">
                    <?php echo empty($pdi['competencies']) ? 
                        '<em class="text-muted">Nenhuma competência definida</em>' : 
                        nl2br(htmlspecialchars($pdi['competencies'])); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    <?php echo empty($pdi['actions']) ? 
                        '<em class="text-muted">Nenhuma ação definida</em>' : 
                        nl2br(htmlspecialchars($pdi['actions'])); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Indicadores</h5>
                </div>
                <div class="card-body">
                    <?php echo empty($pdi['indicators']) ? 
                        '<em class="text-muted">Nenhum indicador definido</em>' : 
                        nl2br(htmlspecialchars($pdi['indicators'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    $colors = [
        'draft' => 'warning',
        'pending' => 'info',
        'approved' => 'success',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

function getStatusLabel($status) {
    $labels = [
        'draft' => 'Rascunho',
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'in_progress' => 'Em Andamento',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado'
    ];
    return $labels[$status] ?? 'Desconhecido';
}
?>

<?php require_once TEMPLATES_PATH . '/footer.php'; ?>
