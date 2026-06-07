<?php
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}

// Determinar o caminho base para os links
$base_path = '';
if (strpos($_SERVER['REQUEST_URI'], '/mbti/') !== false) {
    $base_path = '../';
}

$test_type = $_SESSION['user_type'] === 'mbti' ? 'MBTI' : 'DISC';
?>
<div class="navigation-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Teste <?php echo $test_type; ?></h4>
        <div class="d-flex align-items-center">
            <span class="me-3">
                Olá, <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
            </span>
            <a href="<?php echo $base_path; ?>logout.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </div>
</div>
