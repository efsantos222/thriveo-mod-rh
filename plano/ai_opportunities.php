<?php
include 'includes/header.php';
?>

<div class="card animate-fade-in" style="min-height: 400px;">
    <div class="card-header">
        <h2 class="card-title">Inteligência de Mercado: Sugestões de Expansão</h2>
        <button id="generateOpportunitiesBtn" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-lightbulb"></i> Analisar e Gerar Ideias
        </button>
    </div>

    <div class="dashboard-grid" id="opportunitiesContainer">
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary); padding: 3rem;">
            <i class="fa-solid fa-robot fa-3x" style="margin-bottom: 1rem; color: var(--border-color);"></i>
            <p>Clique no botão acima para que a IA analise seu portfólio atual e sugira novas oportunidades de negócios.
            </p>
        </div>
    </div>
</div>

<template id="opportunityTemplate">
    <div class="card" style="border-left: 4px solid var(--primary-color);">
        <h3 class="card-title" style="margin-bottom: 0.5rem;"></h3>
        <p class="text-description" style="margin-bottom: 1rem; color: var(--text-secondary);"></p>
        <div style="background: var(--background-color); padding: 1rem; border-radius: var(--radius-md);">
            <strong><i class="fa-solid fa-bullseye"></i> Por que apostar?</strong>
            <p class="rationale-text" style="font-size: 0.9rem; margin-top: 0.5rem;"></p>
        </div>
        <div style="margin-top: 1rem; text-align: right;">
            <a href="new_service.php" class="btn btn-outline btn-sm use-suggestion">
                <i class="fa-solid fa-plus"></i> Usar Ideia
            </a>
        </div>
    </div>
</template>

<script>
    document.getElementById('generateOpportunitiesBtn').addEventListener('click', async function () {
        const btn = this;
        const container = document.getElementById('opportunitiesContainer');

        // Loading State
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analisando...';
        container.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 2rem;">Gerando análises estratégicas...<br>(Isso pode levar alguns segundos)</div>';

        try {
            const response = await fetch('api/suggest_opportunities.php');
            const data = await response.json();

            if (data.error) throw new Error(data.error);
            if (!data.suggestions) throw new Error('Formato inválido da IA');

            container.innerHTML = ''; // Clear loading
            const template = document.getElementById('opportunityTemplate');

            data.suggestions.forEach(suggestion => {
                const clone = template.content.cloneNode(true);
                clone.querySelector('.card-title').textContent = suggestion.name;
                clone.querySelector('.text-description').textContent = suggestion.description;
                clone.querySelector('.rationale-text').textContent = suggestion.rationale;

                // Pass name to new service page
                const link = clone.querySelector('.use-suggestion');
                link.href = `new_service.php?name=${encodeURIComponent(suggestion.name)}`;

                container.appendChild(clone);
            });

        } catch (error) {
            container.innerHTML = `<div class="alert" style="color: red; grid-column: 1/-1;">Erro: ${error.message}. Verifique a chave da API.</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-lightbulb"></i> Analisar e Gerar Ideias';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>