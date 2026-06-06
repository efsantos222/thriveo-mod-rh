<?php
require_once 'includes/auth.php';
checkLogin(); // Ensures user is logged in
include 'includes/header.php';
require_once 'config/db.php';

$companyId = getCompanyId();

// Fetch services ONLY for current company
$stmt = $pdo->prepare("SELECT * FROM portfolio_services WHERE company_id = ? ORDER BY name ASC");
$stmt->execute([$companyId]);
$services = $stmt->fetchAll();

// Prepare data for JS
$chartData = [];
foreach ($services as $s) {
    $chartData[] = [
        'id' => $s['id'],
        'label' => $s['name'],
        'x' => floatval($s['synergy_avg']),
        'y' => floatval($s['maturity_avg']),
        'r' => 8 // radius
    ];
}

function getQuadrant($x, $y)
{
    if ($x >= 5 && $y >= 5)
        return ['Star', 'Estrela', 'badge-star'];
    if ($x < 5 && $y >= 5)
        return ['Cash Cow', 'Gerador de Caixa', 'badge-cash'];
    if ($x >= 5 && $y < 5)
        return ['Motor', 'Motor', 'badge-motor'];
    return ['Explorer', 'Explorador', 'badge-explorer'];
}
?>

<div class="dashboard-grid">
    <!-- Matrix Chart Column -->
    <div class="card" style="grid-column: 1 / -1; min-height: 500px;">
        <div class="card-header">
            <h2 class="card-title">Matriz de Portfólio (<?= htmlspecialchars($_SESSION['company_name']) ?>)</h2>
            <a href="new_service.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Novo Serviço
            </a>
        </div>
        <div class="matrix-container">
            <canvas id="portfolioMatrix"></canvas>
        </div>
    </div>

    <!-- Interpretation Guide -->
    <div class="card" style="grid-column: 1 / -1; background-color: #f8fafc;">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0;">
            <h2 class="card-title" style="font-size: 1rem; color: var(--text-secondary);"><i
                    class="fa-solid fa-book-open"></i> Guia de Leitura Estratégica</h2>
        </div>
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; padding-top: 1rem;">

            <!-- Axes Explanation -->
            <div>
                <h3
                    style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; text-transform: uppercase;">
                    Entendendo os Eixos</h3>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Maturidade
                        Operacional (Eixo Y)</strong>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.4;">
                        Mede a qualidade da sua "fábrica". Quanto maior a nota, mais eficiente, padronizado,
                        automatizado e rentável é o processo de entrega do serviço.
                    </p>
                </div>
                <div>
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Sinergia
                        Estratégica (Eixo X)</strong>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.4;">
                        Mede o impacto no ecosistema. Quanto maior, mais esse serviço ajuda a vender outros, gera leads
                        qualificados e fortalece a marca.
                    </p>
                </div>
            </div>

            <!-- Quadrants Explanation -->
            <div>
                <h3
                    style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; text-transform: uppercase;">
                    O que fazer em cada Quadrante?</h3>
                <div style="display: grid; gap: 1rem;">

                    <div style="display: flex; gap: 0.75rem; align-items: start;">
                        <span class="badge badge-explorer"
                            style="width: 110px; text-align: center; flex-shrink: 0;">Exploradores</span>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">
                            <strong>Investir (P&D) ou Descontinuar.</strong> São apostas. Defina metas curtas para
                            provar valor ou encerre para não drenar recursos.
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: start;">
                        <span class="badge badge-motor"
                            style="width: 110px; text-align: center; flex-shrink: 0;">Motores</span>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">
                            <strong>Estruturar e Padronizar.</strong> Trazem muitos clientes mas a operação é caótica.
                            Invista em processos para torná-los escaláveis.
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: start;">
                        <span class="badge badge-cash"
                            style="width: 110px; text-align: center; flex-shrink: 0;">Geradores</span>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">
                            <strong>Otimizar e Colher.</strong> O "filé mignon" financeiro. Mantenha eficiente e use o
                            caixa gerado para financiar a inovação.
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: start;">
                        <span class="badge badge-star"
                            style="width: 110px; text-align: center; flex-shrink: 0;">Estrelas</span>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">
                            <strong>Integrar e Liderar.</strong> O melhor dos mundos. Foque em manter a liderança e
                            criar pacotes integrados com outros serviços.
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Service List Column -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h2 class="card-title">Detalhamento dos Serviços</h2>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Maturidade (Y)</th>
                        <th>Sinergia (X)</th>
                        <th>Classificação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service):
                        $quadrant = getQuadrant($service['synergy_avg'], $service['maturity_avg']);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($service['name']) ?></strong><br>
                                <small class="text-secondary"><?= htmlspecialchars($service['revenue_model']) ?></small>
                            </td>
                            <td><?= number_format($service['maturity_avg'], 2) ?></td>
                            <td><?= number_format($service['synergy_avg'], 2) ?></td>
                            <td><span class="badge <?= $quadrant[2] ?>"><?= $quadrant[1] ?></span></td>
                            <td>
                                <a href="view_service.php?id=<?= $service['id'] ?>" class="btn btn-outline btn-sm"
                                    style="padding: 0.2rem 0.5rem;" title="Ver Detalhes e Estratégia">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="new_service.php?id=<?= $service['id'] ?>" class="btn btn-outline btn-sm"
                                    style="padding: 0.2rem 0.5rem;" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="delete_service.php?id=<?= $service['id'] ?>" class="btn btn-outline btn-sm"
                                    style="padding: 0.2rem 0.5rem; color: #ef4444; border-color: #ef4444;"
                                    onclick="return confirm('Tem certeza?')" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($services) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary);">Nenhum serviço
                                cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Pass PHP data to JS
    const servicesData = <?= json_encode($chartData) ?>;
</script>

<?php include 'includes/footer.php'; ?>