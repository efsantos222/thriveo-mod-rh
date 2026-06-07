<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['responsible', 'company_admin', 'manager', 'admin'])) {
    die("Acesso restrito a Responsáveis, Admins e Gerentes.");
}

$page = 'budget_zbb';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <title>Orçamento ZBB - Thriveo ZBB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Orçamento Base Zero (ZBB)</h1>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <form method="GET" style="display: flex; gap: 0.5rem;">
                        <select name="month" class="form-control" style="width: auto;">
                            <?php
                            $m = date('m');
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $m) ? 'selected' : '';
                                echo "<option value='$i' $selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
                            }
                            ?>
                        </select>
                        <select name="year" class="form-control" style="width: auto;">
                            <option value="2025">2025</option>
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                        </select>
                        <button class="btn btn-secondary" title="Filtrar">🔍</button>
                    </form>

                    <button class="btn btn-secondary" onclick="openModal('importExcelModal')">📂 Importar Excel</button>
                    <button class="btn btn-secondary"
                        onclick="openModal('manageParametersModal'); loadCostCenters();">⚙️ Parâmetros</button>
                    <button class="btn btn-primary" onclick="openModal('newPackageModal')">+ Novo Pacote de
                        Despesa</button>
                </div>
            </header>

            <div class="card">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: var(--background-color);">
                                <th style="padding: 1rem; text-align: left;">Centro de Custo</th>
                                <th style="padding: 1rem; text-align: left;">Categoria</th>
                                <th style="padding: 1rem; text-align: left;">Natureza</th>
                                <th style="padding: 1rem; text-align: right;">Realizado (Ref. Ant.)</th>
                                <th style="padding: 1rem; text-align: right;">Realizado Atual</th>
                                <th style="padding: 1rem; text-align: center;">Evolução %</th>
                                <th style="padding: 1rem; text-align: center;">Status</th>
                                <th style="padding: 1rem; text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch Budgets from Database
                            $companyId = $_SESSION['company_id'] ?? 1; // Default fallback needs improvement later
                            $currentYear = $_GET['year'] ?? 2026;
                            $currentMonth = $_GET['month'] ?? date('m');

                            try {
                                $sql = "
                                    SELECT 
                                        b.id,
                                        cc.name as cost_center,
                                        c.name as category,
                                        c.type as nature,
                                        b.amount_budgeted as realized_ref,
                                        b.amount_realized as realized_current,
                                        b.status,
                                        b.justification
                                    FROM budgets b
                                    JOIN cost_centers cc ON b.cost_center_id = cc.id
                                    JOIN categories c ON b.category_id = c.id
                                    WHERE b.company_id = ? 
                                    AND b.fiscal_year = ? 
                                    AND b.month = ?
                                ";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$companyId, $currentYear, $currentMonth]);
                                $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($budgets) > 0) {
                                    foreach ($budgets as $budget) {
                                        // Calculate Evolution
                                        $evolution = 0;
                                        if ($budget['realized_ref'] > 0) {
                                            $evolution = (($budget['realized_current'] - $budget['realized_ref']) / $budget['realized_ref']) * 100;
                                        }
                                        $evoColor = $evolution > 0 ? '#ef4444' : '#10b981';
                                        $evoText = ($evolution > 0 ? '+' : '') . number_format($evolution, 2, ',', '.') . '%';

                                        // Map Nature Colors
                                        $natureLabel = '';
                                        $natureStyle = '';
                                        switch ($budget['nature']) {
                                            case 'fixed':
                                                $natureLabel = 'Fixa';
                                                $natureStyle = 'background: #e0f2fe; color: #0284c7;';
                                                break;
                                            case 'variable':
                                                $natureLabel = 'Variável';
                                                $natureStyle = 'background: #fef3c7; color: #d97706;';
                                                break;
                                            case 'discretionary':
                                                $natureLabel = 'Discricionária';
                                                $natureStyle = 'background: #f3e8ff; color: #7e22ce;';
                                                break;
                                        }

                                        // Status
                                        $statusLabel = ucfirst($budget['status']);
                                        $statusColor = 'var(--text-secondary)';
                                        if ($budget['status'] == 'approved') {
                                            $statusLabel = 'Aprovado';
                                            $statusColor = 'var(--success-color)';
                                        }
                                        if ($budget['status'] == 'pending') {
                                            $statusLabel = 'Pendente';
                                            $statusColor = 'var(--warning-color)';
                                        }
                                        if ($budget['status'] == 'draft') {
                                            $statusLabel = 'Rascunho';
                                            $statusColor = '#64748b';
                                        }

                                        echo "
                                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                                            <td style='padding: 1rem; font-weight: 500;'>{$budget['cost_center']}</td>
                                            <td style='padding: 1rem; font-weight: 600;'>{$budget['category']}</td>
                                            <td style='padding: 1rem;'><span style='$natureStyle padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.75rem;'>$natureLabel</span></td>
                                            <td style='padding: 1rem; text-align: right; color: var(--text-secondary);'>R$ " . number_format($budget['realized_ref'], 2, ',', '.') . "</td>
                                            <td style='padding: 1rem; text-align: right; font-weight: 700;'>R$ " . number_format($budget['realized_current'], 2, ',', '.') . "</td>
                                            <td style='padding: 1rem; text-align: center; color: $evoColor; font-weight: 600;'>$evoText</td>
                                            <td style='padding: 1rem; text-align: center;'><span style='color: $statusColor;'>$statusLabel</span></td>
                                            <td style='padding: 1rem; text-align: center;'>
                                                <button class='btn btn-secondary btn-sm' onclick=\"openDetailsModal({$budget['id']})\">Detalhes</button>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' style='padding: 2rem; text-align: center; color: var(--text-secondary);'>Nenhum registro encontrado para este período.</td></tr>";
                                }

                            } catch (Exception $e) {
                                echo "<tr><td colspan='8' style='color: red; text-align: center;'>Erro ao carregar dados: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Justification Modal -->
            <div id="justificationModal" class="card"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; width: 90%; max-width: 600px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);">
                <h3 class="mb-4">Justificativa ZBB</h3>
                <form>
                    <div class="form-group">
                        <label class="form-label">Por que esta despesa é necessária?</label>
                        <textarea class="form-control" rows="3"
                            placeholder="Explique a necessidade real desta despesa..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Impacto se rejeitado</label>
                        <textarea class="form-control" rows="2"
                            placeholder="O que acontece se não aprovarmos?"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alternativas consideradas</label>
                        <textarea class="form-control" rows="2" placeholder="Existe opção mais barata?"></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('justificationModal')">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="alert('Justificativa enviada com sucesso!')">Enviar</button>
                    </div>
                </form>
            </div>

            <!-- New Package Modal -->
            <div id="newPackageModal" class="card"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; width: 90%; max-width: 600px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);">
                <h3 class="mb-4">Novo Pacote de Despesa</h3>
                <form>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Mês</label>
                            <select class="form-control">
                                <option value="1">Janeiro</option>
                                <option value="2">Fevereiro</option>
                                <!-- ... -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ano</label>
                            <input type="number" class="form-control" value="2026">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Centro de Custo</label>
                        <select class="form-control">
                            <option>Selecione...</option>
                            <option>Administrativo</option>
                            <option>Marketing e Vendas</option>
                            <option>TI & Infra</option>
                            <option>Operações</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoria</label>
                        <select class="form-control">
                            <option>Selecione...</option>
                            <option>Marketing</option>
                            <option>TI & Software</option>
                            <option>Pessoal</option>
                            <option>Viagens</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Natureza</label>
                        <select class="form-control">
                            <option value="variable">Variável</option>
                            <option value="fixed">Fixa</option>
                            <option value="discretionary">Discricionária</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor Orçado</label>
                        <input type="text" class="form-control" placeholder="R$ 0,00">
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('newPackageModal')">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="alert('Pacote criado com sucesso!')">Criar Pacote</button>
                    </div>
                </form>
            </div>

            <!-- Import Excel Modal -->
            <div id="importExcelModal" class="card"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);">
                <h3 class="mb-4">Importar Despesas (Excel)</h3>
                <p class="text-secondary mb-4">
                    Faça upload de uma planilha (.xlsx ou .csv) contendo as colunas obrigatórias:<br>
                    <strong>Centro de Custo, Categoria, Natureza, Valor (Ref. Ant.), Valor (Atual),
                        Justificativa</strong>.
                </p>
                <form enctype="multipart/form-data">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Mês</label>
                            <select id="importMonth" class="form-control">
                                <?php
                                $m = date('m');
                                for ($i = 1; $i <= 12; $i++) {
                                    $sel = ($i == $m) ? 'selected' : '';
                                    echo "<option value='$i' $sel>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Ano</label>
                            <select id="importYear" class="form-control">
                                <option value="2025">2025</option>
                                <option value="2026" selected>2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div id="dropZone" onclick="document.getElementById('fileInput').click()"
                            style="border: 2px dashed #94a3b8; padding: 2rem; text-align: center; border-radius: 0.5rem; cursor: pointer; transition: background 0.2s;">
                            <span style="font-size: 2rem;">📂</span>
                            <p style="margin-top: 1rem;" id="dropZoneText">Clique para selecionar ou arraste o arquivo
                                aqui</p>
                            <input type="file" style="display: none;" id="fileInput" accept=".csv, .xlsx">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('importExcelModal')">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="uploadImportFile()">Importar</button>
                    </div>
                </form>
            </div>

            <!-- Manage Parameters Modal -->
            <div id="manageParametersModal" class="card"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; width: 90%; max-width: 700px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);">
                <h3 class="mb-4">Gerenciar Parâmetros</h3>

                <!-- Tabs -->
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0;">
                    <button class="tab-btn active" onclick="switchTab('tabCostCenters')"
                        style="padding: 0.5rem 1rem; background: none; border: none; border-bottom: 2px solid var(--primary-color); font-weight: 600; color: var(--primary-color); cursor: pointer;">Centros
                        de Custo</button>
                    <button class="tab-btn" onclick="switchTab('tabCategories')"
                        style="padding: 0.5rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-secondary); cursor: pointer;">Categorias</button>
                    <button class="tab-btn" onclick="switchTab('tabNatures')"
                        style="padding: 0.5rem 1rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-secondary); cursor: pointer;">Naturezas</button>
                </div>

                <!-- Tab: Cost Centers -->
                <div id="tabCostCenters" class="tab-content">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <input type="text" id="newCostCenterName" class="form-control"
                            placeholder="Novo Centro de Custo">
                        <input type="text" id="newCostCenterCode" class="form-control" placeholder="Código (Ex: CC01)"
                            style="width: 150px;">
                        <button class="btn btn-primary" onclick="createCostCenter()">Adicionar</button>
                    </div>
                    <div id="costCentersList"
                        style="max-height: 250px; overflow-y: auto; background: #f8fafc; border-radius: 0.5rem; padding: 1rem;">
                        <!-- Content loaded via JS -->
                        <p class="text-center text-muted">Carregando...</p>
                    </div>
                </div>

                <!-- Tab: Categories (Hidden by default) -->
                <div id="tabCategories" class="tab-content" style="display: none;">
                    <form style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <input type="text" class="form-control" placeholder="Nova Categoria">
                        <button type="button" class="btn btn-primary"
                            onclick="alert('Categoria adicionada!')">Adicionar</button>
                    </form>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table style="width: 100%;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.5rem;">Marketing</td>
                                <td style="text-align: right;">
                                    <button style="border:none; background:none; cursor:pointer;">✏️</button>
                                    <button
                                        style="color:red; background:none; border:none; cursor:pointer;">🗑️</button>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.5rem;">Pessoal</td>
                                <td style="text-align: right;">
                                    <button style="border:none; background:none; cursor:pointer;">✏️</button>
                                    <button
                                        style="color:red; background:none; border:none; cursor:pointer;">🗑️</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Tab: Natures (Hidden by default) -->
                <div id="tabNatures" class="tab-content" style="display: none;">
                    <p class="text-secondary mb-4">Naturezas definem o comportamento da despesa na análise ZBB.</p>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem;">
                        <div class="form-group">
                            <label class="form-label">Natureza 1</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" class="form-control" value="Fixa">
                                <button class="btn btn-secondary">Renomear</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Natureza 2</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" class="form-control" value="Variável">
                                <button class="btn btn-secondary">Renomear</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Natureza 3</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" class="form-control" value="Discricionária">
                                <button class="btn btn-secondary">Renomear</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('manageParametersModal')">Fechar</button>
                </div>
            </div>

            <script>
                // Tab Switching Logic
                function switchTab(tabId) {
                    // Hide all contents
                    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
                    // Show selected
                    document.getElementById(tabId).style.display = 'block';

                    // Update buttons style
                    const tabs = ['tabCostCenters', 'tabCategories', 'tabNatures'];
                    const btns = document.querySelectorAll('.tab-btn');
                    tabs.forEach((t, index) => {
                        // Reset
                        btns[index].classList.remove('active');
                        btns[index].style.borderBottom = '2px solid transparent';
                        btns[index].style.color = 'var(--text-secondary)';

                        // Set active
                        if (t === tabId) {
                            btns[index].classList.add('active');
                            btns[index].style.borderBottom = '2px solid var(--primary-color)';
                            btns[index].style.color = 'var(--primary-color)';
                        }
                    });

                    // Load data if switching to Cost Centers
                    if (tabId === 'tabCostCenters') {
                        loadCostCenters();
                    }
                }

                // AJAX: Load Cost Centers
                function loadCostCenters() {
                    const listContainer = document.getElementById('costCentersList');
                    listContainer.innerHTML = '<p class="text-muted" style="padding:1rem;">Carregando...</p>';

                    fetch('api/manage_parameters.php?action=list_cost_centers')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.data.length === 0) {
                                    listContainer.innerHTML = '<p class="text-muted" style="padding:1rem;">Nenhum centro de custo encontrado.</p>';
                                    return;
                                }

                                let html = '<table style="width: 100%;">';
                                data.data.forEach(cc => {
                                    html += `
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 0.75rem;">
                                            <strong>${cc.name}</strong> 
                                            <span style="font-size: 0.8rem; color: #64748b;">(${cc.code || 'S/C'})</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="btn btn-secondary btn-sm" onclick="editCostCenter(${cc.id}, '${cc.name}', '${cc.code}')" style="padding: 0.2rem 0.5rem;">✏️</button>
                                            <button class="btn btn-secondary btn-sm" onclick="deleteCostCenter(${cc.id})" style="padding: 0.2rem 0.5rem; color: #ef4444;">🗑️</button>
                                        </td>
                                    </tr>`;
                                });
                                html += '</table>';
                                listContainer.innerHTML = html;
                            } else {
                                listContainer.innerHTML = '<p class="text-danger">Erro ao carregar.</p>';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            listContainer.innerHTML = '<p class="text-danger">Erro de conexão.</p>';
                        });
                }

                // AJAX: Create Cost Center
                function createCostCenter() {
                    const nameInput = document.getElementById('newCostCenterName');
                    const codeInput = document.getElementById('newCostCenterCode');
                    const name = nameInput.value;
                    const code = codeInput.value;

                    if (!name) {
                        alert('Nome é obrigatório');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'create_cost_center');
                    formData.append('name', name);
                    formData.append('code', code);

                    fetch('api/manage_parameters.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                nameInput.value = '';
                                codeInput.value = '';
                                loadCostCenters(); // Reload list
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(err => alert('Erro de conexão'));
                }

                // AJAX: Delete Cost Center
                function deleteCostCenter(id) {
                    if (!confirm('Tem certeza que deseja remover este centro de custo?')) return;

                    const formData = new FormData();
                    formData.append('action', 'delete_cost_center');
                    formData.append('id', id);

                    fetch('api/manage_parameters.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadCostCenters();
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(e => alert('Erro de conexão'));
                }

                // AJAX: Update Cost Center (Edit Mode)
                function editCostCenter(id, oldName, oldCode) {
                    const newName = prompt("Editar Nome do Centro de Custo:", oldName);
                    if (newName === null) return; // Cancelled

                    const newCode = prompt("Editar Código:", oldCode);
                    if (newCode === null) return; // Cancelled

                    const formData = new FormData();
                    formData.append('action', 'update_cost_center');
                    formData.append('id', id);
                    formData.append('name', newName);
                    formData.append('code', newCode);

                    fetch('api/manage_parameters.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert('Atualizado!');
                                loadCostCenters();
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(e => alert('Erro de conexão'));
                }

                function uploadImportFile() {
                    const fileInput = document.getElementById('fileInput');
                    if (fileInput.files.length === 0) {
                        alert('Selecione um arquivo CSV primeiro.');
                        return;
                    }

                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('month', document.getElementById('importMonth').value);
                    formData.append('year', document.getElementById('importYear').value);

                    // Show loading state
                    const btn = document.querySelector('#importExcelModal .btn-primary');
                    const originalText = btn.innerText;
                    btn.innerText = 'Enviando...';
                    btn.disabled = true;

                    fetch('api/import_budget.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let msg = data.message;
                                if (data.details && data.details.errors > 0 && data.details.error_messages) {
                                    msg += "\n\nDetalhes dos erros:\n" + data.details.error_messages.join("\n");
                                }
                                alert(msg);
                                if (data.details && data.details.imported > 0) {
                                    closeModal('importExcelModal');
                                    window.location.reload();
                                }
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro ao enviar o arquivo.');
                        })
                        .finally(() => {
                            btn.innerText = originalText;
                            btn.disabled = false;
                        });
                }

                // Initial Load if needed
                document.addEventListener('DOMContentLoaded', () => {
                    // If needed to pre-load something
                });

                // JS for Details & Analysis Modal
                function openDetailsModal(id) {
                    // Reset fields
                    document.getElementById('modalBudgetId').value = id;
                    document.getElementById('modalCostCenter').innerText = 'Carregando...';
                    document.getElementById('modalCategory').innerText = '-';
                    document.getElementById('modalRefVal').innerText = '-';
                    document.getElementById('modalCurVal').innerText = '-';
                    document.getElementById('modalJustification').value = '';
                    document.getElementById('modalStatusBadge').innerText = '...';

                    openModal('detailsModal');

                    // Fetch data
                    fetch(`api/analyze_budget.php?action=get_details&id=${id}`)
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                const d = res.data;
                                document.getElementById('modalCostCenter').innerText = d.cost_center;
                                document.getElementById('modalCategory').innerText = d.category;

                                // Format currency
                                const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
                                document.getElementById('modalRefVal').innerText = fmt.format(d.amount_budgeted);
                                document.getElementById('modalCurVal').innerText = fmt.format(d.amount_realized);

                                document.getElementById('modalJustification').value = d.justification || '';

                                // Status Badge
                                const badge = document.getElementById('modalStatusBadge');
                                badge.innerText = d.status === 'approved' ? 'Aprovado' : (d.status === 'pending' ? 'Pendente' : 'Rascunho');
                                badge.style.background = d.status === 'approved' ? '#dcfce7' : (d.status === 'pending' ? '#fef9c3' : '#f1f5f9');
                                badge.style.color = d.status === 'approved' ? '#166534' : (d.status === 'pending' ? '#854d0e' : '#475569');
                            } else {
                                alert('Erro ao carregar detalhes: ' + res.message);
                            }
                        });
                }

                function generateAIAnalysis() {
                    const id = document.getElementById('modalBudgetId').value;
                    const context = document.getElementById('modalJustification').value;
                    const loading = document.getElementById('aiLoading');

                    if (!id) return;

                    loading.style.display = 'block';

                    const formData = new FormData();
                    formData.append('action', 'get_ai_analysis');
                    formData.append('budget_id', id);
                    formData.append('context', context);

                    fetch('api/analyze_budget.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(res => {
                            loading.style.display = 'none';
                            if (res.success) {
                                // Append or Replace? Let's append if text exists
                                const textArea = document.getElementById('modalJustification');
                                if (textArea.value.trim() !== '') {
                                    textArea.value += "\n\n--- Sugestão da IA ---\n" + res.analysis;
                                } else {
                                    textArea.value = res.analysis;
                                }
                            } else {
                                alert('Erro na IA: ' + res.message);
                            }
                        })
                        .catch(err => {
                            loading.style.display = 'none';
                            alert('Erro ao conectar com a IA.');
                        });
                }

                function saveAnalysis(status) {
                    const id = document.getElementById('modalBudgetId').value;
                    const justification = document.getElementById('modalJustification').value;

                    if (!id) return;

                    const formData = new FormData();
                    formData.append('action', 'update_status');
                    formData.append('budget_id', id);
                    formData.append('status', status);
                    formData.append('justification', justification);

                    fetch('api/analyze_budget.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                alert('Atualizado com sucesso!');
                                closeModal('detailsModal');
                                window.location.reload();
                            } else {
                                alert('Erro ao salvar: ' + res.message);
                            }
                        });
                }

                /* End of JS functions, closing script first */
            </script>

            <!-- Details & Analysis Modal -->
            <div id="detailsModal" class="card"
                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; width: 90%; max-width: 600px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Análise de Pacote</h3>
                    <span id="modalStatusBadge"
                        style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; background: #eee;">Status</span>
                </div>

                <input type="hidden" id="modalBudgetId">

                <div
                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 0.5rem;">
                    <div>
                        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem;">Centro de Custo</p>
                        <strong id="modalCostCenter">-</strong>
                    </div>
                    <div>
                        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem;">Categoria</p>
                        <strong id="modalCategory">-</strong>
                    </div>
                    <div>
                        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem;">Realizado (Ref)</p>
                        <strong id="modalRefVal">-</strong>
                    </div>
                    <div>
                        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem;">Realizado (Atual)</p>
                        <strong id="modalCurVal" style="font-size: 1.1rem;">-</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        Análise / Justificativa
                        <button type="button" onclick="generateAIAnalysis()"
                            style="float: right; border: none; background: none; color: var(--primary-color); cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            ✨ Gerar com IA
                        </button>
                    </label>
                    <textarea id="modalJustification" class="form-control" rows="5"
                        placeholder="Insira a defesa técnica para este gasto..."></textarea>
                    <p id="aiLoading"
                        style="display: none; color: var(--primary-color); font-size: 0.85rem; margin-top: 0.5rem;">🤖 A
                        IA está analisando os dados...</p>
                </div>

                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('detailsModal')">Cancelar</button>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-primary" onclick="saveAnalysis('pending')"
                            style="background: var(--warning-color); border-color: var(--warning-color);">Salvar como
                            Pendente</button>
                        <button type="button" class="btn btn-primary" onclick="saveAnalysis('approved')"
                            style="background: var(--success-color); border-color: var(--success-color);">Aprovar</button>
                    </div>
                </div>
            </div>

            <!-- Overlay -->
            <div id="modalOverlay"
                style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"
                onclick="closeAllModals()"></div>

            <script>
                function openModal(modalId) {
                    if (document.getElementById(modalId)) {
                        document.getElementById(modalId).style.display = 'block';
                        document.getElementById('modalOverlay').style.display = 'block';
                    } else {
                        console.error('Modal not found: ' + modalId);
                    }
                }

                function closeModal(modalId) {
                    if (document.getElementById(modalId))
                        document.getElementById(modalId).style.display = 'none';
                    document.getElementById('modalOverlay').style.display = 'none';
                }

                function closeAllModals() {
                    document.querySelectorAll('[id$="Modal"]').forEach(el => el.style.display = 'none');
                    document.getElementById('modalOverlay').style.display = 'none';
                }


                // Attach events to existing buttons (quick fix script)
                document.addEventListener('DOMContentLoaded', () => {
                    // New Package Button
                    const newPkgBtn = document.querySelector('button.btn-primary'); // The one in header
                    if (newPkgBtn && newPkgBtn.innerText.includes('Novo Pacote')) {
                        newPkgBtn.onclick = () => openModal('newPackageModal');
                    }

                    // Justify Buttons
                    const justifyBtns = document.querySelectorAll('button.btn-secondary');
                    justifyBtns.forEach(btn => {
                        if (btn.innerText.includes('Justificar')) {
                            btn.onclick = () => openModal('justificationModal');
                        }
                    });
                });

                // Initialize Drag and Drop when DOM is ready
                document.addEventListener('DOMContentLoaded', () => {
                    const dropZone = document.getElementById('dropZone');
                    const fileInput = document.getElementById('fileInput');
                    const dropZoneText = document.getElementById('dropZoneText');

                    if (dropZone && fileInput) {
                        dropZone.addEventListener('click', () => {
                            fileInput.click();
                        });

                        fileInput.addEventListener('change', () => {
                            if (fileInput.files.length > 0) {
                                dropZoneText.innerText = 'Arquivo selecionado: ' + fileInput.files[0].name;
                                dropZone.style.background = '#e2e8f0';
                            }
                        });

                        dropZone.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            dropZone.style.background = '#f1f5f9';
                            dropZone.style.borderColor = 'var(--primary-color)';
                        });

                        dropZone.addEventListener('dragleave', (e) => {
                            e.preventDefault();
                            dropZone.style.background = 'transparent';
                            dropZone.style.borderColor = '#94a3b8';
                        });

                        dropZone.addEventListener('drop', (e) => {
                            e.preventDefault();
                            dropZone.style.background = '#e2e8f0';
                            dropZone.style.borderColor = '#94a3b8';

                            if (e.dataTransfer.files.length > 0) {
                                fileInput.files = e.dataTransfer.files;
                                dropZoneText.innerText = 'Arquivo selecionado: ' + e.dataTransfer.files[0].name;
                            }
                        });
                    } else {
                        console.error("Dropzone elements not found");
                    }
                });

            </script>
</body>

</html>