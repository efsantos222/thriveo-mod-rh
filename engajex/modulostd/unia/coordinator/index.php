<?php
require_once '../config/db.php';
require_once 'header.php';

// Fetch stats
try {
    $myId = $_SESSION['user_id'];

    // Count courses managed by this coordinator or all courses? 
    // Usually coordinators manage their own courses, but requirements didn't specify strict ownership limits.
    // We'll show all courses for now as the prompt implies a general coordinator role.
    $coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

    // Count instructors
    $instructorsCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();

} catch (Exception $e) {
    $coursesCount = 0;
    $instructorsCount = 0;
}
?>

<div style="margin-bottom: 3rem; text-align: center;">
    <h1>Painel do Coordenador</h1>
    <p style="color: var(--text-muted);">Gerencie a estrutura acadêmica da universidade.</p>
</div>

<div class="grid">
    <a href="courses.php" class="card" style="text-decoration: none;">
        <h3>📚 Cursos</h3>
        <p style="font-size: 2.5rem; font-weight: 700; color: white;"><?php echo $coursesCount; ?></p>
        <p style="font-size: 0.85rem;">Cursos Cadastrados</p>
        <span class="btn btn-outline" style="margin-top: 1rem; font-size: 0.8rem;">Gerenciar</span>
    </a>

    <a href="instructors.php" class="card" style="text-decoration: none;">
        <h3>👨‍🏫 Instrutores</h3>
        <p style="font-size: 2.5rem; font-weight: 700; color: white;"><?php echo $instructorsCount; ?></p>
        <p style="font-size: 0.85rem;">Instrutores Ativos</p>
        <span class="btn btn-outline" style="margin-top: 1rem; font-size: 0.8rem;">Gerenciar</span>
    </a>

    <div class="card" style="opacity: 0.5;">
        <h3>📊 Relatórios</h3>
        <p>Visualização de desempenho geral (Em breve)</p>
    </div>
</div>

</body>

</html>