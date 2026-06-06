<div class="card">
    <div style="margin-bottom:2rem; text-align:center">
        <h2 style="margin-bottom:0.5rem">Seu Relatório de Perfil Comportamental</h2>
        <span class="badge badge-completed"
            style="font-size:1rem; padding:0.5rem 1rem"><?php echo $assignment['test_type']; ?></span>
    </div>

    <div
        style="background:rgba(255,255,255,0.03); padding:2rem; border-radius:12px; line-height:1.8; font-size:1.05rem; border:1px solid var(--border)">
        <?php echo $assignment['ai_report']; ?>
    </div>

    <div style="margin-top:2rem; text-align:center">
        <a href="<?php echo BASE_URL; ?>candidate/dashboard" class="btn-primary"
            style="display:inline-block; width:auto; text-decoration:none; padding:0.8rem 2rem">Voltar ao Painel</a>
    </div>
</div>