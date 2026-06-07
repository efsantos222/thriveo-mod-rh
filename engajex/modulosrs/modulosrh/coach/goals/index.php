<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas SMART - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/goals.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <h3>Bem-vindo(a),</h3>
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <ul class="menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../users/index.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="../sessions/index.php"><i class="fas fa-calendar"></i> Sessões</a></li>
                <li><a href="../questionnaires/index.php"><i class="fas fa-clipboard-list"></i> Questionários</a></li>
                <li><a href="#" class="active"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="../feedback/index.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Metas SMART</h1>
                <button class="btn-primary" onclick="openNewGoalModal()">
                    <i class="fas fa-plus"></i> Nova Meta
                </button>
            </header>
            
            <div class="goals-container">
                <div class="goals-filters">
                    <div class="search-box">
                        <input type="text" id="search-goals" placeholder="Buscar metas...">
                    </div>
                    <div class="filter-options">
                        <select id="status-filter">
                            <option value="">Todos os Status</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluida">Concluída</option>
                            <option value="atrasada">Atrasada</option>
                        </select>
                        <select id="category-filter">
                            <option value="">Todas as Categorias</option>
                            <option value="pessoal">Pessoal</option>
                            <option value="profissional">Profissional</option>
                            <option value="saude">Saúde</option>
                            <option value="financeiro">Financeiro</option>
                        </select>
                    </div>
                </div>
                
                <div class="goals-grid" id="goals-grid">
                    <!-- Goals will be loaded here -->
                </div>
            </div>
            
            <!-- New Goal Modal -->
            <div class="modal" id="goal-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Nova Meta SMART</h2>
                        <button class="close-button" onclick="closeNewGoalModal()">&times;</button>
                    </div>
                    <form id="goal-form">
                        <div class="form-group">
                            <label for="goal-title">Título</label>
                            <input type="text" id="goal-title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="goal-category">Categoria</label>
                            <select id="goal-category" name="category" required>
                                <option value="pessoal">Pessoal</option>
                                <option value="profissional">Profissional</option>
                                <option value="saude">Saúde</option>
                                <option value="financeiro">Financeiro</option>
                            </select>
                        </div>
                        
                        <div class="smart-criteria">
                            <h3>Critérios SMART</h3>
                            
                            <div class="form-group">
                                <label>Específico (Specific)</label>
                                <textarea name="specific" placeholder="O que exatamente você quer alcançar?" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Mensurável (Measurable)</label>
                                <textarea name="measurable" placeholder="Como você vai medir o progresso?" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Atingível (Achievable)</label>
                                <textarea name="achievable" placeholder="Quais recursos e ações são necessários?" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Relevante (Relevant)</label>
                                <textarea name="relevant" placeholder="Por que esta meta é importante?" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Temporal (Time-bound)</label>
                                <div class="date-inputs">
                                    <div>
                                        <label for="start-date">Data de Início</label>
                                        <input type="date" id="start-date" name="start_date" required>
                                    </div>
                                    <div>
                                        <label for="end-date">Data de Conclusão</label>
                                        <input type="date" id="end-date" name="end_date" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="goal-milestones">Marcos (Milestones)</label>
                            <div id="milestones-container">
                                <div class="milestone-input">
                                    <input type="text" name="milestones[]" placeholder="Descreva um marco">
                                    <button type="button" class="btn-secondary" onclick="removeMilestone(this)">-</button>
                                </div>
                            </div>
                            <button type="button" class="btn-secondary" onclick="addMilestone()">+ Adicionar Marco</button>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="closeNewGoalModal()">Cancelar</button>
                            <button type="submit" class="btn-primary">Salvar Meta</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Load goals on page load
    document.addEventListener('DOMContentLoaded', loadGoals);
    
    // Add event listeners for filters
    document.getElementById('search-goals').addEventListener('input', filterGoals);
    document.getElementById('status-filter').addEventListener('change', filterGoals);
    document.getElementById('category-filter').addEventListener('change', filterGoals);
    
    async function loadGoals() {
        try {
            const response = await fetch('../api/goals/list.php');
            const data = await response.json();
            
            if (data.success) {
                renderGoals(data.goals);
            } else {
                alert('Erro ao carregar metas: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro ao carregar metas');
        }
    }
    
    function renderGoals(goals) {
        const container = document.getElementById('goals-grid');
        container.innerHTML = '';
        
        goals.forEach(goal => {
            const progress = calculateProgress(goal);
            const status = determineStatus(goal);
            
            container.innerHTML += `
                <div class="goal-card ${status}">
                    <div class="goal-header">
                        <span class="goal-category">${goal.category}</span>
                        <span class="goal-status">${status}</span>
                    </div>
                    <h3>${goal.title}</h3>
                    <div class="goal-progress">
                        <div class="progress-bar">
                            <div class="progress" style="width: ${progress}%"></div>
                        </div>
                        <span>${progress}%</span>
                    </div>
                    <div class="goal-dates">
                        <span>Início: ${formatDate(goal.start_date)}</span>
                        <span>Fim: ${formatDate(goal.end_date)}</span>
                    </div>
                    <div class="goal-actions">
                        <button onclick="viewGoalDetails(${goal.id})" class="btn-secondary">Detalhes</button>
                        <button onclick="updateGoalProgress(${goal.id})" class="btn-primary">Atualizar</button>
                    </div>
                </div>
            `;
        });
    }
    
    function calculateProgress(goal) {
        const totalMilestones = goal.milestones.length;
        const completedMilestones = goal.milestones.filter(m => m.completed).length;
        return totalMilestones > 0 ? Math.round((completedMilestones / totalMilestones) * 100) : 0;
    }
    
    function determineStatus(goal) {
        const now = new Date();
        const endDate = new Date(goal.end_date);
        const progress = calculateProgress(goal);
        
        if (progress === 100) return 'concluida';
        if (endDate < now) return 'atrasada';
        return 'em_andamento';
    }
    
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }
    
    function filterGoals() {
        const searchTerm = document.getElementById('search-goals').value.toLowerCase();
        const statusFilter = document.getElementById('status-filter').value;
        const categoryFilter = document.getElementById('category-filter').value;
        
        const goals = document.querySelectorAll('.goal-card');
        
        goals.forEach(goal => {
            const title = goal.querySelector('h3').textContent.toLowerCase();
            const status = goal.querySelector('.goal-status').textContent.toLowerCase();
            const category = goal.querySelector('.goal-category').textContent.toLowerCase();
            
            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesCategory = !categoryFilter || category === categoryFilter;
            
            goal.style.display = matchesSearch && matchesStatus && matchesCategory ? 'block' : 'none';
        });
    }
    
    // Modal functions
    function openNewGoalModal() {
        document.getElementById('goal-modal').style.display = 'block';
    }
    
    function closeNewGoalModal() {
        document.getElementById('goal-modal').style.display = 'none';
        document.getElementById('goal-form').reset();
    }
    
    function addMilestone() {
        const container = document.getElementById('milestones-container');
        const div = document.createElement('div');
        div.className = 'milestone-input';
        div.innerHTML = `
            <input type="text" name="milestones[]" placeholder="Descreva um marco">
            <button type="button" class="btn-secondary" onclick="removeMilestone(this)">-</button>
        `;
        container.appendChild(div);
    }
    
    function removeMilestone(button) {
        button.parentElement.remove();
    }
    
    // Form submission
    document.getElementById('goal-form').onsubmit = async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            title: formData.get('title'),
            category: formData.get('category'),
            smart: {
                specific: formData.get('specific'),
                measurable: formData.get('measurable'),
                achievable: formData.get('achievable'),
                relevant: formData.get('relevant'),
                time_bound: {
                    start_date: formData.get('start_date'),
                    end_date: formData.get('end_date')
                }
            },
            milestones: Array.from(formData.getAll('milestones[]')).filter(m => m.trim())
        };
        
        try {
            const response = await fetch('../api/goals/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeNewGoalModal();
                loadGoals();
            } else {
                alert(result.message || 'Erro ao criar meta');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro ao criar meta');
        }
    };
    </script>
</body>
</html>
