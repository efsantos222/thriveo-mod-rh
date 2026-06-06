<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$openai_key = '';
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
$stmt->execute();
$openai_key = $stmt->fetchColumn();

// If in production, you might not want to expose the key to frontend directly if possible,
// but for this architecture where JS calls Python, we might need a proxy or send it.
// Ideally, PHP calls Python. But let's build the UI first.
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Inteligência Competitiva - Proftest IC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: rgba(0, 0, 0, 0.2);
            border-right: 1px solid var(--glass-border);
            padding: 2rem 1rem;
        }

        .content {
            flex: 1;
            padding: 2rem;
        }

        .nav-item {
            display: block;
            padding: 1rem;
            color: var(--text-muted);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            padding: 0.75rem;
            border-radius: 0.5rem;
            color: white;
            width: 100%;
            margin-bottom: 1rem;
            font-family: inherit;
        }

        /* Analysis Result Styles */
        .result-box {
            display: none;
            margin-top: 2rem;
            animation: fadeIn 0.5s ease;
        }

        .swot-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .swot-card {
            padding: 1rem;
            border-radius: 0.5rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
        }

        .swot-card h4 {
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .score-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        .loading i {
            animation: spin 1s linear infinite;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 0.25rem;
            padding: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <div class="sidebar">
            <h3 style="margin-bottom: 2rem; padding-left: 1rem;">
                <?= $_SESSION['user_role'] === 'admin' ? 'Admin Panel' : 'Painel Cliente' ?>
            </h3>
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <a href="#" class="nav-item active" id="nav-new-analysis" onclick="showNewAnalysis()"><i
                    class="fa-solid fa-robot"></i> Análise de Site</a>
            <a href="#" class="nav-item" id="nav-radar" onclick="showRadar()"><i class="fa-solid fa-satellite-dish"></i>
                Radar de Mercado</a>
            <a href="#" class="nav-item" id="nav-history" onclick="showHistory()"><i class="fa-solid fa-chart-line"></i>
                Histórico</a>
            <a href="logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>

        <div class="content">
            <!-- VIEW: New Analysis -->
            <div id="view-new-analysis">
                <h1>Análise Competitiva</h1>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Utilize a IA para analisar concorrentes, sites
                    e
                    estratégias de mercado.</p>

                <div class="card" style="max-width: 800px;">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">URL do Concorrente ou Site</label>
                        <div style="display: flex; gap: 1rem;">
                            <input type="text" id="target_url" placeholder="https://exemplo.com.br" class="form-control"
                                style="margin-bottom: 0;">
                            <button onclick="runAnalysis()" class="btn-login" style="white-space: nowrap;">
                                <i class="fa-solid fa-bolt"></i> Analisar
                            </button>
                        </div>
                    </div>

                    <p style="font-size: 0.85rem; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info"></i> A análise pode levar até 30 segundos. O sistema fará o
                        scraping da página e processará com GPT-4.
                    </p>
                </div>

                <!-- Loading State -->
                <div id="loading" class="loading">
                    <i class="fa-solid fa-circle-notch"></i>
                    <p>Analisando dados e gerando insights estratégicos...</p>
                </div>

                <!-- Results -->
                <div id="result" class="result-box">

                    <!-- Header Result -->
                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                        <div class="card"
                            style="flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center;">
                            <div class="score-circle" id="threat_score">0</div>
                            <span style="color: var(--text-muted);">Score de Ameaça</span>
                        </div>
                        <div class="card" style="flex: 3;">
                            <h3>Recomendações Estratégicas</h3>
                            <ul id="recommendations_list"
                                style="padding-left: 1.5rem; margin-top: 1rem; color: var(--text-muted);">
                                <!-- Items injected via JS -->
                            </ul>
                        </div>
                    </div>

                    <!-- SWOT -->
                    <h3 style="margin-bottom: 1rem;">Matriz SWOT (Gerada por IA)</h3>
                    <div class="swot-grid">
                        <div class="swot-card" style="border-left: 3px solid #34d399;">
                            <h4 style="color: #34d399;">Forças (Strengths)</h4>
                            <ul id="swot_s" style="padding-left: 1.2rem; font-size: 0.9rem; color: var(--text-muted);">
                            </ul>
                        </div>
                        <div class="swot-card" style="border-left: 3px solid #f87171;">
                            <h4 style="color: #f87171;">Fraquezas (Weaknesses)</h4>
                            <ul id="swot_w" style="padding-left: 1.2rem; font-size: 0.9rem; color: var(--text-muted);">
                            </ul>
                        </div>
                        <div class="swot-card" style="border-left: 3px solid #fbbf24;">
                            <h4 style="color: #fbbf24;">Oportunidades (Opportunities)</h4>
                            <ul id="swot_o" style="padding-left: 1.2rem; font-size: 0.9rem; color: var(--text-muted);">
                            </ul>
                        </div>
                        <div class="swot-card" style="border-left: 3px solid #60a5fa;">
                            <h4 style="color: #60a5fa;">Ameaças (Threats)</h4>
                            <ul id="swot_t" style="padding-left: 1.2rem; font-size: 0.9rem; color: var(--text-muted);">
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
            <!-- END VIEW: New Analysis -->

            <!-- VIEW: Market Radar -->
            <div id="view-radar" style="display: none;">
                <h1>Radar de Mercado</h1>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Monitoramento avançado de legislação,
                    tendências e novos concorrentes.</p>

                <div class="card" style="max-width: 800px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem;">Ramo de Atividade</label>
                            <input type="text" id="radar_industry" placeholder="Ex: Farmacêutico, Varejo..."
                                class="form-control">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem;">País</label>
                            <input type="text" id="radar_country" placeholder="Ex: Brasil" class="form-control">
                        </div>
                    </div>

                    <button onclick="runRadar()" class="btn-login" style="width: 100%;">
                        <i class="fa-solid fa-satellite-dish"></i> Iniciar Radar
                    </button>

                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">
                        <i class="fa-solid fa-circle-info"></i> O sistema fará buscas por legislação, concorrentes e
                        tendências. Isso pode levar cerca de 45-60 segundos.
                    </p>
                </div>

                <!-- Radar Loading -->
                <div id="radar-loading" class="loading">
                    <i class="fa-solid fa-satellite-dish fa-spin"></i>
                    <p>Varrendo o mercado em busca de dados...</p>
                </div>

                <!-- Radar Results -->
                <div id="radar-result" class="result-box">
                    <div class="swot-grid" style="grid-template-columns: 1fr;">

                        <!-- 1. Legislação -->
                        <div class="swot-card" style="border-left: 3px solid #60a5fa;">
                            <h4 style="color: #60a5fa;"><i class="fa-solid fa-scale-balanced"></i> Legislação e
                                Regulação</h4>
                            <div id="radar_legislation"
                                style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>

                        <!-- 2. Tecnologias -->
                        <div class="swot-card" style="border-left: 3px solid #a78bfa;">
                            <h4 style="color: #a78bfa;"><i class="fa-solid fa-microchip"></i> Novas Tecnologias</h4>
                            <div id="radar_tech" style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
                            </div>
                        </div>

                        <!-- 3. Concorrentes -->
                        <div class="swot-card" style="border-left: 3px solid #f87171;">
                            <h4 style="color: #f87171;"><i class="fa-solid fa-users-viewfinder"></i> Novos Concorrentes
                            </h4>
                            <div id="radar_competitors"
                                style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>

                        <!-- 4. Fornecedores -->
                        <div class="swot-card" style="border-left: 3px solid #34d399;">
                            <h4 style="color: #34d399;"><i class="fa-solid fa-truck-fast"></i> Fornecedores em Ascensão
                            </h4>
                            <div id="radar_suppliers"
                                style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>

                        <!-- 5. Tendências -->
                        <div class="swot-card" style="border-left: 3px solid #fbbf24;">
                            <h4 style="color: #fbbf24;"><i class="fa-solid fa-arrow-trend-up"></i> Tendências de
                                Clientes</h4>
                            <div id="radar_trends"
                                style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;"></div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- END VIEW: Radar -->

            <!-- VIEW: History -->
            <div id="view-history" style="display: none;">
                <h1>Histórico de Análises</h1>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Registro das suas últimas consultas de
                    inteligência.</p>

                <div id="history-loading" class="loading">
                    <i class="fa-solid fa-circle-notch"></i>
                    <p>Carregando histórico...</p>
                </div>

                <div id="history-list" style="display: grid; gap: 1rem;">
                    <!-- History Cards will happen here -->
                </div>
            </div>
            <!-- END VIEW: History -->

        </div>
    </div>

    <script>
        // Navigation Logic
        function showNewAnalysis() {
            document.getElementById('view-new-analysis').style.display = 'block';
            document.getElementById('view-radar').style.display = 'none';
            document.getElementById('view-history').style.display = 'none';

            document.getElementById('nav-new-analysis').classList.add('active');
            document.getElementById('nav-radar').classList.remove('active');
            document.getElementById('nav-history').classList.remove('active');
        }

        function showRadar() {
            document.getElementById('view-new-analysis').style.display = 'none';
            document.getElementById('view-radar').style.display = 'block';
            document.getElementById('view-history').style.display = 'none';

            document.getElementById('nav-new-analysis').classList.remove('active');
            document.getElementById('nav-radar').classList.add('active');
            document.getElementById('nav-history').classList.remove('active');
        }

        function showHistory() {
            document.getElementById('view-new-analysis').style.display = 'none';
            document.getElementById('view-radar').style.display = 'none';
            document.getElementById('view-history').style.display = 'block';

            document.getElementById('nav-new-analysis').classList.remove('active');
            document.getElementById('nav-radar').classList.remove('active');
            document.getElementById('nav-history').classList.add('active');

            loadHistory();
        }

        async function loadHistory() {
            const list = document.getElementById('history-list');
            const loader = document.getElementById('history-loading');

            list.innerHTML = '';
            loader.style.display = 'block';

            try {
                const response = await fetch('api/get_history.php');
                const data = await response.json();

                if (data.error) {
                    list.innerHTML = `<p style="color: #f87171;">Erro: ${data.error}</p>`;
                    return;
                }

                if (data.length === 0) {
                    list.innerHTML = '<p style="color: var(--text-muted);">Nenhuma análise encontrada.</p>';
                }

                data.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'swot-card'; // Reuse style
                    card.style.cursor = 'pointer';
                    card.style.position = 'relative'; // For positioning delete button if needed, or flex layout
                    card.onclick = () => loadHistoricalResult(item);

                    const date = new Date(item.created_at).toLocaleString('pt-BR');
                    const threatColor = getScoreColor(item.threat_score);

                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="color: #fff; margin-bottom: 0.2rem;">${item.target_url}</h4>
                                <small style="color: var(--text-muted);">${date}</small>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <div style="text-align: right;">
                                    <span style="font-size: 1.5rem; font-weight: bold; color: ${threatColor}">${item.threat_score}</span>
                                    <br><small style="color: var(--text-muted);">Score</small>
                                </div>
                                <button class="btn-delete" onclick="deleteHistoryItem(${item.id}, event)" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    list.appendChild(card);
                });

            } catch (e) {
                console.error(e);
                list.innerHTML = '<p style="color: #f87171;">Erro ao carregar histórico.</p>';
            } finally {
                loader.style.display = 'none';
            }
        }

        async function deleteHistoryItem(id, event) {
            event.stopPropagation(); // Prevent opening the analysis

            if (!confirm('Tem certeza que deseja excluir este item do histórico?')) return;

            try {
                const response = await fetch('api/delete_history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const data = await response.json();

                if (data.success) {
                    loadHistory(); // Reload list
                } else {
                    alert('Erro ao excluir: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao excluir item.');
            }
        }

        function getScoreColor(score) {
            if (score > 70) return '#ef4444';
            if (score > 40) return '#fbbf24';
            return '#34d399';
        }

        async function loadHistoricalResult(summaryItem) {
            // Restore data to main view and switch
            
            // Check if it's a Radar item
            if (summaryItem.target_url.startsWith('RADAR:')) {
                loadRadarHistory(summaryItem);
                return;
            }

            // Normal Analysis
            showNewAnalysis();
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            document.getElementById('target_url').value = summaryItem.target_url;

            try {
                // Fetch Full Details
                const response = await fetch(`api/get_history.php?id=${summaryItem.id}`);
                const item = await response.json();

                if (item.error) {
                    alert('Erro ao carregar detalhes: ' + item.error);
                    document.getElementById('loading').style.display = 'none';
                    return;
                }

                const analysis = item.analysis;
                
                document.getElementById('threat_score').innerText = item.threat_score;
                document.getElementById('threat_score').style.borderColor = getScoreColor(item.threat_score);

                const recList = document.getElementById('recommendations_list');
                recList.innerHTML = '';
                if (analysis.recommendations) {
                    analysis.recommendations.forEach(r => {
                        const li = document.createElement('li');
                        li.innerText = r;
                        recList.appendChild(li);
                    });
                }

                const renderList = (id, items) => {
                    const ul = document.getElementById(id);
                    ul.innerHTML = '';
                    if (items) {
                        items.forEach(i => {
                            const li = document.createElement('li');
                            li.innerText = i;
                            ul.appendChild(li);
                        });
                    }
                };

                if (analysis.swot) {
                    renderList('swot_s', analysis.swot.s);
                    renderList('swot_w', analysis.swot.w);
                    renderList('swot_o', analysis.swot.o);
                    renderList('swot_t', analysis.swot.t);
                }

                document.getElementById('loading').style.display = 'none';
                document.getElementById('result').style.display = 'block';
                document.getElementById('result').scrollIntoView({ behavior: 'smooth' });

            } catch(e) {
                console.error(e);
                alert('Erro ao buscar detalhes.');
                document.getElementById('loading').style.display = 'none';
            }
        }

        async function loadRadarHistory(summaryItem) {
            showRadar();
            document.getElementById('radar-loading').style.display = 'block';
            document.getElementById('radar-result').style.display = 'none';
            
            // Extract Industry/Country from string "RADAR: Industry - Country"
            const parts = summaryItem.target_url.replace('RADAR: ', '').split(' - ');
            if(parts.length >= 2) {
                document.getElementById('radar_industry').value = parts[0];
                document.getElementById('radar_country').value = parts[1];
            }

            try {
                 // Fetch Full Details
                const response = await fetch(`api/get_history.php?id=${summaryItem.id}`);
                const item = await response.json();

                if (item.error) {
                    alert('Erro: ' + item.error);
                    document.getElementById('radar-loading').style.display = 'none';
                    return;
                }

                const data = item.analysis;

                // Helper to format list reused 
                const formatList = (items) => {
                    if(!items || items.length === 0) return 'Sem dados.';
                    return '<ul style="padding-left: 1.2rem; margin-top: 0.5rem;">' + 
                        items.map(i => `<li>${i}</li>`).join('') + 
                        '</ul>';
                };

                document.getElementById('radar_legislation').innerHTML = formatList(data.legislation);
                document.getElementById('radar_tech').innerHTML = formatList(data.technologies);
                document.getElementById('radar_competitors').innerHTML = formatList(data.competitors);
                document.getElementById('radar_suppliers').innerHTML = formatList(data.suppliers);
                document.getElementById('radar_trends').innerHTML = formatList(data.trends);

                document.getElementById('radar-loading').style.display = 'none';
                document.getElementById('radar-result').style.display = 'block';

            } catch(e) {
                console.error(e);
                alert('Erro ao carregar detalhes do radar.');
                document.getElementById('radar-loading').style.display = 'none';
            }
        }

        async function runAnalysis() {
            const url = document.getElementById('target_url').value;
            if (!url) {
                alert('Por favor, insira uma URL.');
                return;
            }

            // UI Updates
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').style.display = 'none';

            // Fetch from PHP API
            try {
                const response = await fetch('api/analyze.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: url })
                });

                const data = await response.json();

                if (data.error) {
                    alert('Erro: ' + data.error);
                    document.getElementById('loading').style.display = 'none';
                    return;
                }

                // Render Data
                document.getElementById('threat_score').innerText = data.threat_score;
                document.getElementById('threat_score').style.borderColor = getScoreColor(data.threat_score);

                const recList = document.getElementById('recommendations_list');
                recList.innerHTML = '';
                if (data.recommendations) {
                    data.recommendations.forEach(r => {
                        const li = document.createElement('li');
                        li.innerText = r;
                        recList.appendChild(li);
                    });
                }

                const renderList = (id, items) => {
                    const ul = document.getElementById(id);
                    ul.innerHTML = '';
                    if (items) {
                        items.forEach(i => {
                            const li = document.createElement('li');
                            li.innerText = i;
                            ul.appendChild(li);
                        });
                    }
                };

                renderList('swot_s', data.swot.s);
                renderList('swot_w', data.swot.w);
                renderList('swot_o', data.swot.o);
                renderList('swot_t', data.swot.t);

                document.getElementById('loading').style.display = 'none';
                document.getElementById('result').style.display = 'block';

            } catch (e) {
                console.error(e);
                alert('Erro na comunicação com o servidor. Verifique o console.');
                document.getElementById('loading').style.display = 'none';
            }
        }
        async function runRadar() {
            const industry = document.getElementById('radar_industry').value;
            const country = document.getElementById('radar_country').value;

            if (!industry || !country) {
                alert('Preencha o Ramo de Atividade e o País.');
                return;
            }

            document.getElementById('radar-loading').style.display = 'block';
            document.getElementById('radar-result').style.display = 'none';

            try {
                const response = await fetch('api/radar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ industry, country })
                });

                const data = await response.json();

                if (data.error) {
                    alert('Erro: ' + data.error);
                    document.getElementById('radar-loading').style.display = 'none';
                    return;
                }

                // Render Results
                const formatList = (items) => {
                    if (!items || items.length === 0) return 'Sem dados.';
                    return '<ul style="padding-left: 1.2rem; margin-top: 0.5rem;">' +
                        items.map(i => `<li>${i}</li>`).join('') +
                        '</ul>';
                };

                document.getElementById('radar_legislation').innerHTML = formatList(data.legislation);
                document.getElementById('radar_tech').innerHTML = formatList(data.technologies);
                document.getElementById('radar_competitors').innerHTML = formatList(data.competitors);
                document.getElementById('radar_suppliers').innerHTML = formatList(data.suppliers);
                document.getElementById('radar_trends').innerHTML = formatList(data.trends);

                document.getElementById('radar-result').style.display = 'block';

            } catch (e) {
                console.error(e);
                alert('Erro ao processar radar.');
            } finally {
                document.getElementById('radar-loading').style.display = 'none';
            }
        }
    </script>

</body>

</html>