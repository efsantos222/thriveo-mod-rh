let processedData = null;

// DOM elements
const fileInput = document.getElementById('fileInput');
const uploadSection = document.getElementById('uploadSection');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');
const loading = document.getElementById('loading');
const resultsSection = document.getElementById('resultsSection');
const statsGrid = document.getElementById('statsGrid');
const resultsGrid = document.getElementById('resultsGrid');

// Initialize upload icon with visual feedback
document.addEventListener('DOMContentLoaded', function() {
    const iconContainer = document.querySelector('.icon-container');
    if (iconContainer) {
        // Add a subtle pulse animation to draw attention
        iconContainer.style.animation = 'pulse 2s infinite';
    }
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);

// Event listeners
fileInput.addEventListener('change', handleFileSelect);
uploadSection.addEventListener('dragover', handleDragOver);
uploadSection.addEventListener('dragleave', handleDragLeave);
uploadSection.addEventListener('drop', handleDrop);

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        uploadFile(file);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    uploadSection.classList.add('dragover');
}

function handleDragLeave(event) {
    event.preventDefault();
    uploadSection.classList.remove('dragover');
}

function handleDrop(event) {
    event.preventDefault();
    uploadSection.classList.remove('dragover');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        uploadFile(files[0]);
    }
}

function showMessage(message, type) {
    hideMessages();
    
    if (type === 'error') {
        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
    } else if (type === 'success') {
        successMessage.textContent = message;
        successMessage.classList.remove('d-none');
    }
}

function hideMessages() {
    errorMessage.classList.add('d-none');
    successMessage.classList.add('d-none');
}

function showLoading() {
    loading.classList.remove('d-none');
}

function hideLoading() {
    loading.classList.add('d-none');
}

async function uploadFile(file) {
    if (!file.name.match(/\.(xlsx|xls)$/)) {
        showMessage('Por favor, selecione um arquivo Excel (.xlsx ou .xls)', 'error');
        return;
    }

    hideMessages();
    showLoading();

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('/upload', {
            method: 'POST',
            body: formData
        });

        // Check if response is HTML (error page) instead of JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/html')) {
            throw new Error('Servidor retornou uma página de erro. Tente novamente em alguns momentos.');
        }

        const result = await response.json();

        if (response.ok && result.success) {
            processedData = result.data;
            displayResults();
            showMessage(result.message, 'success');
        } else {
            showMessage(result.error || 'Erro ao processar arquivo', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        let errorMessage = 'Erro de conexão';
        
        if (error.message.includes('página de erro')) {
            errorMessage = 'O servidor está processando a solicitação. Tente novamente em alguns segundos.';
        } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
            errorMessage = 'Erro de rede. Verifique sua conexão com a internet.';
        } else {
            errorMessage = error.message || 'Erro desconhecido';
        }
        
        showMessage(errorMessage, 'error');
    } finally {
        hideLoading();
    }
}

function displayResults() {
    if (!processedData) return;

    // Display statistics
    displayStatistics(processedData.statistics);
    
    // Display team analysis if available
    if (processedData.team_analysis) {
        displayTeamAnalysis(processedData.team_analysis);
    }
    
    // Display individual results
    displayIndividuals(processedData.individuals);
    
    // Show results section
    resultsSection.classList.remove('d-none');
    
    // Initialize tab functionality
    initializeTabs();
}

function displayTeamAnalysis(teamAnalysis) {
    const teamSection = document.createElement('div');
    teamSection.className = 'team-analysis-section';
    teamSection.innerHTML = `
        <div class="team-analysis-card">
            <h4><i class="fas fa-users"></i> Análise da Dinâmica de Equipe</h4>
            
            <div class="team-insights">
                <div class="insight-item">
                    <h6>🏆 Pontos Fortes da Equipe</h6>
                    <ul>
                        ${teamAnalysis.pontos_fortes_equipe?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                    </ul>
                </div>
                
                <div class="insight-item">
                    <h6>⚠️ Desafios Potenciais</h6>
                    <ul>
                        ${teamAnalysis.desafios_potenciais?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                    </ul>
                </div>
                
                <div class="insight-item">
                    <h6>💡 Recomendações de Gestão</h6>
                    <ul>
                        ${teamAnalysis.recomendacoes_gestao?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                    </ul>
                </div>
                
                <div class="insight-item full-width">
                    <h6>🔄 Dinâmica Geral</h6>
                    <p>${teamAnalysis.dinamica_geral || 'Não disponível'}</p>
                </div>
            </div>
        </div>
    `;
    
    // Insert team analysis before individual results
    const resultsTitle = document.querySelector('#resultsSection h3');
    resultsTitle.parentNode.insertBefore(teamSection, resultsTitle);
}

function initializeTabs() {
    // Add event listeners to tab buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('tab-btn')) {
            const tabContainer = e.target.closest('.analysis-tabs');
            const targetTab = e.target.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes in this container
            tabContainer.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            tabContainer.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button
            e.target.classList.add('active');
            
            // Show corresponding tab pane
            const targetPane = tabContainer.querySelector(`#${targetTab}`);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        }
    });
}

function displayStatistics(statistics) {
    statsGrid.innerHTML = '';
    
    statistics.forEach(stat => {
        const statElement = document.createElement('div');
        statElement.className = 'stat-item';
        statElement.innerHTML = `
            <div class="stat-number">${stat.value}</div>
            <div class="stat-label">${stat.label}</div>
        `;
        statsGrid.appendChild(statElement);
    });
}

