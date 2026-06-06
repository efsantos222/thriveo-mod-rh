<?php
require_once '../config/db.php';
require_once 'header.php';
?>

<div style="margin-bottom: 3rem; text-align: center;">
    <h1>Bem-vindo,
        <?php echo htmlspecialchars($_SESSION['name']); ?>
    </h1>
    <p style="color: var(--text-muted);">Painel do Instrutor</p>
</div>

<div class="grid">
    <a href="ai_create.php" class="card"
        style="text-decoration: none; border-color: #a855f7; background: rgba(168, 85, 247, 0.1);">
        <h3 style="color: #d8b4fe;">✨ Criar com IA</h3>
        <p>Utilize a Inteligência Artificial para gerar planos de aula, questionários e conteúdos didáticos rapidamente.
        </p>
    </a>

    <a href="courses.php" class="card" style="text-decoration: none;">
        <h3>📚 Gerenciar Cursos</h3>
        <p>Visualize os cursos onde você atua, adicione materiais e gerencie módulos.</p>
    </a>

    <a href="events.php" class="card" style="text-decoration: none;">
        <h3>📅 Agendar Evento</h3>
        <p>Crie novos treinamentos, aulas ao vivo ou sessões de mentoria.</p>
    </a>

    <a href="students.php" class="card" style="text-decoration: none;">
        <h3>🎓 Alunos</h3>
        <p>Acompanhe o progresso e avalie os alunos matriculados.</p>
    </a>
</div>

</body>

</html>