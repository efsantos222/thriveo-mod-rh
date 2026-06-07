<div class="card">
    <h3>Configuração OpenAI</h3>
    <p style="color:var(--text-secondary); margin-bottom:1rem">Insira sua chave API da OpenAI para habilitar a correção
        automática dos testes.</p>
    <form action="<?php echo BASE_URL; ?>admin/saveSettings" method="POST">
        <div class="form-group">
            <label class="form-label">API Key</label>
            <input type="password" name="api_key" class="form-input"
                value="<?php echo htmlspecialchars($apiKey ?? ''); ?>" placeholder="sk-..." required>
        </div>
        <button type="submit" class="btn-primary" style="max-width:200px">Salvar Chave</button>
    </form>
</div>