function displayIndividuals(individuals) {
    resultsGrid.innerHTML = '';
    
    individuals.forEach(person => {
        const personCard = document.createElement('div');
        personCard.className = 'person-card';
        
        const profilesHtml = Object.entries(person.scores)
            .map(([profile, score]) => `
                <div class="profile-item profile-${profile.toLowerCase()}">
                    <div class="profile-letter">${profile}</div>
                    <div class="profile-score">${score.toFixed(1)}%</div>
                </div>
            `).join('');
        
        // Create AI analysis section if available
        const aiAnalysisHtml = person.ai_analysis ? createAIAnalysisHTML(person.ai_analysis) : '';
        
        personCard.innerHTML = `
            <div class="person-name">${person.name}</div>
            <div class="disc-profiles">
                ${profilesHtml}
            </div>
            <div class="dominant-profile">
                Perfil Dominante: ${person.dominant_profile}
            </div>
            ${aiAnalysisHtml}
        `;
        
        resultsGrid.appendChild(personCard);
    });
}

function createAIAnalysisHTML(analysis) {
    if (!analysis) return '';
    
    return `
        <div class="ai-analysis-section">
            <div class="ai-header">
                <h5><i class="fas fa-brain"></i> Análise Comportamental IA</h5>
            </div>
            
            <div class="analysis-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="strengths">Pontos Fortes</button>
                    <button class="tab-btn" data-tab="improvements">Melhorias</button>
                    <button class="tab-btn" data-tab="development">Desenvolvimento</button>
                    <button class="tab-btn" data-tab="summary">Resumo</button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="strengths">
                        <h6>💪 Pontos Fortes</h6>
                        <ul>
                            ${analysis.pontos_fortes?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                        </ul>
                    </div>
                    
                    <div class="tab-pane" id="improvements">
                        <h6>🎯 Oportunidades de Melhoria</h6>
                        <ul>
                            ${analysis.oportunidades_melhoria?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                        </ul>
                    </div>
                    
                    <div class="tab-pane" id="development">
                        <h6>🚀 Áreas de Desenvolvimento</h6>
                        <ul>
                            ${analysis.areas_desenvolvimento?.map(item => `<li>${item}</li>`).join('') || '<li>Não disponível</li>'}
                        </ul>
                        <div class="additional-info">
                            <p><strong>Estilo de Comunicação:</strong> ${analysis.estilo_comunicacao || 'Não disponível'}</p>
                            <p><strong>Ambiente Ideal:</strong> ${analysis.ambiente_trabalho_ideal || 'Não disponível'}</p>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="summary">
                        <h6>📋 Resumo Executivo</h6>
                        <p>${analysis.resumo_executivo || 'Não disponível'}</p>
                        <div class="management-tips">
                            <p><strong>Dicas para Gestores:</strong></p>
                            <p>${analysis.dicas_gestao || 'Não disponível'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

async function exportToExcel() {
    if (!processedData) {
        showMessage('Nenhum dado para exportar', 'error');
        return;
    }

    try {
        const response = await fetch('/export/excel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(processedData)
        });

        if (response.ok) {
            const blob = await response.blob();
            downloadFile(blob, 'resultados_disc.xlsx');
            showMessage('Excel exportado com sucesso!', 'success');
        } else {
            const error = await response.json();
            showMessage(error.error || 'Erro ao exportar Excel', 'error');
        }
    } catch (error) {
        showMessage('Erro de conexão: ' + error.message, 'error');
        console.error('Export Excel error:', error);
    }
}

async function exportDetailed(format) {
    if (!processedData) {
        showMessage('Nenhum dado para exportar', 'error');
        return;
    }

    showLoading();

    try {
        const exportData = { ...processedData, format: format };
        const response = await fetch('/export/detailed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(exportData)
        });

        if (response.ok) {
            const blob = await response.blob();
            let filename;
            switch (format) {
                case 'excel':
                    filename = 'resultados_disc_detalhado.xlsx';
                    break;
                case 'pdf':
                    filename = 'resultados_disc_detalhado.pdf';
                    break;
                case 'csv':
                    filename = 'resultados_disc.csv';
                    break;
                default:
                    filename = 'resultados_disc_detalhado.xlsx';
            }
            
            downloadFile(blob, filename);
            showMessage(`Relatório ${format.toUpperCase()} exportado com sucesso!`, 'success');
        } else {
            const error = await response.json();
            showMessage(error.error || 'Erro ao exportar relatório', 'error');
        }
    } catch (error) {
        showMessage('Erro de conexão: ' + error.message, 'error');
        console.error('Export detailed error:', error);
    } finally {
        hideLoading();
    }
}

function downloadFile(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

async function exportToPDF() {
    if (!processedData) {
        showMessage('Nenhum dado para exportar', 'error');
        return;
    }

    try {
        const response = await fetch('/export/pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(processedData)
        });

        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'resultados_disc.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showMessage('PDF exportado com sucesso!', 'success');
        } else {
            const error = await response.json();
            showMessage(error.error || 'Erro ao exportar PDF', 'error');
        }
    } catch (error) {
        showMessage('Erro de conexão: ' + error.message, 'error');
        console.error('Export PDF error:', error);
    }
}
