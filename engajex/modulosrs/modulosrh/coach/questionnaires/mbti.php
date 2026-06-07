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
    <title>Avaliação MBTI - Sistema de Coaching</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/questionnaires.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="content">
            <header class="dashboard-header">
                <h1>Avaliação MBTI</h1>
            </header>
            
            <div class="assessment-container">
                <div class="card">
                    <div class="assessment-intro">
                        <h2>Sobre o MBTI</h2>
                        <p>O Myers-Briggs Type Indicator (MBTI) avalia quatro dimensões da personalidade:</p>
                        <ul>
                            <li><strong>Energia:</strong> Extroversão (E) vs. Introversão (I)</li>
                            <li><strong>Informação:</strong> Sensação (S) vs. Intuição (N)</li>
                            <li><strong>Decisões:</strong> Pensamento (T) vs. Sentimento (F)</li>
                            <li><strong>Estilo de vida:</strong> Julgamento (J) vs. Percepção (P)</li>
                        </ul>
                        <p class="assessment-instructions">
                            A avaliação consiste em 70 questões. Para cada questão, escolha a opção que melhor descreve
                            como você geralmente se sente ou age. Não há respostas certas ou erradas.
                        </p>
                        <button class="btn-primary" onclick="startAssessment()">Iniciar Avaliação</button>
                    </div>
                </div>
                
                <form id="mbti-assessment" style="display: none;">
                    <div id="question-container">
                        <!-- Questions will be loaded here -->
                    </div>
                    
                    <div class="assessment-navigation">
                        <button type="button" class="btn-secondary" onclick="previousQuestion()" id="btn-previous" style="display: none;">Anterior</button>
                        <span id="question-progress">Questão 1 de 70</span>
                        <button type="button" class="btn-primary" onclick="nextQuestion()" id="btn-next">Próxima</button>
                        <button type="submit" class="btn-primary" id="btn-submit" style="display: none;">Finalizar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    const mbtiQuestions = [
        {
            id: 1,
            question: "Em uma situação social, você geralmente:",
            options: [
                { text: "Inicia conversas com outras pessoas", value: "E" },
                { text: "Espera que outros iniciem a conversa", value: "I" }
            ],
            dimension: "EI"
        },
        {
            id: 2,
            question: "Ao resolver problemas, você prefere:",
            options: [
                { text: "Usar métodos testados e comprovados", value: "S" },
                { text: "Tentar novas abordagens", value: "N" }
            ],
            dimension: "SN"
        },
        // Add more questions here
    ];
    
    let currentQuestion = 0;
    let answers = {};
    
    function startAssessment() {
        document.querySelector('.assessment-intro').style.display = 'none';
        document.getElementById('mbti-assessment').style.display = 'block';
        loadQuestion(0);
    }
    
    function loadQuestion(index) {
        const question = mbtiQuestions[index];
        const container = document.getElementById('question-container');
        
        container.innerHTML = `
            <div class="question-card">
                <div class="question-number">Questão ${index + 1} de ${mbtiQuestions.length}</div>
                <div class="question-text">${question.question}</div>
                <div class="options-grid">
                    ${question.options.map((option, i) => `
                        <label class="option-item ${answers[question.id] === option.value ? 'selected' : ''}">
                            <input type="radio" name="q${question.id}" value="${option.value}"
                                ${answers[question.id] === option.value ? 'checked' : ''}>
                            ${option.text}
                        </label>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Add click handlers for options
        const options = container.querySelectorAll('.option-item');
        options.forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        updateNavigation();
    }
    
    function updateNavigation() {
        document.getElementById('btn-previous').style.display = currentQuestion > 0 ? 'block' : 'none';
        document.getElementById('btn-next').style.display = currentQuestion < mbtiQuestions.length - 1 ? 'block' : 'none';
        document.getElementById('btn-submit').style.display = currentQuestion === mbtiQuestions.length - 1 ? 'block' : 'none';
        document.getElementById('question-progress').textContent = `Questão ${currentQuestion + 1} de ${mbtiQuestions.length}`;
    }
    
    function previousQuestion() {
        if (currentQuestion > 0) {
            saveCurrentAnswer();
            currentQuestion--;
            loadQuestion(currentQuestion);
        }
    }
    
    function nextQuestion() {
        if (validateCurrentAnswer()) {
            saveCurrentAnswer();
            currentQuestion++;
            loadQuestion(currentQuestion);
        } else {
            alert('Por favor, selecione uma opção antes de continuar.');
        }
    }
    
    function validateCurrentAnswer() {
        const question = mbtiQuestions[currentQuestion];
        return document.querySelector(`input[name="q${question.id}"]:checked`) !== null;
    }
    
    function saveCurrentAnswer() {
        const question = mbtiQuestions[currentQuestion];
        const selected = document.querySelector(`input[name="q${question.id}"]:checked`);
        if (selected) {
            answers[question.id] = selected.value;
        }
    }
    
    document.getElementById('mbti-assessment').onsubmit = async function(e) {
        e.preventDefault();
        
        if (!validateCurrentAnswer()) {
            alert('Por favor, responda a questão atual antes de finalizar.');
            return;
        }
        
        saveCurrentAnswer();
        
        try {
            const response = await fetch('../api/questionnaires/mbti/submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ answers })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'mbti-results.php?id=' + result.assessment_id;
            } else {
                alert(result.message || 'Erro ao enviar avaliação');
            }
        } catch (error) {
            alert('Erro ao enviar avaliação');
            console.error('Error:', error);
        }
    };
    </script>
</body>
</html>
