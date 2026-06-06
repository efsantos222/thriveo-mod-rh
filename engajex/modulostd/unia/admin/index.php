<?php
require_once '../config/db.php';
require_once 'header.php';

try {
    $companiesCount = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
} catch (Exception $e) {
    $companiesCount = 0;
    $usersCount = 0;
    $coursesCount = 0;
    echo "<div style='color:red'>Erro ao carregar dados: " . $e->getMessage() . "</div>";
}
?>

<div class="header-flex"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Visão Geral</h1>
    <span style="color: var(--text-muted); font-size: 0.9rem;">Bem-vindo,
        <?php echo htmlspecialchars($_SESSION['name']); ?>
    </span>
</div>

<div class="grid">
    <div class="card">
        <h3>🏢 Empresas</h3>
        <p style="font-size: 2.5rem; font-weight: 700; color: white;">
            <?php echo $companiesCount; ?>
        </p>
        <p style="font-size: 0.85rem;">Total Cadastrado</p>
    </div>
    <div class="card">
        <h3>👥 Usuários</h3>
        <p style="font-size: 2.5rem; font-weight: 700; color: white;">
            <?php echo $usersCount; ?>
        </p>
        <p style="font-size: 0.85rem;">Total na Plataforma</p>
    </div>
    <div class="card">
        <h3>📚 Cursos</h3>
        <p style="font-size: 2.5rem; font-weight: 700; color: white;">
            <?php echo $coursesCount; ?>
        </p>
        <p style="font-size: 0.85rem;">Total Disponível</p>
    </div>
</div>

<div style="margin-top: 3rem;">
    <h2>Ações Rápidas</h2>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <a href="users.php?action=create" class="btn btn-primary">Novo Usuário</a>
        <a href="companies.php?action=create" class="btn btn-outline">Nova Empresa</a>
        <a href="settings.php" class="btn btn-outline">Configurar API</a>
    </div>
</div>

</body>

</html>