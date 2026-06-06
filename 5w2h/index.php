<?php
// index.php
require_once 'includes/config.php';
checkLoggedIn();

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['role'];
$company_name = $_SESSION['company_name'] ?? 'Minha Empresa';
$initials = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema 5W2H</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">5W2H<span>.</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item active">
                    <i>📊</i> Dashboard
                </a>
                <?php if ($user_role === 'admin'): ?>
                    <a href="#" onclick="showAdminSection('users')" class="nav-item">
                        <i>👥</i> Usuários
                    </a>
                    <a href="#" onclick="showAdminSection('companies')" class="nav-item">
                        <i>🏢</i> Empresas
                    </a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item">
                    <i>🚪</i> Sair
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header-row">
                <div class="header-title">
                    <h1 id="page-title">Dashoard Estratégico</h1>
                    <p id="page-subtitle">
                        <?php echo $company_name; ?> • Planos 5W2H
                    </p>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name">
                            <?php echo $user_name; ?>
                        </div>
                        <div class="user-role">
                            <?php echo $user_role === 'admin' ? 'Administrador' : 'Gestor de Ações'; ?>
                        </div>
                    </div>
                    <div class="user-avatar">
                        <?php echo $initials; ?>
                    </div>
                </div>
            </header>

            <!-- KPI Summary -->
            <div id="dashboard-section">
                <div class="kpi-row">
                    <div class="kpi-card" style="--primary: #7C3AED;">
                        <div class="kpi-label">Total de Ações</div>
                        <div class="kpi-value" id="kpi-total">0</div>
                    </div>
                    <div class="kpi-card" style="--primary: #3B82F6;">
                        <div class="kpi-label">Em Andamento</div>
                        <div class="kpi-value" id="kpi-progress">0</div>
                    </div>
                    <div class="kpi-card" style="--primary: #16A34A;">
                        <div class="kpi-label">Concluídas</div>
                        <div class="kpi-value" id="kpi-done">0</div>
                    </div>
                    <div class="kpi-card" style="--primary: #DC2626;">
                        <div class="kpi-label">Atrasadas</div>
                        <div class="kpi-value" id="kpi-overdue">0</div>
                    </div>
                </div>

                <!-- Board controls -->
                <div class="header-row" style="margin-bottom: 20px;">
                    <div>
                        <button class="btn btn-primary" onclick="openModal('action-modal')">+ Nova Ação</button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select onchange="filterActions(this.value)"
                            style="padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="all">Todas Categorias</option>
                            <option value="proftest">ProfTest</option>
                            <option value="consultoria">Consultoria MAIA</option>
                            <!-- other options ... -->
                        </select>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="kanban-board" id="kanban-board">
                    <div class="kanban-column" id="col-pendente">
                        <div class="column-header">
                            <span class="column-title">○ PENDENTE</span>
                            <span class="column-count" id="count-pendente">0</span>
                        </div>
                        <div class="column-body" id="body-pendente"></div>
                    </div>
                    <div class="kanban-column" id="col-andamento">
                        <div class="column-header">
                            <span class="column-title">◐ EM ANDAMENTO</span>
                            <span class="column-count" id="count-andamento">0</span>
                        </div>
                        <div class="column-body" id="body-andamento"></div>
                    </div>
                    <div class="kanban-column" id="col-concluida">
                        <div class="column-header">
                            <span class="column-title">● CONCLUÍDA</span>
                            <span class="column-count" id="count-concluida">0</span>
                        </div>
                        <div class="column-body" id="body-concluida"></div>
                    </div>
                    <div class="kanban-column" id="col-atrasada">
                        <div class="column-header">
                            <span class="column-title">⚠ ATRASADA</span>
                            <span class="column-count" id="count-atrasada">0</span>
                        </div>
                        <div class="column-body" id="body-atrasada"></div>
                    </div>
                </div>
            </div>

            <!-- Admin Section Placeholder -->
            <div id="admin-section" style="display: none;">
                <div class="header-row">
                    <h2 id="admin-title">Gerenciamento</h2>
                    <button class="btn btn-primary" id="admin-add-btn">+ Adicionar</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="admin-table"
                        style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                        <thead style="background: #F8FAFC;">
                            <!-- Dynamic columns -->
                        </thead>
                        <tbody id="admin-tbody">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Action Add/Edit -->
    <div class="modal-overlay" id="action-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="action-modal-title">Nova Ação 5W2H</h2>
                <button onclick="closeModal('action-modal')"
                    style="background:none; border:none; cursor:pointer; font-size:20px;">✕</button>
            </div>
            <div class="modal-body">
                <form id="action-form">
                    <input type="hidden" id="action-id">
                    <div class="form-field">
                        <label>O QUE? (WHAT)</label>
                        <input type="text" id="action-what" placeholder="Ação a ser executada" required>
                    </div>
                    <div class="form-field">
                        <label>POR QUÊ? (WHY)</label>
                        <textarea id="action-why" placeholder="Justificativa da ação"></textarea>
                    </div>
                    <div class="grid-2">
                        <div class="form-field">
                            <label>ONDE? (WHERE)</label>
                            <input type="text" id="action-where">
                        </div>
                        <div class="form-field">
                            <label>QUEM? (WHO)</label>
                            <input type="text" id="action-who">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-field">
                            <label>QUANDO INÍCIO?</label>
                            <input type="date" id="action-when-start">
                        </div>
                        <div class="form-field">
                            <label>QUANDO FIM?</label>
                            <input type="date" id="action-when-end">
                        </div>
                    </div>
                    <div class="form-field">
                        <label>COMO? (HOW)</label>
                        <textarea id="action-how" style="min-height: 60px;"></textarea>
                    </div>
                    <div class="grid-2">
                        <div class="form-field">
                            <label>QUANTO CUSTA? (HOW MUCH)</label>
                            <input type="text" id="action-how-much">
                        </div>
                        <div class="form-field">
                            <label>CATEGORIA</label>
                            <select id="action-category">
                                <option value="consultoria">Consultoria MAIA</option>
                                <option value="proftest">ProfTest</option>
                                <option value="workshops">Workshops</option>
                                <option value="portais">Portais Locais</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-field">
                            <label>PRIORIDADE</label>
                            <select id="action-priority">
                                <option value="critica">Crítica</option>
                                <option value="alta">Alta</option>
                                <option value="media">Média</option>
                                <option value="baixa">Baixa</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>STATUS</label>
                            <select id="action-status">
                                <option value="pendente">Pendente</option>
                                <option value="andamento">Em Andamento</option>
                                <option value="concluida">Concluída</option>
                                <option value="atrasada">Atrasada</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-field">
                        <label id="progress-label">PROGRESSO: 0%</label>
                        <input type="range" id="action-progress" min="0" max="100" step="5"
                            oninput="document.getElementById('progress-label').innerText = 'PROGRESSO: ' + this.value + '%'">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" style="background:var(--border-color);"
                    onclick="closeModal('action-modal')">Cancelar</button>
                <button class="btn btn-primary" onclick="handleSaveAction()">Salvar Ação</button>
            </div>
        </div>
    </div>

    <!-- Modal: View Details (DetailView) -->
    <div class="modal-overlay" id="detail-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="detail-title">Detalhes da Ação</h2>
                <button onclick="closeModal('detail-modal')"
                    style="background:none; border:none; cursor:pointer; font-size:20px;">✕</button>
            </div>
            <div class="modal-body" id="detail-body">
                <!-- Dynamic detail info -->
            </div>
            <div class="modal-footer">
                <button class="btn" style="color:var(--danger); background:#fee2e2;"
                    id="detail-delete-btn">Excluir</button>
                <button class="btn btn-primary" id="detail-edit-btn">Editar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Admin Add/Edit -->
    <div class="modal-overlay" id="admin-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="admin-modal-title">Admin</h2>
                <button onclick="closeModal('admin-modal')"
                    style="background:none; border:none; cursor:pointer; font-size:20px;">✕</button>
            </div>
            <div class="modal-body" id="admin-modal-form">
                <!-- User or Company form -->
            </div>
            <div class="modal-footer">
                <button class="btn" style="background:var(--border-color);"
                    onclick="closeModal('admin-modal')">Cancelar</button>
                <button class="btn btn-primary" id="admin-save-btn">Salvar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>

</html>