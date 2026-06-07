<?php
include 'includes/header.php';
require_once 'config/db.php';

$service = null;
$prefillName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM portfolio_services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $service = $stmt->fetch();
}

// Helper for values
function val($key, $default = '')
{
    global $service;
    return $service ? $service[$key] : $default;
}

function num_val($key, $default = 0)
{
    global $service;
    return $service ? $service[$key] : $default;
}

// Tooltips Definitions
$tooltips = [
    'score_standardization' => 'O quão bem definidos e repetíveis são os processos de entrega? (1 = Caótico, 10 = Altamente Padronizado)',
    'score_scalability' => 'O serviço pode crescer sem aumento proporcional de custos e complexidade? (1 = Artesanal, 10 = Exponencial)',
    'score_margin' => 'A margem de lucro é estável e previsível? (1 = Volátil/Baixa, 10 = Garantida/Alta)',
    'score_automation' => 'Qual o nível de automação envolvido na entrega? (1 = 100% Manual, 10 = 100% Automático)',
    'score_lead_gen' => 'O serviço gera oportunidades qualificadas para outras áreas? (1 = Não gera, 10 = Principal porta de entrada)',
    'score_relationship' => 'O serviço aprofunda a fidelidade do cliente? (1 = Transacional, 10 = Parceria Estratégica)',
    'score_cross_sell' => 'Cria oportunidade natural para vender outros serviços? (1 = Isolado, 10 = Abre todo o portfólio)',
    'score_brand_value' => 'Posiciona a empresa como líder de pensamento? (1 = Commodity, 10 = Referência de Mercado)'
];
?>

<div class="card animate-fade-in">
    <div class="card-header">
        <h2 class="card-title"><?= $service ? 'Editar Serviço' : 'Novo Serviço' ?></h2>
        <div style="display: flex; gap: 10px;">
            <?php if (!$service): ?>
                <button type="button" class="btn btn-outline btn-sm" id="btnFillAI"
                    title="Preencher detalhes automaticamente com IA">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Sugerir Detalhes
                </button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-outline btn-sm">Cancelar</a>
        </div>
    </div>

    <form action="save_service.php" method="POST">
        <?php if ($service): ?>
            <input type="hidden" name="id" value="<?= $service['id'] ?>">
        <?php endif; ?>

        <!-- Etapa 1: Mapeamento -->
        <div class="form-section">
            <h3 class="form-section-title">1. Mapeamento do Serviço</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nome do Serviço</label>
                    <input type="text" id="nameInput" name="name" class="form-control" required
                        value="<?= $service ? val('name') : $prefillName ?>" placeholder="Ex: Cloud Migration">
                </div>
                <div class="form-group">
                    <label>Modelo de Receita</label>
                    <input type="text" id="revenueInput" name="revenue_model" class="form-control"
                        placeholder="Ex: Taxa Fixa, Assinatura..." value="<?= val('revenue_model') ?>">
                </div>
                <div class="form-group">
                    <label>Público-Alvo</label>
                    <input type="text" id="audienceInput" name="target_audience" class="form-control"
                        placeholder="Ex: C-Level, Pequenas Empresas..." value="<?= val('target_audience') ?>">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Recursos Principais</label>
                    <input type="text" id="resourcesInput" name="key_resources" class="form-control"
                        placeholder="Ex: Consultores, Servidores..." value="<?= val('key_resources') ?>">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Descrição</label>
                    <textarea id="descInput" name="description"
                        class="form-control"><?= val('description') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Etapa 2: Avaliação -->
        <div class="dashboard-grid">
            <!-- Eixo Y -->
            <div class="form-section">
                <h3 class="form-section-title">
                    2. Maturidade Operacional (Eixo Y)
                </h3>

                <?php
                $fieldsY = [
                    'score_standardization' => 'Padronização de Processos',
                    'score_scalability' => 'Escalabilidade',
                    'score_margin' => 'Previsibilidade de Margem',
                    'score_automation' => 'Automação'
                ];
                foreach ($fieldsY as $field => $label): ?>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; justify-content: space-between;">
                            <?= $label ?>
                            <i class="fa-regular fa-circle-question tooltip-icon" title="<?= $tooltips[$field] ?>"
                                style="color: var(--primary-color); cursor: help;"></i>
                        </label>
                        <div class="range-slider-container">
                            <input type="range" name="<?= $field ?>" min="0" max="10" step="1"
                                value="<?= num_val($field, 5) ?>" class="range-input"
                                oninput="this.nextElementSibling.innerText = this.value">
                            <span class="range-value"><?= num_val($field, 5) ?></span>
                        </div>
                        <small
                            style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 0.2rem;"><?= $tooltips[$field] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Eixo X -->
            <div class="form-section">
                <h3 class="form-section-title">
                    3. Sinergia Estratégica (Eixo X)
                </h3>

                <?php
                $fieldsX = [
                    'score_lead_gen' => 'Geração de Leads',
                    'score_relationship' => 'Fortalecimento de Relacionamento',
                    'score_cross_sell' => 'Potencial de Cross-Selling',
                    'score_brand_value' => 'Valor de Marca'
                ];
                foreach ($fieldsX as $field => $label): ?>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; justify-content: space-between;">
                            <?= $label ?>
                            <i class="fa-regular fa-circle-question tooltip-icon" title="<?= $tooltips[$field] ?>"
                                style="color: var(--primary-color); cursor: help;"></i>
                        </label>
                        <div class="range-slider-container">
                            <input type="range" name="<?= $field ?>" min="0" max="10" step="1"
                                value="<?= num_val($field, 5) ?>" class="range-input"
                                oninput="this.nextElementSibling.innerText = this.value">
                            <span class="range-value"><?= num_val($field, 5) ?></span>
                        </div>
                        <small
                            style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 0.2rem;"><?= $tooltips[$field] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="text-align: right; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Salvar Serviço
            </button>
        </div>

    </form>
</div>

<script>
    document.getElementById('btnFillAI')?.addEventListener('click', async function () {
        const nameInput = document.getElementById('nameInput');
        const serviceName = nameInput.value;
        const btn = this;

        if (!serviceName) {
            alert('Por favor, digite o nome do serviço primeiro.');
            nameInput.focus();
            return;
        }

        // Loading
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Consultando IA...';

        try {
            const response = await fetch('api/suggest_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_name: serviceName })
            });
            const data = await response.json();

            if (data.error) throw new Error(data.error);

            // Fill fields
            if (data.description) document.getElementById('descInput').value = data.description;
            if (data.revenue_model) document.getElementById('revenueInput').value = data.revenue_model;
            if (data.target_audience) document.getElementById('audienceInput').value = data.target_audience;
            if (data.key_resources) document.getElementById('resourcesInput').value = data.key_resources;

        } catch (err) {
            alert('Erro ao consultar IA: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>