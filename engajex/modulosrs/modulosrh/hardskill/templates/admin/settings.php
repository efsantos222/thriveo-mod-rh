<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Configurações do Sistema</h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card mt-4" style="max-width: 600px;">
        <form action="" method="POST">
            <div class="form-group">
                <label for="key">OpenAI API Key</label>
                <input type="text" name="openai_api_key" id="key"
                    value="<?= htmlspecialchars(($apiKey) ? $apiKey : '') ?>" placeholder="sk-..." required>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem">Chave usada para gerar os
                    testes via IA.</p>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Configuração</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>