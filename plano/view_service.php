<?php
include 'includes/header.php';
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM portfolio_services WHERE id = ?");
$stmt->execute([$_GET['id']]);
$service = $stmt->fetch();

if (!$service) {
    echo "<div class='container'>Serviço não encontrado.</div>";
    include 'includes/footer.php';
    exit();
}

// Determine Quadrant Data
$maturity = floatval($service['maturity_avg']);
$synergy = floatval($service['synergy_avg']);
$quadrantInfo = [];

if ($synergy >= 5 && $maturity >= 5)
    $quadrantInfo = ['Estrela', 'badge-star', 'Integrar e Liderar'];
elseif ($synergy < 5 && $maturity >= 5)
    $quadrantInfo = ['Gerador de Caixa', 'badge-cash', 'Otimizar e Colher'];
elseif ($synergy >= 5 && $maturity < 5)
    $quadrantInfo = ['Motor', 'badge-motor', 'Estruturar e Padronizar'];
else
    $quadrantInfo = ['Explorador', 'badge-explorer', 'Investir ou Descontinuar'];

$hasPlan = !empty($service['strategic_plan']);
?>

<div class="dashboard-grid">
    <!-- Header / Info -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <div>
                <span class="badge <?= $quadrantInfo[1] ?>"
                    style="margin-bottom: 0.5rem; display: inline-block;"><?= $quadrantInfo[0] ?></span>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">
                    <?= htmlspecialchars($service['name']) ?></h1>
                <p style="color: var(--text-secondary);"><?= htmlspecialchars($service['description']) ?></p>
            </div>
            <div style="text-align: right;">
                <a href="new_service.php?id=<?= $service['id'] ?>" class="btn btn-outline btn-sm"><i
                        class="fa-solid fa-pen"></i> Editar Info</a>
                <a href="index.php" class="btn btn-outline btn-sm">Voltar</a>
            </div>
        </div>

        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md);">
            <div>
                <small
                    style="color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">Modelo
                    de Receita</small>
                <div style="font-weight: 500;"><?= htmlspecialchars($service['revenue_model']) ?></div>
            </div>
            <div>
                <small
                    style="color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">Público-Alvo</small>
                <div style="font-weight: 500;"><?= htmlspecialchars($service['target_audience']) ?></div>
            </div>
            <div>
                <small
                    style="color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">Média
                    Maturidade (Y)</small>
                <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">
                    <?= number_format($maturity, 2) ?></div>
            </div>
            <div>
                <small
                    style="color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">Média
                    Sinergia (X)</small>
                <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">
                    <?= number_format($synergy, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- AI Strategic Plan -->
    <div class="card" style="grid-column: 1 / -1; border-top: 4px solid var(--primary-color);">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-chess-knight" style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                Plano de Ação Estratégico (IA)
            </h2>
            <button id="btnRegenerate" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrows-rotate"></i> Regenerar Plano
            </button>
        </div>

        <div id="planContent" style="line-height: 1.7; color: var(--text-primary);">
            <?php if ($hasPlan): ?>
                <?= $service['strategic_plan'] ?>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fa-solid fa-wand-magic-sparkles fa-3x"
                        style="color: var(--border-color); margin-bottom: 1rem;"></i>
                    <p>Gerando estratégias para alavancar este serviço...</p>
                    <div id="loader" class="fa-3x"><i class="fas fa-circle-notch fa-spin"
                            style="font-size: 1.5rem; color: var(--primary-color);"></i></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const serviceId = <?= $service['id'] ?>;
    const hasPlan = <?= $hasPlan ? 'true' : 'false' ?>;

    async function generatePlan() {
        const contentDiv = document.getElementById('planContent');
        contentDiv.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fa-solid fa-robot fa-bounce" style="color: var(--primary-color); font-size: 2rem; margin-bottom: 1rem;"></i>
                <p>A IA está analisando seus dados e criando objetivos estratégicos para ampliar mercado e receitas...</p>
            </div>
        `;

        try {
            const response = await fetch('api/generate_strategy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: serviceId })
            });

            const data = await response.json();

            if (data.error) throw new Error(data.error);

            contentDiv.innerHTML = data.plan;

        } catch (error) {
            contentDiv.innerHTML = `<div class="alert" style="color: red;">Erro ao gerar plano: ${error.message}</div>`;
        }
    }

    document.getElementById('btnRegenerate').addEventListener('click', generatePlan);

    // Auto-generate if empty
    if (!hasPlan) {
        generatePlan();
    }
</script>

<?php include 'includes/footer.php'; ?>