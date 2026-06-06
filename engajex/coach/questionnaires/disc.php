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
    <title>Avaliação DISC - Sistema de Coaching</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/questionnaires.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="content">
            <header class="dashboard-header">
                <h1>Avaliação DISC</h1>
            </header>
            
            <div class="assessment-container">
                <div class="card">
                    <div class="assessment-intro">
                        <h2>Sobre a Avaliação DISC</h2>
                        <p>O DISC é uma ferramenta de avaliação comportamental que analisa quatro dimensões principais:</p>
                        <ul>
                            <li><strong>D - Dominância:</strong> Como você lida com problemas e desafios</li>
                            <li><strong>I - Influência:</strong> Como você lida com pessoas e influencia outros</li>
                            <li><strong>S - Estabilidade:</strong> Como você lida com ritmo e consistência</li>
                            <li><strong>C - Conformidade:</strong> Como você lida com procedimentos e restrições</li>
                        </ul>
                        <p class="assessment-instructions">
                            A avaliação consiste em 24 grupos de palavras. Em cada grupo, você deve:
                            <br>1. Selecionar a palavra que MAIS te descreve
                            <br>2. Selecionar a palavra que MENOS te descreve
                        </p>
                        <button class="btn-primary" onclick="startAssessment()">Iniciar Avaliação</button>
                    </div>
                </div>
                
                <form id="disc-assessment" style="display: none;">
                    <div id="question-container">
                        <!-- Questions will be loaded here -->
                    </div>
                    
                    <div class="assessment-navigation">
                        <button type="button" class="btn-secondary" onclick="previousQuestion()" id="btn-previous" style="display: none;">Anterior</button>
                        <span id="question-progress">Questão 1 de 24</span>
                        <button type="button" class="btn-primary" onclick="nextQuestion()" id="btn-next">Próxima</button>
                        <button type="submit" class="btn-primary" id="btn-submit" style="display: none;">Finalizar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    const discQuestions = [
        {
            group: 1,
            words: ['Dominante', 'Inspirador', 'Sereno', 'Cauteloso']
        },
        {
            group: 2,
            words: ['Direto', 'Interativo', 'Estável', 'Consciente']
        },
        // Add more question groups here
    ];
    
    let currentQuestion = 0;
    let answers = [];
    
    function startAssessment() {
        document.querySelector('.assessment-intro').style.display = 'none';
        document.getElementById('disc-assessment').style.display = 'block';
        loadQuestion(0);
    }
    
    function loadQuestion(index) {
        const question = discQuestions[index];
        const container = document.getElementById('question-container');
        
        container.innerHTML = `
            <div class="question-card">
                <div class="question-number">Grupo ${question.group} de 24</div>
                <div class="question-text">Selecione a palavra que MAIS e MENOS te descreve:</div>
                <div class="options-grid">
                    ${question.words.map((word, i) => `
                        <div class="word-item">
                            <div class="word-text">${word}</div>
                            <div class="word-choices">
                                <label>
                                    <input type="radio" name="most_${index}" value="${i}" 
                                        ${answers[index]?.most === i ? 'checked' : ''}>
                                    MAIS
                                </label>
                                <label>
                                    <input type="radio" name="least_${index}" value="${i}"
                                        ${answers[index]?.least === i ? 'checked' : ''}>
                                    MENOS
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        updateNavigation();
    }
    
    function updateNavigation() {
        document.getElementById('btn-previous').style.display = currentQuestion > 0 ? 'block' : 'none';
        document.getElementById('btn-next').style.display = currentQuestion < discQuestions.length - 1 ? 'block' : 'none';
        document.getElementById('btn-submit').style.display = currentQuestion === discQuestions.length - 1 ? 'block' : 'none';
        document.getElementById('question-progress').textContent = `Questão ${currentQuestion + 1} de ${discQuestions.length}`;
    }
    
    function previousQuestion() {
        if (currentQuestion > 0) {
            saveCurrentAnswers();
            currentQuestion--;
            loadQuestion(currentQuestion);
        }
    }
    
    function nextQuestion() {
        if (validateCurrentAnswers()) {
            saveCurrentAnswers();
            currentQuestion++;
            loadQuestion(currentQuestion);
        } else {
            alert('Por favor, selecione uma palavra que MAIS te descreve e uma que MENOS te descreve.');
        }
    }
    
    function validateCurrentAnswers() {
        const most = document.querySelector(`input[name="most_${currentQuestion}"]:checked`);
        const least = document.querySelector(`input[name="least_${currentQuestion}"]:checked`);
        return most && least && most.value !== least.value;
    }
    
    function saveCurrentAnswers() {
        const most = document.querySelector(`input[name="most_${currentQuestion}"]:checked`);
        const least = document.querySelector(`input[name="least_${currentQuestion}"]:checked`);
        
        if (most && least) {
            answers[currentQuestion] = {
                most: parseInt(most.value),
                least: parseInt(least.value)
            };
        }
    }
    
    document.getElementById('disc-assessment').onsubmit = async function(e) {
        e.preventDefault();
        
        if (!validateCurrentAnswers()) {
            alert('Por favor, complete todas as escolhas antes de finalizar.');
            return;
        }
        
        saveCurrentAnswers();
        
        try {
            const response = await fetch('../api/questionnaires/disc/submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ answers })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'disc-results.php?id=' + result.assessment_id;
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
