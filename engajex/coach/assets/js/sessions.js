document.addEventListener('DOMContentLoaded', function() {
    loadUpcomingSessions();
    loadPastSessions();
    loadParticipants();
    setupModalHandlers();
    setupFormHandlers();
});

// Modal handlers
function setupModalHandlers() {
    const modal = document.getElementById('new-session-modal');
    const closeBtn = modal.querySelector('.close');
    
    window.openNewSessionModal = function() {
        modal.style.display = 'block';
    }
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}

// Form handlers
function setupFormHandlers() {
    const form = document.getElementById('new-session-form');
    
    form.onsubmit = async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = {
            type: formData.get('type'),
            date: formData.get('date'),
            time_start: formData.get('time_start'),
            time_end: formData.get('time_end'),
            location: formData.get('location'),
            participants: Array.from(formData.getAll('participants[]')),
            notes: formData.get('notes')
        };
        
        try {
            const response = await fetch('../api/sessions/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('new-session-modal').style.display = 'none';
                form.reset();
                loadUpcomingSessions();
                showNotification('Sessão agendada com sucesso!', 'success');
            } else {
                showNotification(result.message || 'Erro ao agendar sessão', 'error');
            }
        } catch (error) {
            showNotification('Erro ao agendar sessão', 'error');
            console.error('Error:', error);
        }
    }
}

// Load sessions
async function loadUpcomingSessions() {
    const container = document.getElementById('upcoming-sessions');
    try {
        const response = await fetch('../api/sessions/upcoming.php');
        const sessions = await response.json();
        
        if (sessions.length === 0) {
            container.innerHTML = '<p class="no-data">Nenhuma sessão agendada</p>';
            return;
        }
        
        container.innerHTML = sessions.map(session => createSessionHTML(session)).join('');
    } catch (error) {
        container.innerHTML = '<p class="error">Erro ao carregar sessões</p>';
        console.error('Error:', error);
    }
}

async function loadPastSessions() {
    const container = document.getElementById('past-sessions');
    try {
        const response = await fetch('../api/sessions/past.php');
        const sessions = await response.json();
        
        if (sessions.length === 0) {
            container.innerHTML = '<p class="no-data">Nenhuma sessão anterior</p>';
            return;
        }
        
        container.innerHTML = sessions.map(session => createSessionHTML(session)).join('');
    } catch (error) {
        container.innerHTML = '<p class="error">Erro ao carregar sessões</p>';
        console.error('Error:', error);
    }
}

// Load participants for select
async function loadParticipants() {
    const select = document.getElementById('session-participants');
    try {
        const response = await fetch('../api/users/list.php');
        const users = await response.json();
        
        select.innerHTML = users.map(user => 
            `<option value="${user.id_usuario}">${user.nome}</option>`
        ).join('');
    } catch (error) {
        console.error('Error loading participants:', error);
    }
}

// Helper functions
function createSessionHTML(session) {
    return `
        <div class="session-item">
            <div class="session-header">
                <span class="session-date">${formatDate(session.data_sessao)}</span>
                <span class="session-type ${session.tipo}">${session.tipo}</span>
            </div>
            <div class="session-time">
                ${session.hora_inicio} - ${session.hora_fim}
            </div>
            <div class="session-location">
                ${session.local}
            </div>
            <div class="session-participants">
                Participantes: ${session.participantes}
            </div>
            <div class="session-actions">
                ${createSessionActions(session)}
            </div>
        </div>
    `;
}

function createSessionActions(session) {
    const actions = [];
    const now = new Date();
    const sessionDate = new Date(session.data_sessao + ' ' + session.hora_inicio);
    
    if (sessionDate > now) {
        actions.push(`<button onclick="editSession(${session.id_sessao})" class="btn-secondary">Editar</button>`);
        actions.push(`<button onclick="cancelSession(${session.id_sessao})" class="btn-danger">Cancelar</button>`);
    } else {
        actions.push(`<button onclick="viewSessionNotes(${session.id_sessao})" class="btn-secondary">Ver Anotações</button>`);
    }
    
    return actions.join('');
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function showNotification(message, type) {
    if (window.Toast) {
        Toast[type] ? Toast[type](message) : Toast.info(message);
    }
}

// Session actions
async function editSession(sessionId) {
    // Implement edit session functionality
    console.log('Edit session:', sessionId);
}

async function cancelSession(sessionId) {
    if (!confirm('Tem certeza que deseja cancelar esta sessão?')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/sessions/cancel.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: sessionId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadUpcomingSessions();
            showNotification('Sessão cancelada com sucesso!', 'success');
        } else {
            showNotification(result.message || 'Erro ao cancelar sessão', 'error');
        }
    } catch (error) {
        showNotification('Erro ao cancelar sessão', 'error');
        console.error('Error:', error);
    }
}

async function viewSessionNotes(sessionId) {
    // Implement view notes functionality
    console.log('View notes for session:', sessionId);
}
