// ── Skeleton helpers ──────────────────────────────────────
function skeletonRows(count, hasSubline) {
    return Array(count).fill(0).map(() => `
        <div style="display:flex;flex-direction:column;gap:.4rem;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
            <div class="skeleton skeleton--title" style="width:55%;"></div>
            ${hasSubline ? '<div class="skeleton skeleton--text" style="width:35%;"></div>' : ''}
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', function() {
    // Show skeletons immediately
    document.getElementById('upcoming-sessions').innerHTML = skeletonRows(3, true);
    document.getElementById('smart-goals').innerHTML      = skeletonRows(3, true);
    document.getElementById('recent-feedback').innerHTML  = skeletonRows(3, false);

    // Carregar próximas sessões
    fetch('api/sessions/upcoming.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('upcoming-sessions');
            if (data.length === 0) {
                container.innerHTML = '<p>Nenhuma sessão agendada.</p>';
                return;
            }

            const sessionsList = data.map(session => `
                <div class="session-item">
                    <div class="session-date">${formatDate(session.data_sessao)}</div>
                    <div class="session-time">${session.hora_inicio} - ${session.hora_fim}</div>
                    <div class="session-type">${session.tipo}</div>
                    <div class="session-location">${session.local}</div>
                </div>
            `).join('');

            container.innerHTML = sessionsList;
        })
        .catch(error => {
            document.getElementById('upcoming-sessions').innerHTML =
                '<p class="error">Erro ao carregar as sessões.</p>';
        });

    // Carregar metas SMART
    fetch('api/goals/list.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('smart-goals');
            if (data.length === 0) {
                container.innerHTML = '<p>Nenhuma meta cadastrada.</p>';
                return;
            }

            const goalsList = data.map(goal => `
                <div class="goal-item">
                    <div class="goal-title">${goal.titulo}</div>
                    <div class="goal-progress">
                        <div class="progress-bar">
                            <div class="progress" style="width: ${goal.progresso}%"></div>
                        </div>
                        <span>${goal.progresso}%</span>
                    </div>
                    <div class="goal-date">Prazo: ${formatDate(goal.data_fim)}</div>
                </div>
            `).join('');

            container.innerHTML = goalsList;
        })
        .catch(error => {
            document.getElementById('smart-goals').innerHTML =
                '<p class="error">Erro ao carregar as metas.</p>';
        });

    // Carregar feedback recente
    fetch('api/feedback/list.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-feedback');
            if (data.length === 0) {
                container.innerHTML = '<p>Nenhum feedback recebido.</p>';
                return;
            }

            const feedbackList = data.map(feedback => `
                <div class="feedback-item">
                    <div class="feedback-meta">
                        <span class="feedback-type">${feedback.tipo_feedback}</span>
                        <span class="feedback-date">${formatDate(feedback.data_criacao)}</span>
                    </div>
                    <div class="feedback-content">${feedback.situacao}</div>
                </div>
            `).join('');

            container.innerHTML = feedbackList;
        })
        .catch(error => {
            document.getElementById('recent-feedback').innerHTML =
                '<p class="error">Erro ao carregar os feedbacks.</p>';
        });
});

// Função auxiliar para formatar datas
function formatDate(dateString) {
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
}
