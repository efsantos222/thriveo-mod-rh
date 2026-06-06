<div class="card">
    <h3>Bem-vindo, Administrador!</h3>
    <p style="margin-top:1rem; color:var(--text-secondary)">Utilize o menu lateral para gerenciar recrutadores,
        configurações da API e o banco de questões.</p>
</div>

<div class="grid-3">
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">Recrutadores</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:var(--primary); margin:1rem 0">
            <?php
            $db = new Database();
            $c = $db->getConnection();
            echo $c->query("SELECT COUNT(*) FROM users WHERE role='recruiter'")->fetchColumn();
            ?>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/recruiters" class="action-btn">Gerenciar</a>
    </div>
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">MBTI Questões</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:var(--primary); margin:1rem 0">
            <?php echo $c->query("SELECT COUNT(*) FROM questions WHERE test_type='MBTI'")->fetchColumn(); ?>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/questions?type=MBTI" class="action-btn">Gerenciar</a>
    </div>
    <div class="card" style="text-align:center">
        <h4 style="color:var(--text-secondary)">DISC Questões</h4>
        <div style="font-size:2.5rem; font-weight:bold; color:var(--primary); margin:1rem 0">
            <?php echo $c->query("SELECT COUNT(*) FROM questions WHERE test_type='DISC'")->fetchColumn(); ?>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/questions?type=DISC" class="action-btn">Gerenciar</a>
    </div>
</div>