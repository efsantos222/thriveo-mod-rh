// Função para carregar os feedbacks
async function loadFeedback() {
    try {
        const response = await fetch('../api/feedback/list.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erro ao carregar feedback');
        }

        const feedbackGrid = document.querySelector('.feedback-grid');
        
        if (!result.data || result.data.length === 0) {
            feedbackGrid.innerHTML = `
                <div class="no-feedback">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum feedback encontrado</p>
                </div>
            `;
            return;
        }
        
        feedbackGrid.innerHTML = result.data.map(feedback => `
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
                        ${new Date(feedback.created_at).toLocaleDateString('pt-BR')}
                    </div>
                </div>
                <div class="feedback-content">
                    <div class="feedback-section">
                        <h4>Categoria</h4>
                        <p>${feedback.category}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Tipo de Feedback</h4>
                        <p>${feedback.feedback_type}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Situação</h4>
                        <p>${feedback.situation}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Comportamento</h4>
                        <p>${feedback.behavior}</p>
                    </div>
                    <div class="feedback-section">
                        <h4>Impacto</h4>
                        <p>${feedback.impact}</p>
                    </div>
                    ${feedback.suggestion ? `
                        <div class="feedback-section">
                            <h4>Sugestão</h4>
                            <p>${feedback.suggestion}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Erro:', error);
        const feedbackGrid = document.querySelector('.feedback-grid');
        feedbackGrid.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>${error.message}</p>
            </div>
        `;
    }
}

// Função para abrir o modal de novo feedback
function openNewFeedbackModal() {
    // Implementar depois
    alert('Em desenvolvimento');
}

// Carregar feedbacks quando a página carregar
document.addEventListener('DOMContentLoaded', loadFeedback);
