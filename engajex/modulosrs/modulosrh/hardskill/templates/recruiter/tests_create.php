<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Gerar Novo Teste Técnico</h1>
<p class="text-muted mb-4">Utilize a Inteligência Artificial para criar um teste customizado.</p>

<div class="card" style="max-width: 800px;">
    <form id="generateForm">
        <div class="form-group">
            <label for="job_description">Descrição da Vaga / Competências</label>
            <textarea name="job_description" id="job_description" rows="5"
                placeholder="Ex: Desenvolvedor PHP Pleno com experiência em Laravel, Docker e REST APIs..."
                required></textarea>
        </div>

        <div class="form-group">
            <label for="level">Nível de Senioridade</label>
            <select name="level" id="level">
                <option value="Júnior">Júnior</option>
                <option value="Pleno" selected>Pleno</option>
                <option value="Sênior">Sênior</option>
                <option value="Especialista">Especialista</option>
            </select>
        </div>

        <div id="loading" style="display: none; text-align: center; margin: 1rem 0;">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary-color)"></i>
            <p class="mt-4">Aguarde, a IA está elaborando as questões...</p>
        </div>

        <div id="error-msg" class="alert alert-error" style="display:none;"></div>

        <button type="submit" class="btn btn-primary" id="btn-submit">
            <i class="fas fa-magic"></i> Gerar Teste
        </button>
    </form>
</div>

<script>
    document.getElementById('generateForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const btn = document.getElementById('btn-submit');
        const loading = document.getElementById('loading');
        const errorMsg = document.getElementById('error-msg');

        btn.style.display = 'none';
        loading.style.display = 'block';
        errorMsg.style.display = 'none';

        const formData = new FormData(form);

        fetch('<?= APP_URL ?>/recruiter/tests/generate', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= APP_URL ?>' + data.redirect;
                } else {
                    errorMsg.innerText = data.error || 'Erro desconhecido';
                    errorMsg.style.display = 'block';
                    btn.style.display = 'block';
                    loading.style.display = 'none';
                }
            })
            .catch(err => {
                errorMsg.innerText = 'Erro na comunicação com o servidor.';
                errorMsg.style.display = 'block';
                btn.style.display = 'block';
                loading.style.display = 'none';
                console.error(err);
            });
    });
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>