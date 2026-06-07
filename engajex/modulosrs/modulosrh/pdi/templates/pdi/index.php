<?php
$pageTitle = "Meu PDI";
require_once TEMPLATES_PATH . '/base.php';

// Buscar PDIs do usuário
$stmt = $pdo->prepare('
    SELECT * FROM pdis 
    WHERE user_id = ? 
    ORDER BY created_at DESC
');
$stmt->execute([$_SESSION['user']['id']]);
$pdis = $stmt->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Meus PDIs</h2>
        <a href="?route=pdi/novo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo PDI
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($pdis)): ?>
        <div class="alert alert-info">
            Você ainda não tem nenhum PDI. Clique em "Novo PDI" para criar o seu primeiro plano de desenvolvimento.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pdis as $pdi): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">PDI #<?php echo $pdi['id']; ?></h5>
                            <div class="card-text">
                                <p><strong>Objetivos de Curto Prazo:</strong><br>
                                <?php echo nl2br(htmlspecialchars(substr($pdi['short_term_goals'], 0, 100))) . '...'; ?></p>
                                
                                <p><strong>Status:</strong><br>
                                <span class="badge bg-<?php echo getStatusColor($pdi['status']); ?>">
                                    <?php echo getStatusLabel($pdi['status']); ?>
                                </span></p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    Criado em: <?php echo date('d/m/Y', strtotime($pdi['created_at'])); ?>
                                </small>
                                <a href="?route=pdi/view&id=<?php echo $pdi['id']; ?>" class="btn btn-sm btn-primary">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
