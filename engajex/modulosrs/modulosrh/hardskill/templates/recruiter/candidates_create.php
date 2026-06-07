<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Cadastrar Novo Candidato</h1>
<div class="card mt-4" style="max-width: 600px;">
    <form action="<?= APP_URL ?>/recruiter/candidates/create" method="POST">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Senha Provisória</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Candidato</button>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>