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
    <title>Feedback - Sistema de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/feedback.css">
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
                <li><a href="../goals/index.php"><i class="fas fa-bullseye"></i> Metas SMART</a></li>
                <li><a href="#" class="active"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
        <main class="content">
            <header class="dashboard-header">
                <h1>Sistema de Feedback</h1>
                <button class="btn-primary" onclick="openNewFeedbackModal()">
                    <i class="fas fa-plus"></i> Novo Feedback
                </button>
            </header>
            
            <div class="feedback-container">
                <div class="feedback-filters">
                    <div class="search-box">
                        <input type="text" id="search-feedback" placeholder="Buscar feedback...">
                    </div>
                    <div class="filter-options">
                        <select id="type-filter">
                            <option value="">Todos os Tipos</option>
                            <option value="enviado">Enviados</option>
                            <option value="recebido">Recebidos</option>
                        </select>
                        <select id="category-filter">
                            <option value="">Todas as Categorias</option>
                            <option value="desempenho">Desempenho</option>
                            <option value="comportamento">Comportamento</option>
                            <option value="habilidades">Habilidades</option>
                            <option value="objetivos">Objetivos</option>
                        </select>
                    </div>
                </div>
                
                <div class="feedback-grid" id="feedback-grid">
                    <!-- Feedback items will be loaded here -->
                </div>
            </div>
            
            <!-- New Feedback Modal -->
            <div class="modal" id="feedback-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Novo Feedback</h2>
                        <button class="close-button" onclick="closeNewFeedbackModal()">&times;</button>
                    </div>
                    <form id="feedback-form">
                        <div class="form-group">
                            <label for="feedback-recipient">Destinatário</label>
                            <select id="feedback-recipient" name="recipient_id" required>
                                <!-- Recipients will be loaded here -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="feedback-category">Categoria</label>
                            <select id="feedback-category" name="category" required>
                                <option value="desempenho">Desempenho</option>
                                <option value="comportamento">Comportamento</option>
                                <option value="habilidades">Habilidades</option>
                                <option value="objetivos">Objetivos</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="feedback-type">Tipo de Feedback</label>
                            <select id="feedback-type" name="type" required>
                                <option value="positivo">Positivo</option>
                                <option value="construtivo">Construtivo</option>
                                <option value="sugestao">Sugestão</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Situação</label>
                            <textarea name="situation" placeholder="Descreva a situação específica" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Comportamento</label>
                            <textarea name="behavior" placeholder="Descreva o comportamento observado" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Impacto</label>
                            <textarea name="impact" placeholder="Descreva o impacto do comportamento" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Sugestão</label>
                            <textarea name="suggestion" placeholder="Ofereça sugestões construtivas para melhoria"></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="closeNewFeedbackModal()">Cancelar</button>
                            <button type="submit" class="btn-primary">Enviar Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Load feedback and recipients on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadFeedback();
        loadRecipients();
    });
    
    // Add event listeners for filters
    document.getElementById('search-feedback').addEventListener('input', filterFeedback);
    document.getElementById('type-filter').addEventListener('change', filterFeedback);
    document.getElementById('category-filter').addEventListener('change', filterFeedback);
    
    async function loadFeedback() {
        try {
            const response = await fetch('../api/feedback/list.php');
            const result = await response.json();
            
            if (result.success) {
                renderFeedback(result.data);
            } else {
                console.error('API Error:', result);
                alert('Erro ao carregar feedback: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro ao carregar feedback');
        }
    }
    
    async function loadRecipients() {
        try {
            const response = await fetch('../api/users/list.php');
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('feedback-recipient');
                select.innerHTML = '<option value="">Selecione o destinatário...</option>' + 
                    result.data.map(user => 
                        `<option value="${user.id_usuario}">${user.nome} (${user.perfil})</option>`
                    ).join('');
            } else {
                console.error('API Error:', result);
                alert('Erro ao carregar usuários: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro ao carregar usuários');
        }
    }
    
    function renderFeedback(feedbackList) {
        const container = document.querySelector('.feedback-grid');
        
        if (!feedbackList || feedbackList.length === 0) {
            container.innerHTML = `
                <div class="no-feedback">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum feedback encontrado</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = feedbackList.map(feedback => `
            <div class="feedback-card ${feedback.type}">
                <div class="feedback-header">
                    <div class="feedback-type">
                        <i class="fas fa-${feedback.type === 'enviado' ? 'paper-plane' : 'envelope'}"></i>
                        <span>${feedback.type === 'enviado' ? 'Enviado para' : 'Recebido de'}</span>
                    </div>
                    <div class="feedback-user">
                        ${feedback.type === 'enviado' ? feedback.to_name : feedback.from_name}
                        <span class="user-perfil ${feedback.type === 'enviado' ? feedback.to_perfil : feedback.from_perfil}">
                            ${feedback.type === 'enviado' ? feedback.to_perfil : feedback.from_perfil}
                        </span>
                    </div>
                    <div class="feedback-date">
                        ${new Date(feedback.data_criacao).toLocaleDateString('pt-BR')}
                    </div>
                </div>
                <div class="feedback-content">
                    <div class="feedback-section">
                        <h4>Categoria</h4>
                        <p>${feedback.categoria}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Tipo de Feedback</h4>
                        <p>${feedback.tipo_feedback}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Situação</h4>
                        <p>${feedback.situacao}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Comportamento</h4>
                        <p>${feedback.comportamento}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Impacto</h4>
                        <p>${feedback.impacto}</p>
                    </div>
                    ${feedback.sugestao ? `
                        <div class="feedback-section">
                            <h4>Sugestão</h4>
                            <p>${feedback.sugestao}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
    
    function filterFeedback() {
        const searchTerm = document.getElementById('search-feedback').value.toLowerCase();
        const typeFilter = document.getElementById('type-filter').value;
        const categoryFilter = document.getElementById('category-filter').value;
        
        const feedbackItems = document.querySelectorAll('.feedback-card');
        
        feedbackItems.forEach(item => {
            const content = item.textContent.toLowerCase();
            const type = item.classList.contains('enviado') ? 'enviado' : 'recebido';
            const category = item.querySelector('.feedback-category').textContent.toLowerCase();
            
            const matchesSearch = content.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesCategory = !categoryFilter || category === categoryFilter;
            
            item.style.display = matchesSearch && matchesType && matchesCategory ? 'block' : 'none';
        });
    }
    
    // Modal functions
    function openNewFeedbackModal() {
        document.getElementById('feedback-modal').style.display = 'block';
    }
    
    function closeNewFeedbackModal() {
        document.getElementById('feedback-modal').style.display = 'none';
        document.getElementById('feedback-form').reset();
    }
    
    // Form submission
    document.getElementById('feedback-form').onsubmit = async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            recipient_id: formData.get('recipient_id'),
            category: formData.get('category'),
            feedback_type: formData.get('type'),
            situation: formData.get('situation'),
            behavior: formData.get('behavior'),
            impact: formData.get('impact'),
            suggestion: formData.get('suggestion')
        };
        
        try {
            const response = await fetch('../api/feedback/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeNewFeedbackModal();
                loadFeedback();
            } else {
                alert(result.message || 'Erro ao criar feedback');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro ao criar feedback');
        }
    };
    </script>
</body>
</html>
